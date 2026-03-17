<?php
    /**
     *  @package fusecms
     *
     *  This is the base for our asset classes.
     *
     *  Assets are 3rd arty of external resources, like BX Slider, Colorbox,
     *  etc.
     */
    
    namespace Fuse;
    
    
    abstract class Asset {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            // add_action ('wp_enqueue_scripts', array ($this, 'register'), 1);
// echo "<p>Set enqueue for '".get_class ($this)."'</p>";
        } // __construct ()
        
        
        
        
        /**
         *  This function is used to register the JavaScript and CSS files that
         *  are part of this asset.
         *
         *  Use this function to register your script and style files.
         */
        abstract public function register ();
        
    } // abstract class Asset