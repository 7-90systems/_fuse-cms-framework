<?php
    /**
     *  @package fuse-cms
     *
     *  This class represents an image choice field.
     */
    
    namespace Fuse\Input;
    
    use Fuse\Input;
    
    
    class Image extends Input {
        
        /**
         *  Object constructor.
         *
         *  @param string $input_name The name for this input.
         *  @param int $value The image ID for this input.
         */
        public function __construct ($input_name, $value = '') {
            parent::__construct ($input_name, $value);
        } // __construct ()
        
        
        
        
        /**
         *  Render the inputs HTML code.
         */
        public function render () {
            $image = NULL;
            
            if (intval ($this->value) > 0) {
                $image = wp_get_attachment_image_url (intval ($this->value), 'thumbnail');
            } // if ()
            ?>
                <div class="fuse-input-image-container">
                
                    <button class="button"<?php if (empty ($image) === false) echo ' style="display: none;"'; ?>><?php _e ('Choose image...', 'fuse'); ?></button>
                
                    <div class="image-container"<?php if (empty ($image) === true) echo ' style="display: none;"'; ?>>
                        <img src="<?php echo esc_url ($image); ?>" alt="<?php _e ('Image input field', 'fuse'); ?>" width="150" height="150" />
                        <a class="delete">
                            <span class="dashicons dashicons-no"></span>
                        </a>
                    </div>
                    
                    <input type="hidden" name="<?php esc_attr_e ($this->name) ?>" value="<?php echo intval ($this->value); ?>" />
                </div>
            <?php
        } // render ()
        
    } // class Image