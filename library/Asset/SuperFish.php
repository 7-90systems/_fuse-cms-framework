<?php
    /**
     *  @package fusecms
     *
     *  This class represents the SuperFish asset.
     */
    
    namespace Fuse\Asset;
    
    use Fuse\Asset;
    
    
    class SuperFish extends Asset {
        
        /**
         *  Register the assets JavaScript and CSS files.
         */
        public function register () {
            wp_register_script ('hoverintent', FUSE_BASE_URL.'/assets/external/superfish-master/dist/js/hoverIntent.js', array ('jquery'));
            
            // Are we in debug mode?
            if (defined ('WP_DEBUG') && WP_DEBUG === true || defined ('SCRIPT_DEBUG') && SCRIPT_DEBUG === true) {
                // Load full versions
                wp_register_script ('superfish', FUSE_BASE_URL.'/assets/external/superfish-master/dist/js/superfish.js', array ('jquery', 'hoverintent'));
            } // if ()
            else {
                // Load minified versions
                wp_register_script ('superfish', FUSE_BASE_URL.'/assets/external/superfish-master/dist/js/superfish.min.js', array ('jquery'));
            } // else
            
            wp_register_style ('superfish', FUSE_BASE_URL.'/assets/external/superfish-master/dist/css/superfish.css');
        } // register ()
        
    } // class SuperFish