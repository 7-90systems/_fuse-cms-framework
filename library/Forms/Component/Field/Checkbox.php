<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is a standard group of checkbox fields.
     */
    
    namespace Fuse\Forms\Component\Field;
    
    use Fuse\Forms\Component\Field;
    
    
    class Checkbox extends Field {
        
        /**
         *  @var array The options for this field.
         */
        protected $_options;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $name The fields name.
         *  @param string $label The fields label.
         *  @param array $options The options for this select field.
         *  @param mixed $value The fields value.
         *  @param array $args The arguments for this field. See the parent
         *  class for valid argument values.
         */
        public function __construct (string $name, string $label, array $options, $value = '', array $args = array ()) {
            if (is_array ($value)) {
                $value = implode (',', $value);
            } // if ()
            
            parent::__construct ($name, $label, $value, $args);
            
            $this->_options = $options;
        } // __construct ()
        
        /**
         *  Set the value for this field.
         *
         *  @param mixed $value The value to set.
         *
         *  @return Fuse\Form\Component\Field This field object.
         */
        public function setValue ($value) {
            if (is_array ($value)) {
                $value = implode (',', $value);
            } // if ()
            
            $this->_value = $this->validate ($value);
            
            return $this;
        } // setValue ()
        
        
        
        
        /**
         *  Get the options for this field.
         *
         *  @return array The existing set of options.
         */
        public function getOptions () {
            return $this->_options;
        } // getOptions ()
        
        /**
         *  Set the options for this field.
         *
         *  @param array The options to set.
         *
         *  @return Fuse\Forms\Component\Field\Select This select field object.
         */
        public function setOptions (array $options) {
            $this->_options = $options;
            
            return $this;
        } // setOptions ()
        
        
        
        
        /**
         *  Render the field!
         */
        public function render ($output = true) {
            $attributes = array_merge ($this->_args, array (
                'id' => $this->getId (),
                'name' => $this->getName ()
            ));
            
            if (array_key_exists ('required', $attributes)) {
                if ($attributes ['required'] === true) {
                    $attributes ['required'] = 'required';
                } // if ()
                else {
                    unset ($attributes ['required']);
                } // else
            } // if ()
            
            $values = explode (',', $this->_value);
            
            ob_start ();
            ?>
                <ul class="fuse-form-checkbox-list">
                    
                    <?php foreach ($this->_options as $key => $label): ?>
                    
                        <li>
                            <label>
                                <input type="checkbox" name="fuseform[<?php esc_attr_e ($this->name); ?>][]" value="<?php esc_attr_e ($key); ?>"<?php if (in_array ($key, $values)) echo ' checked="checked"'; ?> />
                                <?php echo $label; ?>
                            </label>
                        </li>
                    
                    <?php endforeach; ?>
                    
                </ul>
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
        
    } // class Checkbox