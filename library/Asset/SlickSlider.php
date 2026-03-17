<?php
    /**
     *  @package fusecms
     *
     *  This class represents the Slick Slider asset.
     */
    
    namespace Fuse\Asset;
    
    use Fuse\Asset;
    
    
    class SlickSlider extends Asset {
        
        /**
         *  Register the assets JavaScript and CSS files.
         */
        public function register () {
            // Are we in debug mode?
            if (defined ('WP_DEBUG') && WP_DEBUG === true || defined ('SCRIPT_DEBUG') && SCRIPT_DEBUG === true) {
                // Load full versions
                wp_register_script ('slick', FUSE_BASE_URL.'/assets/external/slick-1.8.1/slick/slick.js', array ('jquery'));
            } // if ()
            else {
                // Load minified versions
                wp_register_script ('slick', FUSE_BASE_URL.'/assets/external/slick-1.8.1/slick/slick.min.js', array ('jquery'));
            } // else
            
            wp_register_style ('slick', FUSE_BASE_URL.'/assets/external/slick-1.8.1/slick/slick.css');
        } // register ()
        
    } // class SlickSlider