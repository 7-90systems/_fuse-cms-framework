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
         *  @var string The class marking a field as disabled. JavaScript uses
         *  it to find what it has switched off, and the stylesheet to grey it.
         */
        const DISABLED_CLASS = 'fuse-forms-field-disabled';

        /**
         *  @var bool Whether this field is disabled.
         */
        protected $_disabled = false;
        
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

            /**
             *  Held on the object rather than left in the attributes, because a
             *  disabled field has to do more than carry the attribute -- see
             *  renderDisabledValue ().
             */
            if (array_key_exists ('disabled', $args)) {
                $this->_disabled = ($args ['disabled'] == true);
                unset ($args ['disabled']);
            } // if ()

            $this->name = $name;
            $this->label = $label;
            $this->description = $description;
            $this->_value = $value;

            parent::__construct ($args);
        } // __construct ()




        /**
         *  Is this field disabled?
         *
         *  @return bool True when it is.
         */
        public function isDisabled () {
            return $this->_disabled;
        } // isDisabled ()

        /**
         *  Disable or enable this field.
         *
         *  @param bool $disabled True to disable it.
         *
         *  @return Fuse\Forms\Component\Field This field.
         */
        public function setDisabled ($disabled = true) {
            $this->_disabled = ($disabled == true);

            return $this;
        } // setDisabled ()

        /**
         *  Add the disabled state to a field's attributes.
         *
         *  Every field calls this on its way to being rendered, so the state is
         *  applied the same way whatever the field is, and so the markup always
         *  carries the hooks the JavaScript needs to find it again.
         *
         *  @param array $attributes The attributes being built.
         *
         *  @return array The attributes, with the state applied.
         */
        public function applyState ($attributes) {
            if (is_array ($attributes) === false) {
                $attributes = array ();
            } // if ()

            // 'required' => true is not valid HTML on its own.
            if (array_key_exists ('required', $attributes)) {
                if ($attributes ['required'] == true) {
                    $attributes ['required'] = 'required';
                } // if ()
                else {
                    unset ($attributes ['required']);
                } // else
            } // if ()

            /**
             *  The field's own name, so a script can find it without knowing
             *  how the id was built.
             */
            $attributes ['data-fuse-field'] = $this->name;

            if ($this->isDisabled () === true) {
                $attributes ['disabled'] = 'disabled';
                $attributes ['class'] = trim (
                    (array_key_exists ('class', $attributes) ? $attributes ['class'] : '').' '.self::DISABLED_CLASS
                );
            } // if ()

            return $attributes;
        } // applyState ()

        /**
         *  The field's description, as it sits under the field itself.
         *
         *  Every container that lays out fields calls this, so a description
         *  appears wherever a field does. It used to be written out by the
         *  panel alone, which meant a field put inside a group lost its
         *  description without anything saying so.
         *
         *  @param bool $output True to print it, false to return it.
         *
         *  @return string The HTML, when it is not being printed.
         */
        public function renderDescription (bool $output = true) {
            $html = '';

            if (strlen (strval ($this->description)) > 0) {
                $html = '<p class="fuse-field-description">'.wp_kses_post ($this->description).'</p>';
            } // if ()

            if ($output === true) {
                echo $html;

                return '';
            } // if ()

            return $html;
        } // renderDescription ()

        /**
         *  A hidden copy of the value, for when the field is disabled.
         *
         *  A disabled control is not submitted with the form, and the settings
         *  form saves an empty value for anything it does not find in the
         *  request. Disabling a field would therefore wipe the setting behind
         *  it. This posts the value the field is already holding, so a disabled
         *  field keeps what it has instead of losing it.
         *
         *  @param bool $output True to print it, false to return it.
         *
         *  @return string The HTML, when it is not being printed.
         */
        public function renderDisabledValue (bool $output = true) {
            $html = '';

            if ($this->isDisabled () === true) {
                $value = $this->getValue ();

                $html = '<input type="hidden" name="'.esc_attr ($this->getName ()).'" value="'.esc_attr (is_scalar ($value) ? $value : '').'" />';
            } // if ()

            if ($output === true) {
                echo $html;

                return '';
            } // if ()

            return $html;
        } // renderDisabledValue ()
        
        
        
        
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