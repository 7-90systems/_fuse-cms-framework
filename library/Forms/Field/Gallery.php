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
                <div id="<?php esc_attr_e ($id); ?>" class="fuse-gallery-field">
                
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
                    
                    <input type="hidden" name="<?php esc_attr_e ($this->_name); ?>" value="<?php echo implode (',', $image_ids); ?>" />
                    
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
            if (empty ($image_id)) {
                // Swap for placeholders
                $image_id = '%%ID%%';
                $src = '%%SRC%%';
            } // if ()
            else {
                // Get the image details
                $src = esc_url (wp_get_attachment_image_url ($image_id, 'thumbnail'));
            } //else
            
            ?>
                <div class="fuse-gallery-image">
                    <span class="dashicons dashicons-no"></span>
                    <img src="<?php echo $src; ?>" alt="<?php _e ('Gallery image', 'fuse'); ?>" width="150" height="150" data-id="<?php echo $image_id; ?>" />
                </div>
            <?php
        } // _imageHtml ()
        
    } // class Gallery