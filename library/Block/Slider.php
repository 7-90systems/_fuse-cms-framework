<?php
    /**
     *  Set up our Slider block.
     */
    
    namespace Fuse\Block;
    
    use Fuse\Block;
    
    
    class Slider extends Block {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            add_action ('rest_api_init', array ($this, 'registerRest'));
            
            parent::__construct ();
        } // __construct ()
        
        
        
        
        /**
         *  Register REST API routes.
         */
        public function registerRest () {
            register_rest_route ('fuse-slider/v1', '/options', array(
                'methods' => 'GET',
                'callback' => array ($this, 'getOptions'),
                'permission_callback' => function () {
                    return current_user_can ('edit_posts');
                }
            ));
        } // registerRest ()
        
        
        
        
        /**
         *  Register our block
         */
        public function registerBlock () {
            // Register block script
            wp_register_script (
                'fuse-slider-editor',
                // plugins_url('build/index.js', __FILE__),
                FUSE_BASE_URL.'/blocks/slider/block.js',
                array ('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch')
            );
        
            // Register the block with WordPress
            register_block_type ('fuse-slider/main', array(
                'editor_script' => 'fuse-slider-editor',
                'editor_style' => 'fuse-slider-editor-style',
                'style' => 'fuse-slider-style',
                'render_callback' => array ($this, 'render'),
                'attributes' => array(
                    'selectedOption' => array(
                        'type' => 'string',
                        'default' => '',
                    ),
                    'blockId' => array(
                        'type' => 'string',
                        'default' => '',
                    ),
                ),
            ));
        } // registerBlock ()
        
        /**
         *  Render the tabs container content.
         */
        public function render ($args = array (), $content = '') {
            $selected_slider = isset ($args ['selectedOption']) ? $args ['selectedOption'] : '';
            $output = '';
            
            if (is_numeric ($selected_slider)) {
                $slider = get_post ($selected_slider);
                
                if ($slider && $slider->post_type == 'fuse_slider') {
                    $model = new \Fuse\Model\PostType\Slider ($slider);
                    
                    $output = $model->render ();
                } // if ()
            } // if ()
            
            return $output;
        } // render ()
        
        
        
        
        function getOptions () {
            return $this->_getSectionOptions ();
        } // getOptions ()
        
        
        
        
        /**
         *  Get the options for the sections dropdown select.
         */
        protected function _getSectionOptions () {
            $sliders = get_posts (array (
                'numberposts' => -1,
                'post_type' => 'fuse_slider',
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            $options = array ();
            
            foreach ($sliders as $slider) {
                $options [] = array (
                    'value' => $slider->ID,
                    'label' => $slider->post_title
                );
            } // foreach ()
            
            return $options;
        } // _getSelectOptions ()

    } // class Slider