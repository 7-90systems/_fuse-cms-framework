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
                // False when the attachment has since been deleted.
                $src = strval (wp_get_attachment_image_url ($value, 'thumbnail'));
            } // if ()

            /**
             *  What is shown follows whether there is an image to show, not
             *  whether a number was saved. An attachment that has since been
             *  deleted leaves a value pointing at nothing, and the field then
             *  offers to choose one rather than showing an empty frame with no
             *  way back. The value itself is left alone: it is not this
             *  field's place to throw a reference away on the strength of one
             *  lookup.
             */
            $has_image = ($src !== '');
            ?>
                <div id="<?php echo esc_attr ($id); ?>" class="fuse-image-field">

                    <div class="fuse-image-image"<?php if ($has_image === false) echo ' style="display: none;"'; ?>>
                        <span class="dashicons dashicons-no"></span>
                        <?php
                            /**
                             *  No src attribute at all rather than an empty
                             *  one. A browser resolves src="" against the page
                             *  it is on and fetches it again, so an empty image
                             *  field cost a second request for the whole admin
                             *  screen. The JavaScript fills this in when an
                             *  image is chosen, so the element has to stay.
                             */
                        ?>
                        <img<?php echo ($has_image === true) ? ' src="'.esc_url ($src).'"' : ''; ?> alt="<?php esc_attr_e ('Selected image', 'fuse'); ?>" width="150" height="150" />
                    </div>

                    <p class="select-image-container"<?php if ($has_image === true) echo ' style="display: none;"'; ?>>
                        <a href="#" class="choose-image-link button"><?php _e ('Select Image', 'fuse'); ?></a>
                    </p>

                    <input type="hidden" name="<?php echo esc_attr ($this->_name); ?>" value="<?php echo esc_attr ($value); ?>" />
                </div>
            <?php
        } // render ()
        
    } // class Image