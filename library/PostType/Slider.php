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
            add_meta_box ('fuse_slider_templates_meta', __ ('Slided Templates', 'fuse'), array  ($this, 'templatesMeta'), $this->getSlug (), 'normal', 'default');
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
         *  Set up the templates meta box.
         */
        public function templatesMeta ($post) {
            $slider = get_post_meta ($post->ID, 'fuse_slider_template_slider', true);
            $slide = get_post_meta ($post->ID, 'fuse_slider_template_slide', true);
            ?>
                <p><?php _e ('Template files can be added in your theme or using the /templates/slider/ folder.', 'fuse'); ?></p>
                <p><?php _e ('Slider - Templates files must be called "slider-*NAME*.php".', 'fuse'); ?></p>
                <p><?php _e ('Slides - Templates files must be called "slide-*NAME*.php".', 'fuse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th><?php _e ('Slider template file', 'fuse'); ?></th>
                        <td>
                            <select name="fuse_slider_template_slider">
                                <?php foreach ($this->_getTemplateFiles ('slider') as $template): ?>
                                    <option value="<?php esc_attr_e ($template); ?>"<?php selected ($template, $slider); ?>><?php echo $template; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e ('Slide template file', 'fuse'); ?></th>
                        <td>
                            <select name="fuse_slider_template_slide">
                                <?php foreach ($this->_getTemplateFiles ('slide') as $template): ?>
                                    <option value="<?php esc_attr_e ($template); ?>"<?php selected ($template, $slide); ?>><?php echo $template; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
            <?php
        } // templatesMeta ()
        
        
        
        
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
            
            // Templates
            if (array_key_exists ('fuse_slider_template_slider', $_POST)) {
                update_post_meta ($post_id, 'fuse_slider_template_slider', $_POST ['fuse_slider_template_slider']);
                update_post_meta ($post_id, 'fuse_slider_template_slide', $_POST ['fuse_slider_template_slide']);
            } // if ()
        } // savePost ()
        
        
        
        
        /**
         *  Get template files list.
         *
         *  @param string $templage The base template file. This can be either 'slider' or 'slide'.
         *
         *  @return array The slide templates that are available.
         */
        protected function _getTemplateFiles ($template = 'slider') {
            if ($template != 'slide') {
                $template = 'slider';
            } // if ()
            
            $templates = array ($template.'.php');
            
            $template_directories = apply_filters ('fuse_slider_template_locations', array ());
            
            foreach ($template_directories as $dir => $url) {
                error_log ("Template directory: ".$dir." - ".$url);
                
                if (file_exists ($dir)) {
                    $files = glob ($dir.$template.'-*.php');
                    
                    foreach ($files as $file) {
                        error_log ("Found file '".$file."'");
                        $templates [] = basename ($file);
                    } // foraech ()
                } // if ()
            } // foreach ()
            
            return $templates;
        } // getTemplateFiles ()
        
    } // class Slider