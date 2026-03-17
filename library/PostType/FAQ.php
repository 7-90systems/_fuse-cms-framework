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
        
        
        
        
        /**
         *  Allow others to over-ride the existing admin list columns.
         *
         *  @param array $columns The existing columns.
         *
         *  @return array The completed column list.
         */
        public function adminListColumns ($columns) {
            $columns ['fuse_faq_order'] = __ ('Display Order', 'fuse');
            
            return $columns;
        } // adminListColumns ()
        
        /**
         *  Output the values for our custom admin list columns.
         *
         *  @param string $column The name of the column
         *  @param int $post_id The ID of the post.
         */
        public function adminListValues ($column, $post_id) {
            switch ($column) {
                case 'fuse_faq_order':
                    $post = get_post ($post_id);
                    echo $post->menu_order;
                    break;
            } // switch ()
        } // adminListValues ()
        
    } // class FAQ