<?php
    /**
     *  This is our FAQs post type.
     */
    
    namespace Fuse\PostType;
    
    use Fuse\PostType;
    
    
    class FAQ extends PostType {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('fuse_faq', 'Question', 'FAQs', array (
                'public' => false,
                'publicly_queryable' => false,
                'show_in_rest' => true,
                'show_in_menu' => true,
                'show_ui' => true,
                'menu_icon' => 'dashicons-editor-help',
                'supports' => array (
                    'title',
                    'editor',
                    'page-attributes'
                ),
                'text_domain' => 'fuse'
            ));
        } // __construct ()
        
        
        
        
        /**
         *  Register our taxonomies.
         */
        public function registerTaxonomies () {
            register_taxonomy ('fuse_faq_section', $this->getSlug (), array (
                'labels' => array (
                    'name' => __ ('FAQ Sections', 'fuse'),
                    'singular_name' => __('Section', 'fuse')
                ),
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
                'hierarchical' => true,
                'show_admin_column' =>true
            ));
        } // registerTaxonomies ()
        
    } // class FAQ