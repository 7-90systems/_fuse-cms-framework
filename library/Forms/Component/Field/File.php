<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is a file upload field.
     */
    
    namespace Fuse\Forms\Component\Field;
    
    use Fuse\Forms\Component\Field;
    
    
    class File extends Field {
        
        /**
         *  Object constructor.
         *
         *  @param string $name The fields name.
         *  @param string $label The fields label.
         *  @param mixed $value The fields value.
         *  @param array $args The arguments for this field. See the parent
         *  class for valid argument values.
         */
        public function __construct ($name, $label, $value = '', $args = array ()) {
            parent::__construct ($name, $label, $value, $args);
        } // __construct ()
        
        
        
        
        /**
         *  Render the field!
         *
         *  @param bool $render True to render the field, or false to return the
         *  HTML code.
         *
         *  @return string Returns the groups HTML code.
         */
        public function render ($output = true) {
            $field = new \Fuse\Forms\Field\File ($this->getName (), $this->getValue ());
            
            if ($output === true) {
                echo $field->render ();
            } // if ()
            else {
                return $field->render ();
            } // else
        } // render ()
        
    } // class File