<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our base form field class.
     *
     *  This is separate from the Fuse form fields so it can be sued anywhere around the site.
     */
    
    namespace Fuse\Forms;
    
    
    abstract class Field {
        
        /**
         *  Object constructor.
         *
         *  @param string $name The HTML name for this field.
         *  @param mixed $value The value to set for this field.
         */
        public function __construct ($name, $value = '') {
            $this->_name = $name;
            $this->_value = $value;
        } // __construct ()
        
        
        
        
        /**
         *  Render our fields HTML content.
         */
        abstract public function render ();
        
    } // abstract class Field