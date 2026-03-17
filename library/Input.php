<?php
    /**
     *  @package fuse-cms
     *
     *  This is the base for our input classes. We use these to extend the standard
     *  functionality of the WordPress system.
     */
    
    namespace Fuse;
    
    
    abstract class Input {
        
        /**
         *  @var string The input name.
         */
        public $name;
        
        /**
         *  @vare mixed The inputs value.
         */
        public $value;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $input_name The name for this input.
         *  @param mixed $value The value for this field. The type will change depending in teh fields needs.
         */
        public function __construct ($input_name, $value = '') {
            $this->name = $input_name;
            $this->value = $value;
        } // __construct ()
        
        
        
        
        /**
         *  This function renders our input fields HTML.
         */
        abstract public function render ();
        
        
        
        
        /**
         *  Output this input field as a string.
         *
         *  @return string The HTML for the input field.
         */
        public function __toString () {
            ob_start ();
            $this->render ();
            $html = ob_get_contents ();
            ob_end_clean ();
            
            return $html;
        } // __toString ()
        
    } // abstract class Input