<?php
    /**
     *  @package fuse-cms
     *
     *  This class takes care of our theme updates for our Fuse plugins.
     */
    
    namespace Fuse\Update;
    
    use Fuse\Traits\Update;
    
    
    class Theme {
        
        use Update;
        
        
        
        
        /**
         *  @var array Our themes list. Don't access this list directly. Use the getThemes() function in this class.
         */
        private $_themes;
        
        
        
        
        /**
         *  Object constructor.
         */
        public function __construct () {
           // add_action ('init', array ($this, 'getThemes'));
           
           add_filter ('pre_set_site_transient_update_themes', array ($this, 'checkForUpdate'));
           add_filter ('pre_set_site_transient_update_themes', array ($this, 'checkForTranslations'), 11);
           add_filter ('themes_api', array ($this, 'themeApiCall'), 10, 3);
        } // __construct ()
        
        
        
        
        /**
         *  Check to see if there is a theme update.
         */
        public function checkForUpdate ($checked_data) {
            global $wp_version, $theme_version;
            
            foreach ($this->getThemes () as $slug => $theme) {
                $request = array (
                    'slug' => $slug,
                    'version' => $theme ['theme']->Version
                );
                
                // Start checking for an update
                $send_for_check = array(
                    'body' => array(
                        'action' => 'theme_update',
                        'request' => serialize($request),
                        'api-key' => md5(get_bloginfo('url')),
                        'wp-version' => $wp_version,
                        'php-version' => phpversion()
                    ),
                    'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo ('url')
                );
                $response = NULL;
                
                $raw_response = wp_remote_post ($this->_getServerUrl ($theme ['server']), $send_for_check);
                
                if (!is_wp_error($raw_response) && ($raw_response ['response']['code'] == 200)) {
                    $response = (array) json_decode ($raw_response['body']);
                } // if ()
            
                // Feed the update data into WP updater
                if (!empty ($response)) {
                    $checked_data->response [$slug] = $response;
                } // if ()
            } // foreach ()
        
            return $checked_data;
        } // checkForUpdate ()
        
        /**
         *  Make the theme API call.
         */
        public function themeApiCall ($def, $action, $args) {
            global $wp_version;
            
            $res = false;
            
            foreach ($this->getThemes () as $slug => $theme) {
                if (property_exists ($args, 'slug') && $slug == $args->slug) {
                    $theme_data = wp_get_theme ($slug);
                    $args->version = $theme_data->Version;
                    
                    $request_string = array (
                        'body' => array (
                            'action' => $action,
                            'request' => serialize ($args),
                            'api-key' => md5 (get_bloginfo ('url')),
                            'wp-version' => $wp_version,
                            'php-version' => phpversion ()
                        ),
                        'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo ('url')
                    );
                    
                    $request = wp_remote_post ($this->_getServerUrl ($theme ['server']), $request_string);
                    
                    if (is_wp_error ($request)) {
                        $res = new WP_Error ('themes_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message ());
                    } // if ()
                    else {
                        $res = json_decode ($request ['body']);
                        
                        if ($res === false) {
                            $res = new WP_Error ('themes_api_failed', __('An unknown error occurred'), $request ['body']);
                        } // if ()
                    } // else
                } // if ()
            } // foreach ()
            
            return $res;
        } // themeApiCall ()



        
        
        
        
        /**
         *  Get the list of themes to update.
         */
        public function getThemes () {
            $this->_themes = array ();
            
            if (empty ($this->_themes)) {
                foreach (wp_get_themes () as $slug => $theme) {
                    $file_uri = trailingslashit ($theme->get_file_path ()).'style.css';
                        
                    if (file_exists ($file_uri)) {
                        $fh = fopen ($file_uri, 'r');
                        $has_fuse_update = false;
                            
                        while ($has_fuse_update === false && ($line = fgets ($fh, 8092)) !== false) {
                            $line = trim ($line, ' *');
                                
                            if (strtolower (substr ($line, 0, 19)) == 'fuse update server:') {
                                $this->_themes [$slug] = array (
                                    'server' => trim (substr ($line, 19)),
                                    'theme' => $theme
                                );
                                $has_fuse_update = true;
                            } // if ()
                        } // while ()
                            
                        fclose ($fh);
                    } // if ()
                } // foreach ()
            } // if ()
            
            return $this->_themes;
        } // getThemes ()




        /**
         *  Ask each update server for available theme language packs and feed
         *  the ones this site wants (and that are newer than installed) into the
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

            // Group our themes by their update server.
            $servers = array ();

            foreach ($this->getThemes () as $slug => $theme) {
                $servers [$theme ['server']][] = $slug;
            } // foreach ()

            $installed = wp_get_installed_translations ('themes');
            $languages = get_available_languages ();
            $locale = get_locale ();

            if (in_array ($locale, $languages, true) === false) {
                $languages [] = $locale;
            } // if ()

            foreach ($servers as $server => $theme_slugs) {
                $request_string = array (
                    'body' => array (
                        'action' => 'theme_translations',
                        'request' => serialize (array ('slugs' => $theme_slugs)),
                        'api-key' => md5 (get_bloginfo ('url')),
                        'wp-version' => $wp_version,
                        'php-version' => phpversion ()
                    ),
                    'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo ('url')
                );

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

                    if (in_array ($pack ['language'], $languages, true) === false) {
                        continue;
                    } // if ()

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

    } // class Theme