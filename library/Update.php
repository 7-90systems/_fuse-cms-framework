<?php
    /**
     *  @package fuse-cms
     *
     *  This class handles our standard plugin and theme update checks.
     */
    
    namespace Fuse;
    
    use Fuse\Traits\Singleton;
    
    
    class Update {
        
        use Singleton;
        
        
        
        
        /**
         *  Initialise our class
         */
        protected function _init () {
            // Only run these functions if we are in DEBUG mode
            if (defined ('WP_DEBUG') && WP_DEBUG === true) {
                // Set to always check for development environments
                set_site_transient ('update_plugins', null);
                set_site_transient ('update_themes', null);
                
                // Allow "unsafe" URL's
                add_filter ('http_request_args', array ($this, 'disableSslVerify'));
            } // if ()
            
            $update_themes = new Update\Theme ();
            $update_plugins = new Update\Plugin ();
        } // _init ()
        
        
        
        
        /**
         *  Disable SSL verification for HTTP requests
         */
        public function disableSslVerify ($args) {
            $args ['sslverify'] = false;
            $args ['reject_unsafe_urls'] = false;
            
            return $args;
        } // disableSslVerify ()
        
    } // class Update