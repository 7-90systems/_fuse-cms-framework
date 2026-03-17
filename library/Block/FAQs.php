<?php
    /**
     *  Set up our FAQS block.
     */
    
    namespace Fuse\Block;
    
    use Fuse\Block;
    
    
    class FAQs extends Block {
        
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
            register_rest_route ('dynamic-select-block/v1', '/options', array(
                'methods' => 'GET',
                'callback' => array ($this, 'dynamic_select_get_options'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                }
            ));
        } // registerRest ()
        
        
        
        
        /**
         *  Register our block
         */
        public function registerBlock () {
            // Register block script
            wp_register_script(
                'dynamic-select-block-editor',
                // plugins_url('build/index.js', __FILE__),
                FUSE_BASE_URL.'/blocks/faqs/block.js',
                array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch')
            );
        
            // Register the block with WordPress
            register_block_type('dynamic-select-block/main', array(
                'editor_script' => 'dynamic-select-block-editor',
                'editor_style' => 'dynamic-select-block-editor-style',
                'style' => 'dynamic-select-block-style',
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
            $selected_option = isset($args['selectedOption']) ? $args['selectedOption'] : '';
            $block_id = isset($args['blockId']) ? $args['blockId'] : 'dynamic-select-' . uniqid();
            
            $section = get_term ($selected_option, 'fuse_faq_section');
            
            $output = '';
            
            if ($section && is_a ($section, 'WP_term')) {
                ob_start ();
                fuse_faqs_list ($section);
                $output = ob_get_contents ();
                ob_end_clean ();
            } // if ()
            
            
            
            
            
            
            
            return $output;
        } // render ()
        
        
        
        
        function dynamic_select_get_options() {
            return $this->_getSectionOptions ();
            // Replace with your own logic to generate options dynamically
            // This is just an example - you could query posts, terms, or external APIs
            
            $options = array(
                array(
                    'value' => 'option1',
                    'label' => 'Option 1',
                ),
                array(
                    'value' => 'option2',
                    'label' => 'Option 2',
                ),
                array(
                    'value' => 'option3',
                    'label' => 'Option 3',
                ),
            );
            
            // Example: Get all published posts as options
            $posts = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'numberposts' => 10,
            ));
            
            foreach ($posts as $post) {
                $options[] = array(
                    'value' => $post->ID,
                    'label' => $post->post_title,
                );
            }
            
            return $options;
        }
        
        
        
        
        /**
         *  Get the options for the sections dropdown select.
         */
        protected function _getSectionOptions ($options = array (), $parent = 0, $indent = 0) {
            $terms = get_terms (array (
                'taxonomy' => 'fuse_faq_section',
                'hide_empty' => false,
                'parent' => $parent
            ));
            
            $spacer = str_repeat ('-', $indent);
            
            foreach ($terms as $term) {
                $options [] = array (
                    'value' => $term->term_id,
                    'label' => $spacer.$term->name
                );
                
                $options = $this->_getSectionOptions ($options, $term->term_id, $indent + 1);
            } // foreach ()
            
            return $options;
        } // _getSelectOptions ()

    } // class FAQs