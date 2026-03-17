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
           add_filter ('themes_api', 'themeApiCall', 10, 3);
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
                        'api-key' => md5(get_bloginfo('url'))
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
                            'api-key' => md5 (get_bloginfo ('url'))
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
        
    } // class Theme