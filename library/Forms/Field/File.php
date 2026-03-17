<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our file form field class.
     */
    
    namespace Fuse\Forms\Field;
    
    use Fuse\Forms\Field;
    
    
    class File extends Field {
        
        /**
         *  Render our fields HTML content.
         */
        public function render () {
            $id = uniqid ('fuse_field_image_');
            $value = intval ($this->_value);
            
            $file = '';
            
            if ($value > 0) {
                $file = wp_get_attachment_url ($value);
            } // if ()
            ?>
                <div id="<?php esc_attr_e ($id); ?>" class="fuse-file-field">
                    
                    <div class="fuse-file-file"<?php if ($value == 0) echo ' style="display: none;"'; ?>>
                        <span class="dashicons dashicons-no"></span>
                        <a href="<?php echo $file ?>" class="file-name" target="_blank"><?php echo basename ($file); ?></a>
                    </div>
                    
                    <p class="select-file-container"<?php if ($value > 0) echo ' style="display: none;"'; ?>>
                        <a href="#" class="choose-file-link button"><?php _e ('Select File', 'fuse'); ?></a>
                    </p>
                    
                    <input type="hidden" name="<?php esc_attr_e ($this->_name); ?>" value="<?php echo $value; ?>" />
                </div>
            <?php
        } // render ()
        
    } // class File