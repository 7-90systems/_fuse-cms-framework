<?php
    /**
     *  Set up our slider post type.
     */
    
    namespace Fuse\PostType;
    
    use Fuse\PostType;
    
    
    class Slider extends PostType {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('fuse_slider', __ ('Slider', 'fuse'), __ ('Sliders', 'fuse'), array (
                'public' => false,
                'publicly_queryable' => false,
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-cover-image',
                'supports' => array (
                    'title'
                )
            ));
        } // __construct ()
        
        
        
        
        /**
         *  Add our meta boxes.
         */
        public function addMetaBoxes () {
            add_meta_box ('fuse_slider_settings_meta', __ ('Slider settings', 'fuse'), array ($this, 'settingsMeta'), $this->getSlug (), 'normal', 'default');
        } // addMetaBoxes ()
        
        /**
         *  Set up the slider settings meta box.
         */
        public function settingsMeta ($post) {
            $settings = get_post_meta ($post->ID, 'fuse_slider_settings', true);
            
            if (empty ($settings)) {
                $settings = 'arrows: true,
dots: false,
slidesToShow: 1';
            } // if ()
            ?>
                <p><?php _e ('Set up the JavaScript settings for the slider. These are set via the <a href="https://kenwheeler.github.io/slick/#settings" target="_blank">Slick Slider settings</a>. Be careful with this as you can break the code if you add the wrong things!', 'fuse'); ?></p>
                <textarea name="fuse_slider_settings" class="widefat" rows="10"><?php echo stripslashes ($settings); ?></textarea>
            <?php
        } // settingsMeta ()
        
        
        
        
        /**
         *  Save the posts values.
         *
         *  @param int $post_id The ID of the post.
         *  @param WP_Post $post The post object.
         */
        public function savePost ($post_id, $post) {
            // Settings
            if (array_key_exists ('fuse_slider_settings', $_POST)) {
                update_post_meta ($post_id, 'fuse_slider_settings', $_POST ['fuse_slider_settings']);
            } // if ()
        } // savePost ()
        
    } // class Slider