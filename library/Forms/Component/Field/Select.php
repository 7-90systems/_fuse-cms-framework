<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is a standard select field.
     */
    
    namespace Fuse\Forms\Component\Field;
    
    use Fuse\Forms\Component\Field;
    
    
    class Select extends Field {
        
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
            parent::__construct ($name, $label, $value, $args);
            
            $this->_options = $options;
        } // __construct ()
        
        
        
        
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
            
            ob_start ();
            ?>
                <select<?php echo fuse_format_attributes ($attributes); ?>>
                    <?php foreach ($this->_options as $key => $label): ?>
                        <?php if (is_array ($label)): ?>
                            <optgroup label="<?php echo esc_attr ($label ['label']); ?>">
                                <?php foreach ($label ['values'] as $opt_key => $opt_label): ?>
                                    <option value="<?php echo esc_attr ($opt_key); ?>"<?php selected ($this->getValue (), $opt_key); ?>><?php echo wp_kses_post ($opt_label); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php else: ?>
                            <option value="<?php echo esc_attr ($key); ?>"<?php selected ($this->getValue (), $key); ?>><?php echo wp_kses_post ($label); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
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
        
    } // class Select