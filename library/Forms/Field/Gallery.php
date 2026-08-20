<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our gallery form field class.
     */
    
    namespace Fuse\Forms\Field;
    
    use Fuse\Forms\Field;
    
    
    class Gallery extends Field {
        
        /**
         *  Render our fields HTML content.
         */
        public function render () {
            $id = uniqid ('fuse_field_gallery_');
            
            $image_ids = explode (',', $this->_value);
            $image_ids = array_filter ($image_ids);
            ?>
                <div id="<?php echo esc_attr ($id); ?>" class="fuse-gallery-field">
                
                    <div class="gallery-images">
                        
                        <?php foreach ($image_ids as $image_id): ?>
                            <?php
                                $image = get_post ($image_id);
                            ?>
                            
                            <?php if ($image && $image->post_type == 'attachment'): ?>
                            
                                <?php
                                    $this->_imageHtml ($image->ID);
                                ?>
                                
                            <?php else: ?>
                            
                                <?php
                                    if (($key = array_search ($image_id, $image_ids)) !== false) {
                                        unset ($image_ids [$key]);
                                    } // if ()
                                ?>
                            
                            <?php endif; ?>
                        
                        <?php endforeach; ?>
                        
                    </div>
                
                    <a href="#" class="choose-gallery-images-link button"><?php _e ('Add images to gallery', 'fuse'); ?></a>
                    
                    <input type="hidden" name="<?php echo esc_attr ($this->_name); ?>" value="<?php echo esc_attr (implode (',', array_map ('intval', $image_ids))); ?>" />
                    
                    <template class="fuse-gallery-image">
                        <?php
                            $this->_imageHtml ();
                        ?>
                    </template>
                </div>
            <?php
        } // render ()
        
        
        
        
        /**
         *  Get the image HTML.
         */
        protected function _imageHtml ($image_id = NULL) {
            /**
             *  With no ID this is the template the JavaScript clones, and the
             *  two values are markers for it to swap rather than data.
             *
             *  Escaping them as though they were data is what broke them.
             *  esc_url () gives anything without a scheme an http:// of its
             *  own, so %%SRC%% went into the template as http://%%SRC%% and
             *  the swap left that in front of the real address. intval ()
             *  turned %%ID%% into 0, so there was no marker left to replace
             *  and an image added to a gallery was saved as attachment 0 --
             *  which is to say, not saved at all.
             *
             *  They are still escaped, just as what they are: text going into
             *  an attribute.
             */
            $placeholder = empty ($image_id);

            if ($placeholder === true) {
                // Swap for placeholders
                $image_id = '%%ID%%';
                $src = '%%SRC%%';
            } // if ()
            else {
                // Get the image details
                $src = wp_get_attachment_image_url ($image_id, 'thumbnail');
            } //else

            ?>
                <div class="fuse-gallery-image">
                    <span class="dashicons dashicons-no"></span>
                    <img src="<?php echo ($placeholder === true) ? esc_attr ($src) : esc_url ($src); ?>" alt="<?php esc_attr_e ('Gallery image', 'fuse'); ?>" width="150" height="150" data-id="<?php echo ($placeholder === true) ? esc_attr ($image_id) : intval ($image_id); ?>" />
                </div>
            <?php
        } // _imageHtml ()
        
    } // class Gallery