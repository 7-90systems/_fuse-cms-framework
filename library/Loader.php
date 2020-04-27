<?php
    /**
     *  @package fusecms
     *
     *  This is our class auto-loader. This lets us set up our core, theme and
     *  plugin classes.
     */
    
    namespace Fuse;
    
    
    class Loader {
        
        /**
         *  @var Fuse\Loader The singular instance of the loader class.
         */
        static private $_instance;
        
        
        
        
        /**
         *  Object constructor.
         */
        private function __construct () {
            spl_autoload_register (array ($this, 'load'));
        } // __construct ()
        
        
        
        
        /**
         *  Attempt to load a class. We first attempt to determine if it is a
         *  Fuse class, and if it is we try to load it.
         *
         *  @param string $class_name The name of the class to attempt to load.
         *
         *  @return null
         */
        public function load ($class_name) {
            $class = explode ('\\', $class_name);
            
            // Check to see if we have a Fuse class
            if (count ($class) > 1 && $class [0] == 'Fuse') {
                // Remove 'Fuse' as it's not needed after here.
                array_shift ($class);
                
                if (count ($class) > 1 && $class [0] == 'Theme') {
                    array_shift ($class);
                    $theme_name = array_shift ($class);
                    $const = 'FUSE_THEME_'.strtoupper ($theme_name).'_BASE_URI';
                    
                    $file = constant ($const).DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.implode (DIRECTORY_SEPARATOR, $class).'.php';
                } // if ()
                elseif (count ($class) > 1 && $class [0] == 'Plugin') {
                    array_shift ($class);
                    $plugin_name = array_shift ($class);
                    $const = 'FUSE_PLUGIN_'.strtoupper ($plugin_name).'_BASE_URI';
                    
                    $file = constant ($const).DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.implode (DIRECTORY_SEPARATOR, $class).'.php';
                } // elseif ()
                else {
                    $file = FUSE_BASE_URI.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.implode (DIRECTORY_SEPARATOR, $class).'.php';
                } // else
                
                if (file_exists ($file)) {
                    require_once ($file);
                } // if ()
            } // if ()
        } // load ()
        
        
        
        
        /**
         *  Get the single instance of this class.
         */
        static final public function getInstance () {
            if (empty (self::$_instance)) {
                self::$_instance = new Loader ();
            } // if ()
            
            return self::$_instance;
        } // getInstance ()
        
    } // class Loader