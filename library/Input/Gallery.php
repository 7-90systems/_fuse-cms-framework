<?php
    /**
     *  @package fuse-cms
     *
     *  This class represents an gallery choice field.
     */
    
    namespace Fuse\Input;
    
    use Fuse\Input;
    
    
    class Gallery extends Input {
        
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
            $images = array ();
            
            if (strlen ($this->value) > 0) {
                $image_ids = explode (',', $this->value);
                
                foreach ($image_ids as $id) {
                    $images [] = get_post ($id);
                } // foreach ()
            } // if ()
            ?>
                <div class="fuse-input-gallery-container">
                    
                    <div class="gallery-images">
                        <?php foreach ($images as $image): ?>
                        
                            <div class="image-container" data-id="<?php echo $image->ID; ?>">
                                <img src="<?php echo esc_url (wp_get_attachment_image_url ($image->ID, 'thumbnail')); ?>" alt="<?php _e ('Gallery input image', 'fuse'); ?>" width="150" height="150" />
                                <a class="delete">
                                    <span class="dashicons dashicons-no"></span>
                                </a>
                            </div>
                        
                        <?php endforeach; ?>
                    </div>
                
                    <button class="button"><?php _e ('Add gallery images', 'fuse'); ?></button>
                    
                    <input type="hidden" name="<?php esc_attr_e ($this->name); ?>" value="<?php esc_attr_e ($this->value); ?>" />
                    
                    <template>
                        <div class="image-container" data-id="%%ID%%">
                            <img src="%%THUMBNAIL%%" alt="<?php _e ('Gallery input image', 'fuse'); ?>" width="150" height="150" />
                            <a class="delete">
                                <span class="dashicons dashicons-no"></span>
                            </a>
                        </div>
                    </template>
                </div>
            <?php
        } // render ()
        
    } // class Gallery