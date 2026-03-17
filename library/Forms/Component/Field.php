<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our base form field.
     */
    
    namespace Fuse\Forms\Component;
    
    use Fuse\Forms\Component;
    
    
    abstract class Field extends Component {
        
        const MESSAGE_SUCCESS = 'success';
        const MESSAGE_NOTICE = 'notice';
        const MESSAGE_ERROR = 'error';
        
        
        
        
        /**
         *  @var string The fields name.
         */
        public $name;
        
        /**
         *  @var string the fields label.
         */
        public $label;
        
        /**
         *  @var string The description for this field.
         */
        public $description;
        
        /**
         *  @var mixed The fields value.
         */
        protected $_value;
        
        /**
         *  @var array The arguments for this field.
         */
        protected $_args;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $name The fields name.
         *  @param string $label The fields label.
         *  @param mixed $value The fields value.
         *  @param array $attributes The arguments for this field. The base arguments
         *  are:
         *      type
         *      id
         *      class
         *      required
         *      placeholder
         */
        public function __construct ($name, $label, $value = '', $args = array ()) {
            $args = array_merge (array (
                'class' => 'fuse-forms-field'
            ), $args);
            
            $description = '';
            
            if (array_key_exists ('description', $args)) {
                $description = $args ['description'];
                unset ($args ['description']);
            } // if ()
            
            $this->name = $name;
            $this->label = $label;
            $this->description = $description;
            $this->_value = $value;
            
            parent::__construct ($args);
        } // __construct ()
        
        
        
        
        /**
         *  Get the values.
         *
         *  @return array The field values.
         */
        public function getValues () {
            return array ($this->name => $this->getValue ());
        } //getValues ()
        
        
        
        
        /**
         *  Get the form value.
         *
         *  @return mixed The forms value.
         */
        public function getValue () {
            return stripslashes ($this->_value);
        } // getValue ()
        
        /**
         *  Set the value for this field.
         *
         *  @param mixed $value The value to set.
         *
         *  @return Fuse\Form\Component\Field This field object.
         */
        public function setValue ($value) {
            $this->_value = $this->validate ($value);
            
            return $this;
        } // setValue ()
        
        
        
        
        /**
         *  Get the field ID.
         */
        public function getId () {
            if (array_key_exists ('id', $this->_args) && empty ($this->_args ['id']) === false) {
                $id = $this->_args ['id'];
            } // if ()
            else {
                $id = 'fuse-form-field-'.$this->name;
            } // else
            
            return $id;
        } // getId ()
        
        /**
         *  Get the name of this field. This is a computed name so that we can
         *  be sure of what's being saved.
         *
         *  @return string The field name;
         */
        public function getName () {
            return 'fuseform['.$this->name.']';
        } // getName ()
        
        /**
         *  Get the description for this field.
         *
         *  @return string Returns the fields description.
         */
        public function getDescription () {
            return $this->description;
        } // getDescription ()
        
        
        
        
        /**
         *  Validate a value for this field.
         *
         *  @param mixed $value The value to validate.
         *
         *  @return mixed The validate value.
         */
        public function validate ($value) {
            /**
             *  While we don't check anything here you should over-write this
             *  function in your child field classes so that you can check if
             *  the value given is a valid one.
             */
            
            return $value;
        } // validate ()
        
        
        
        
        /**
         *  Render the fields HTML code.
         *
         *  @param bool $render True to render the field, or false to return the
         *  HTML code.
         *
         *  @return string The fields HTML code.
         */
        abstract public function render (bool $output = true);
        
    } // abstract class Field