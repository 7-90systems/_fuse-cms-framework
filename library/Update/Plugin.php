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
                            'api-key' => md5 (get_bloginfo ('url'))
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
            
            foreach ($this->getPlugins () as $slug => $update_server) {
                if (property_exists ($args, 'slug') && $slug == $args->slug) {
                    $plugin_data = get_plugin_data (trailingslashit (WP_PLUGIN_DIR).$slug);
                    $args->version = $plugin_data ['Version'];
                    
                    $request_string = array (
                        'body' => array (
                            'action' => $action, 
                            'request' => serialize ($args),
                            'api-key' => md5 (get_bloginfo ('url'))
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
        
    } // class Plugin