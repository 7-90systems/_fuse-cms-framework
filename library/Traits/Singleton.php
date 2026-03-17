<?php
    /**
     *  @package fusecms
     *
     *  This trait forms teh base of all singleton classes.
     */
    
    namespace Fuse\Traits;
    
    
    trait Singleton {
        
        /**
         *  @var objeect The single object instance
         */
        static private $_instance;
        
        
        
        
        /**
         *  Object constructor.
         */
        private function __construct () {
            $this->_init ();
         } // __construct ()
         
         
         
         
         /**
          * Initialise our class.
          */
         protected function _init () {
            /**
             *  Over-ride this method in your class to get set up.
             */
         } // _init ()
         
         
         
         
        /**
         * Get the object instance.
         *
         * @return object The single object instance.
         */
        static public function getInstance () {
            if (is_null (self::$_instance)) {
                $class = get_class ();
                self::$_instance = new $class ();
            } // if ()
            
            return self::$_instance;
        } // getInstance ()
        
    } // trait Singleton