<?php
    /**
     *  @package fusecms
     *
     *  This model takes care of the custom post type builder functions.
     */
    
    namespace Fuse\Model\PostType;
    
    use Fuse\Model;
    
    
    class Builder extends Model {
        
        /**
         *  Get the labels for this post type.
         *
         *  @return array The label values.
         */
        public function getLabels () {
            $label_values = $this->fuse_posttype_builder_labels;
            
            if (is_array ($label_values) === false) {
                $label_values = array ();
            } // if ()
            
            $labels = $this->_labels ();
            
            foreach ($labels as $key => $label) {
                $labels [$key]['value'] = array_key_exists ($key, $label_values) ? $label_values [$key] : '';
            } // foreach ()
            
            return $labels;
        } // getLabels ()
        
        /**
         *  Get the settings for this post type.
         *
         *  @return array The settings.
         */
        public function getSettings () {
            $setting_values = $this->fuse_posttype_builder_settings;
            
            if (is_array ($setting_values) === false) {
                $setting_values = array ();
            } // if ()
            
            $settings = $this->_settings ();
            
            foreach ($settings as $key => $label) {
                $settings [$key]['value'] = array_key_exists ($key, $setting_values) ? $setting_values [$key] : '';
            } // foreach ()
            
            return $settings;
        } // getSettings ()
        
        
        
        
        /**
         *  This is the list of labels for post types.
         *
         *  @return array The list of labels.
         */
        protected function _labels () {
            return array (
                'name' => array (
                    'type' => 'basic',
                    'label' => __ ('General name for the post type, usually plural', 'fuse'),
                ),
                'singular_name' => array (
                    'type' => 'basic',
                    'label' => __ ('Name for one object of this post type', 'fuse'),
                ),
                'add_new' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for adding a new item', 'fuse'),
                    'placeholder' => __ ('Add New Post/Page', 'fuse')
                ),
                'add_new_item' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for adding a new singular item', 'fuse'),
                    'placeholder' => __ ('Add New Post/Page', 'fuse')
                ),
                'edit_item' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for editing a singular item', 'fuse'),
                    'placeholder' => __ ('Edit Post/Page', 'fuse')
                ),
                'new_item' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the new item page title', 'fuse'),
                    'placeholder' => __ ('New Post/Page', 'fuse')
                ),
                'view_item' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for viewing a singular item', 'fuse'),
                    'placeholder' => __ ('View Post/Page', 'fuse')
                ),
                'view_items' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for viewing post type archives', 'fuse'),
                    'placeholder' => __ ('View Posts/Pages', 'fuse')
                ),
                'search_items' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for searching plural items', 'fuse'),
                    'placeholder' => __ ('Search Posts/Pages', 'fuse')
                ),
                'not_found' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used when no items are found', 'fuse'),
                    'placeholder' => __ ('No posts/pages found', 'fuse')
                ),
                'not_found_in_trash' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used when no items are in the Trash', 'fuse'),
                    'placeholder' => __ ('No posts/pages found in Trash', 'fuse')
                ),
                'parent_item_colon' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used to prefix parents of hierarchical items', 'fuse'),
                    'placeholder' => __ ('Parent Page:', 'fuse')
                ),
                'all_items' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label to signify all items in a submenu link', 'fuse'),
                    'placeholder' => __ ('All Posts/Pages', 'fuse')
                ),
                'archives' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for archives in nav menus', 'fuse'),
                    'placeholder' => __ ('Post/Page Archives', 'fuse')
                ),
                'attributes' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the attributes meta box', 'fuse'),
                    'placeholder' => __ ('Post/Page Attributes', 'fuse')
                ),
                'insert_into_item' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the media frame button', 'fuse'),
                    'placeholder' => __ ('Insert into post/page', 'fuse')
                ),
                'uploaded_to_this_item' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the media frame filter', 'fuse'),
                    'placeholder' => __ ('Uploaded to this post/page', 'fuse')
                ),
                'featured_image' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the featured image meta box title', 'fuse'),
                    'placeholder' => __ ('Featured image', 'fuse')
                ),
                'set_featured_image' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for setting the featured image', 'fuse'),
                    'placeholder' => __ ('Set featured image', 'fuse')
                ),
                'remove_featured_image' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for removing the featured image', 'fuse'),
                    'placeholder' => __ ('Remove featured image', 'fuse')
                ),
                'use_featured_image' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label in the media frame for using a featured image', 'fuse'),
                    'placeholder' => __ ('Use as featured image', 'fuse')
                ),
                'menu_name' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the menu name', 'fuse'),
                    'placeholder' => __ ('Default is the same as name', 'fuse')
                ),
                'filter_items_list' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the table views hidden heading', 'fuse'),
                    'placeholder' => __ ('Filter posts/pages list', 'fuse')
                ),
                'filter_by_date' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the date filter in list tables', 'fuse'),
                    'placeholder' => __ ('Filter by date', 'fuse')
                ),
                'items_list_navigation' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the table pagination hidden heading', 'fuse'),
                    'placeholder' => __ ('Posts/Pages list navigation', 'fuse')
                ),
                'items_list' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label for the table hidden heading', 'fuse'),
                    'placeholder' => __ ('Posts/Pages list', 'fuse')
                ),
                'item_published' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used when an item is published', 'fuse'),
                    'placeholder' => __ ('Post/Page published', 'fuse')
                ),
                'item_published_privately' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used when an item is published with private visibility', 'fuse'),
                    'placeholder' => __ ('Post/Page published privately', 'fuse')
                ),
                'item_reverted_to_draft' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used when an item is switched to a draft', 'fuse'),
                    'placeholder' => __ ('Post/Page reverted to draft', 'fuse')
                ),
                'item_trashed' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used when an item is moved to Trash', 'fuse'),
                    'placeholder' => __ ('Post/Page trashed', 'fuse')
                ),
                'item_scheduled' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used when an item is scheduled for publishing', 'fuse'),
                    'placeholder' => __ ('Post/Page scheduled', 'fuse')
                ),
                'item_updated' => array (
                    'type' => 'advanced',
                    'label' => __ ('Label used when an item is updated', 'fuse'),
                    'placeholder' => __ ('Post/Page updated', 'fuse')
                ),
                'item_link' => array (
                    'type' => 'advanced',
                    'label' => __ ('Title for a navigation link block variation', 'fuse'),
                    'placeholder' => __ ('Post/Page Link', 'fuse')
                ),
                'item_link_description' => array (
                    'type' => 'advanced',
                    'label' => __ ('Description for a navigation link block variation', 'fuse'),
                    'placeholder' => __ ('A link to a new post/page', 'fuse')
                )
            );
        } // _labels ()
        
        /**
         *  Get the settings fields.
         */
        protected function _settings () {
            return array (
                'description' => array (
                    'type' => 'text',
                    'label' => 'Description'
                ),
                'public' => array (
                    'type' => 'toggle',
                    'label' => 'Public',
                    'default' => 'yes'
                ),
                'hierarchical' => array (
                    'type' => 'toggle',
                    'label' => 'Hierarchical'
                ),
                'exclude_from_search' => array (
                    'type' => 'toggle',
                    'label' => 'Exclude from search'
                ),
                'publicly_queryable' => array (
                    'type' => 'toggle',
                    'label' => 'Publicly queryable'
                ),
                'show_in_rest' => array (
                    'type' => 'toggle',
                    'label' => 'Show in REST API'
                ),
                'show_in_menu' => array (
                    'type' => 'text',
                    'label' => 'Show under menu item'
                ),
                'menu_icon' => array (
                    'type' => 'text',
                    'label' => 'Menu icon'
                ),
                'menu_position' => array (
                    'type' => 'number',
                    'label' => 'Menu position'
                ),
                'supports' => array (
                    'type' => 'options',
                    'label' => 'Supports',
                    'options' => array (
                        'title' => __ ('Title', 'fuse'),
                        'editor' => __ ('Editor', 'fuse'),
                        'excerpt' => __ ('Excerpt', 'fuse'),
                        'page-attributes' => __ ('Page attributes', 'fuse'),
                        'thumbnail' => __ ('Featured image (thumbnail)', 'fuse'),
                        'comments' => __ ('Comments', 'fuse'),
                        'author' => __ ('Author', 'fuse'),
                        'revisions' => __ ('Revisions', 'fuse'),
                        'trackbacks' => __ ('Trackbacks', 'fuse')
                    ),
                    'default' => array (
                        'title',
                        'editor'
                    )
                ),
                'has_archive' => array (
                    'type' => 'toggle',
                    'label' => 'Has archive'
                ),
                'rewrite' => array (
                    'type' => 'toggle',
                    'label' => 'Rewrite'
                ),
                'show_ui' => array (
                    'type' => 'toggle',
                    'label' => 'Show UI'
                ),
                'show_in_nav_menus' => array (
                    'type' => 'toggle',
                    'label' => 'Show in navigation menus'
                ),
                'show_in_admin_bar' => array (
                    'type' => 'toggle',
                    'label' => 'Show in admin bar'
                )
            );
        } // _settings ()
        
    } // class Builder