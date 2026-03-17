<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our image form field class.
     */
    
    namespace Fuse\Forms\Field;
    
    use Fuse\Forms\Field;
    
    
    class Image extends Field {
        
        /**
         *  Render our fields HTML content.
         */
        public function render () {
            $id = uniqid ('fuse_field_image_');
            $value = intval ($this->_value);
            
            $src = '';
            
            if ($value > 0) {
                $src = wp_get_attachment_image_url ($value, 'thumbnail');
            } // if ()
            ?>
                <div id="<?php esc_attr_e ($id); ?>" class="fuse-image-field">
                    
                    <div class="fuse-image-image"<?php if ($value == 0) echo ' style="display: none;"'; ?>>
                        <span class="dashicons dashicons-no"></span>
                        <img src="<?php echo $src; ?>" alt="<?php _e ('Selected image', 'fuse'); ?>" width="150" height="150" />
                    </div>
                    
                    <p class="select-image-container"<?php if ($value > 0) echo ' style="display: none;"'; ?>>
                        <a href="#" class="choose-image-link button"><?php _e ('Select Image', 'fuse'); ?></a>
                    </p>
                    
                    <input type="hidden" name="<?php esc_attr_e ($this->_name); ?>" value="<?php echo $value; ?>" />
                </div>
            <?php
        } // render ()
        
    } // class Image