<?php
    /**
     *  This is our base class for adding a settings object to your site.
     *
     *  You can have as amny settings objects as required.
     */
    
    namespace Fuse;
    
    use Fuse\Traits\Singleton;
    
    
    class Settings {
        
        use Singleton;
        
        
        
        
        /**
         *  Initialise our object.
         */
        final protected function _init () {
            $this->_initSettings ();
        } // _init ()
        
        
        
        
        /**
         *  Initialise our settings values.
         *
         *  All values are set as properties of this object and must include a leading underscore.
         *  eg:
         *      $_settings_value
         */
        protected function _initSettings () {
            
        } // _initSettings ()
        
        
        
        
        /**
         *  Get a setting value by name.
         *
         *  @param string $name The name of the setting to return.
         *
         *  @return mixed the setting value or NULL or no value exists.
         */
        public function get ($name) {
            $value = NULL;
            
            $name = '_'.$name;
            
            if (property_exists ($this, $name)) {
                $value = $this->$name;
            } // if ()
            
            return $value;
        } // get ()
        
        /**
         *  Magic function for getting a value.
         *
         *  @param string $name The name of the setting to return.
         *
         *  @return mixed the setting value or NULL or no value exists.
         */
        public function __get ($name) {
            return $this->get ($name);
        } // __ get ()
        
    } // class Settings