<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is a toggle field.
     */
    
    namespace Fuse\Forms\Component\Field;
    
    use Fuse\Forms\Component\Field;
    
    
    class Toggle extends Field {
        
        /**
         *  @var array The toggle options.
         */
        protected $_options;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $name The fields name.
         *  @param string $label The fields label.
         *  @param mixed $value The fields value.
         *  @param array $args The arguments for this field. See the parent
         *  class for valid argument values.
         *  @param array $options The options for the toggle. This should be an
         *  associative array with two values. Be aware that the negative or
         *  'no' value should be first to match with the CSS styles.
         */
        public function __construct ($name, $label, $value = '', $args = array (), $options = NULL) {
            parent::__construct ($name, $label, $value, $args);
            
            if (empty ($options) || is_array ($options) == false || count ($options) != 2) {
                $options = array (
                    'no' => __ ('No', 'fuse'),
                    'yes' => __ ('Yes', 'fuse')
                );
            } // if ()
            
            $this->_options = $options;
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
                'type' => 'hidden',
                'value' => $this->_value
            ));
            
            $attributes = $this->applyState ($attributes);
            
            /**
             *  The hidden input carries the value and is left enabled, so a
             *  disabled toggle still posts what it is holding. Only the control
             *  the user clicks is switched off, on the wrapper below.
             *
             *  The wrapper takes the state and the data hook as well, so that a
             *  script looking the field up finds one element and cannot disable
             *  this input by accident -- which would stop the value posting.
             */
            unset ($attributes ['disabled']);
            unset ($attributes ['data-fuse-field']);
            
            $attributes ['class'] = trim (str_replace (self::DISABLED_CLASS, '', $attributes ['class']));
            
            $first = true;
            
            ob_start ();
            ?>
                <div class="fuse-forms-field-toggle<?php echo $this->isDisabled () ? ' '.self::DISABLED_CLASS : ''; ?>" data-field="<?php echo esc_attr ($this->getId ()); ?>" data-fuse-field="<?php echo esc_attr ($this->name); ?>" data-value="<?php echo esc_attr ($this->getValue ()); ?>"<?php echo $this->isDisabled () ? ' aria-disabled="true"' : ''; ?>>
                    <ul>
                        <?php foreach ($this->_options as $key => $label): ?>
                            <?php
                                $class = 'yes';
                                
                                if ($first === true) {
                                    $class = 'no';
                                    $first = false;
                                } // if ()
                            ?>
                            <li class="<?php echo esc_attr ($class); ?><?php if ($this->getValue () == $key) echo ' selected'; ?>" data-value="<?php echo esc_attr ($key); ?>"><?php echo wp_kses_post ($label); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <input<?php echo fuse_format_attributes ($attributes); ?> />
                </div>
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
        
    } // class Toggle