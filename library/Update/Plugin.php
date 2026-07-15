<?php
    /**
     *  @package fuse-cms
     *
     *  This class takes care of our plugin updates for our Fuse plugins.
     */
    
    namespace Fuse\Update;
    
    use WP_Error;
    use Fuse\Traits\Update;
    
    
    class Plugin {
        
        use Update;
        
        
        
        
        /**
         *  @var array Our plugins list. Don't access this list directly. Use the getPlugins() function in this class.
         */
        private $_plugins;
        
        
        
        
        /**
         *  Object constructor
         */
        public function __construct () {
            add_filter ('pre_set_site_transient_update_plugins', array ($this, 'checkForPluginUpdate'));
            add_filter ('pre_set_site_transient_update_plugins', array ($this, 'checkForTranslations'), 11);
            add_filter ('plugins_api', array ($this, 'pluginApiCall'), 10, 3);
        } // __construct ()
        
        
        
        
        /**
         *  Check for any plugin updates
         */
        public function checkForPluginUpdate ($checked_data) {
            global $api_url, $plugin_slug, $wp_version;
            
            if (empty ($checked_data->checked) === false) {
                foreach ($this->getPlugins () as $plugin_file => $update_server) {
                    $version = array_key_exists ($plugin_file, $checked_data->checked) ? $checked_data->checked [$plugin_file] : '0';
                    $args = array (
                        'slug' => $plugin_file,
                        'version' => $version,
                    );
                    $request_string = array (
                        'body' => array (
                            'action' => 'basic_check',
                            'request' => serialize ($args),
                            'api-key' => md5 (get_bloginfo ('url')),
                            'wp-version' => $wp_version,
                            'php-version' => phpversion ()
                        ),
                        'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo ('url'),
                        'timeout' => 60,
                        'httpversion' => '1.1',
                        'method' => 'POST'
                    );
                    
                    if (defined ('WP_DEBUG') && WP_DEBUG === true) {
                        $request_string ['sslverify'] = false;
                    } // if ()
                    
                    // Start checking for an update
                    $raw_response = wp_remote_post ($this->_getServerUrl ($update_server), $request_string);
                    $response = NULL;
                    
                    if (is_array ($raw_response) && array_key_exists ('response', $raw_response) && $raw_response ['response']['code'] == 404) {
                        // Not found, so dont do anyhting
                    } // if ()
                    else {
                        if (!is_wp_error ($raw_response) && ($raw_response ['response']['code'] == 200)) {
                            $response = json_decode ($raw_response ['body']);
                        } // if ()
                    
                        if (is_object ($response) && !empty ($response)) {
                            foreach (get_object_vars ($response) as $key => $val) {
                                if (is_string ($val) === false) {
                                    $response->{$key} = (array) $val;
                                } // if ()
                            } // foreach ()
                            
                            $checked_data->response [$plugin_file] = $response;
                        } // if ()
                    } // else
                } // foreach ()
            } // if ()
            
            return $checked_data;
        } // check_for_plugin_update ()
        
        /**
         *  Perform the plugin API call
         */
        public function pluginApiCall ($def, $action, $args) {
            global $wp_version;
            
            $result = false;
            
            foreach ($this->getPlugins () as $plugin_file => $update_server) {
                // WordPress asks for plugin information by the plugin's folder
                // slug, but the update server identifies a plugin by its
                // "folder/file.php" path (as the update check does), so match on
                // either and send the full path the server expects.
                if (property_exists ($args, 'slug') && (dirname ($plugin_file) == $args->slug || $plugin_file == $args->slug)) {
                    $plugin_data = get_plugin_data (trailingslashit (WP_PLUGIN_DIR).$plugin_file);
                    $args->slug = $plugin_file;
                    $args->version = $plugin_data ['Version'];
                    
                    $request_string = array (
                        'body' => array (
                            'action' => $action,
                            'request' => serialize ($args),
                            'api-key' => md5 (get_bloginfo ('url')),
                            'wp-version' => $wp_version,
                            'php-version' => phpversion ()
                        ),
                        'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo ('url'),
                        'timeout' => 60,
                        'httpversion' => '1.1',
                        'method' => 'POST'
                    );
                    
                    $request = wp_remote_post ($this->_getServerUrl ($update_server), $request_string);
                    
                    if (is_wp_error ($request)) {
                        $result = new WP_Error ('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p>'), $request->get_error_message ());
                    } // if ()
                    else {
                        $result = json_decode ($request ['body']);

                        if (is_object ($result) && !empty ($result)) {
                            foreach (get_object_vars ($result) as $key => $val) {
                                if (is_string ($val) === false) {
                                    $result->{$key} = (array) $val;
                                } // if ()
                            } // foreach ()
                        } // if ()

                        if ($result === false) {
                            $result = new WP_Error ('plugins_api_failed', __('An unknown error occurred'), $request ['body']);
                        } // if ()
                    } // else
                } // if ()
            } // foreach ()
            
            return $result;
        } // pluginApiCall ()
        
        
        
        
        /**
         *  Get the list of plugins.
         */
        public function getPlugins () {
            if (empty ($this->_plugins)) {
                $this->_plugins = array ();
                
                $plugins = get_plugins ();
                
                foreach ($plugins as $file => $data) {
                    $file_uri = trailingslashit (WP_PLUGIN_DIR).$file;
                    
                    if (file_exists ($file_uri)) {
                        $fh = fopen ($file_uri, 'r');
                        $has_fuse_update = false;
                        
                        while ($has_fuse_update === false && ($line = fgets ($fh, 8092)) !== false) {
                            $line = trim ($line, ' *');
                            
                            if (strtolower (substr ($line, 0, 19)) == 'fuse update server:') {
                                $this->_plugins [$file] = trim (substr ($line, 19));
                                $has_fuse_update = true;
                            } // if ()
                        } // while ()
                        
                        fclose ($fh);
                    } // if ()
                } // foreach
            } // if ()
            
            return $this->_plugins;
        } // getPlugins ()




        /**
         *  Ask each update server for available language packs and feed the ones
         *  this site wants (and that are newer than what is installed) into the
         *  update transient's translations list.
         */
        public function checkForTranslations ($checked_data) {
            global $wp_version;

            // WordPress fires this filter with null when the transient is being
            // cleared; only act on a real transient object.
            if (is_object ($checked_data) === false) {
                return $checked_data;
            } // if ()

            if (isset ($checked_data->translations) === false) {
                $checked_data->translations = array ();
            } // if ()

            // Group our plugins by their update server.
            $servers = array ();

            foreach ($this->getPlugins () as $plugin_file => $server) {
                $servers [$server][] = $plugin_file;
            } // foreach ()

            $installed = wp_get_installed_translations ('plugins');
            $languages = get_available_languages ();
            $locale = get_locale ();

            if (in_array ($locale, $languages, true) === false) {
                $languages [] = $locale;
            } // if ()

            foreach ($servers as $server => $plugin_files) {
                $request_string = array (
                    'body' => array (
                        'action' => 'plugin_translations',
                        'request' => serialize (array ('slugs' => $plugin_files)),
                        'api-key' => md5 (get_bloginfo ('url')),
                        'wp-version' => $wp_version,
                        'php-version' => phpversion ()
                    ),
                    'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo ('url'),
                    'timeout' => 60,
                    'httpversion' => '1.1',
                    'method' => 'POST'
                );

                if (defined ('WP_DEBUG') && WP_DEBUG === true) {
                    $request_string ['sslverify'] = false;
                } // if ()

                $raw_response = wp_remote_post ($this->_getServerUrl ($server), $request_string);

                if (is_wp_error ($raw_response) || wp_remote_retrieve_response_code ($raw_response) != 200) {
                    continue;
                } // if ()

                $packs = json_decode (wp_remote_retrieve_body ($raw_response), true);

                if (is_array ($packs) === false) {
                    continue;
                } // if ()

                foreach ($packs as $pack) {
                    if (is_array ($pack) === false || isset ($pack ['language'], $pack ['slug'], $pack ['updated']) === false) {
                        continue;
                    } // if ()

                    // Only offer languages this site actually uses.
                    if (in_array ($pack ['language'], $languages, true) === false) {
                        continue;
                    } // if ()

                    // Skip if the installed translation is the same age or newer.
                    if (isset ($installed [$pack ['slug']][$pack ['language']])) {
                        $existing = $installed [$pack ['slug']][$pack ['language']];
                        $existing_date = isset ($existing ['PO-Revision-Date']) ? $existing ['PO-Revision-Date'] : '';

                        if ($existing_date !== '' && strtotime ($pack ['updated']) <= strtotime ($existing_date)) {
                            continue;
                        } // if ()
                    } // if ()

                    $checked_data->translations [] = $pack;
                } // foreach ()
            } // foreach ()

            return $checked_data;
        } // checkForTranslations ()

    } // class Plugin