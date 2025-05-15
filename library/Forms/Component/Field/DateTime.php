<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is a date and time field.
     */
    
    namespace Fuse\Forms\Component\Field;
    
    use Fuse\Forms\Component\Field\Text;
    
    
    class DateTime extends Text {
        
        /**
         *  Object constructor.
         *
         *  @param string $name The fields name.
         *  @param string $label The fields label.
         *  @param mixed $value The fields value.
         *  @param array $args The arguments for this field.
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
            $attributes = array_merge ($this->_args, array (
                'id' => $this->getId (),
                'name' => $this->getName (),
                'type' => 'datetime-local',
                'value' => $this->_value
            ));
            
            ob_start ();
            ?>
                <input<?php echo fuse_format_attributes ($attributes); ?> />
            <?php
            $html = ob_get_contents ();
            ob_end_clean ();
            
            if ($output === true) {
                echo $html;
            } // if ()
            else {
                return $html;
            } // else
        } // render ()
        
    } // class DateTime