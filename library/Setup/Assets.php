<?php
    /**
     *  @package fusecms
     *
     *  This class takes care of setting up our external assets.
     *
     *  @filter fuse_register_assets
     */
    
    namespace Fuse\Setup;
    
    use Fuse\Asset;
    
    
    class Assets {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            add_action ('wp_enqueue_scripts', array ($this, 'registerAssets'), 1);
        } // __construct ()
        
        
        
        
        /**
         *  Register the external assets with the system.
         */
        public function registerAssets () {
            $assets = apply_filters ('fuse_register_assets', array (
                new Asset\BxSlider (),
                new Asset\Colorbox (),
                new Asset\MmenuLight (),
                new Asset\SlickSlider (),
                new Asset\SuperFish ()
            ));
            
            foreach ($assets as $asset) {
                $asset->register ();
            } // foreach ()
        } // registerAssets ()
        
    } // class Assets