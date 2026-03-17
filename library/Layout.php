<?php
    /**
     *  @package fuse-cms-framework
     *
     *  This class takes care of our main layout functions.
     */

    namespace Fuse;


    class Layout {

        /**
         *  @var Fuse\Layout\Layout The layout.
         */
        protected $_layout;
        
        
        
        
        /**
         *  @var bool Show the header.
         */
        public $header = false;
        
        /**
         *  @var bool Show the footer.
         */
        public $footer = false;
        
        /**
         *  @var bool Show left sidebar 1.
         */
        public $left_sidebar_1 = false;
        
        /**
         *  @var bool Show left sidebar 2.
         */
        public $left_sidebar_2 = false;
        
        /**
         *  @var bool Show right sidebar 1.
         */
        public $right_sidebar_1 = false;
        
        /**
         *  @var bool Show right sidebar 2.
         */
        public $right_sidebar_2 = false;




        /**
         *  Object constructor.
         */
        public function __construct () {
            $this->_setLayout ();
        } // __construct ()
        
        
        
        
        /**
         *  Get the layout set for this request. Be aware that the layout is not
         *  set until the 'wp' action so that we are sure that we have the right
         *  queried objects to check.
         *
         *  @return Fuse\Layout\Layout|NULL Returns the current layout or NULL
         *  if no layout is set.
         */
        public function getLayout () {
            return $this->_layout;
        } // getLayout ()
        
        
        
        
        /**
         *  Set the layout for the current request.
         */
        protected function _setLayout () {
            $this->_layout = $this->_getCurrentLayout ();
            
            $parts = get_post_meta ($this->_layout, 'fuse_layout_parts', true);
            
            if (is_array ($parts) && array_key_exists ('header', $parts)) {
                $this->header = $parts ['header'];
                $this->footer = $parts ['footer'];
                $this->left_sidebar_1 = $parts ['left_1'];
                $this->left_sidebar_2 = $parts ['left_2'];
                $this->right_sidebar_1 = $parts ['right_1'];
                $this->right_sidebar_2 = $parts ['right_2'];
            } // if ()
        } // _setLayout ()




        /**
         *  Get the page type that is currently being used. This can be a single
         *  post (any post type), category, taxonomy, 404, etc.
         *
         *  return Fuse\Layout\Layout The layout to use.
         */
        protected function _getCurrentLayout () {
            /**
             *  We want to get the correct layout template depending on what
             *  type or page is being displayed.
             */
            if (is_singular () === true) {
                global $post;

                $layout = 0;

                if (!empty ($post)) {
                    $layout = intval (get_post_meta ($post->ID, 'fuse_post_layout', true));
                } // if ()

                if ($layout == 0) {
                    $layout = $this->_getPostTypeLayout ();
                } // if ()
            } // if ()
            elseif (function_exists ('is_shop') && is_shop () === true) {
                // WooCommerce shop page
                $layout = 0;

                $layout = intval (get_post_meta (wc_get_page_id ('shop'), 'fuse_post_layout', true));

                if ($layout == 0) {
                    $layout = $this->_getPostTypeArchiveLayout ();
                } // if ()
            } // elseif ()
            elseif (is_post_type_archive ()) {
                $layout = $this->_getPostTypeArchiveLayout ();
            } // elseif ()
            elseif (is_author () === true) {
                $layout = $this->_getAuthorLayout ();
            } // elseif ()
            elseif (is_archive () === true) {
                $layout = $this->_getArchiveLayout ();
            } // elseif ()
            elseif (is_category () === true) {
                $layout = $this->_getTaxonomyLayout ();
            } // elseif ()
            elseif (is_tag () === true) {
                $layout = $this->_getTaxonomyLayout ();
            } // elseif ()
            elseif (is_tax () === true) {
                $layout = $this->_getTaxonomyLayout ();
            } // elseif ()
            elseif (is_attachment () === true) {
                $layout = $this->_getPostTypeLayout ();
            } // elseif ()
            elseif (is_search () === true) {
                $layout = $this->_getSearchLayout ();
            } // elseif ()
            elseif (is_404 () === true) {
                $layout = $this->_get404Layout ();
            } // elseif ()
            else {
                /**
                 *  We normally won't get to this part, but if we do just use the
                 *  system-default layout.
                 */
                $layout = $this->_getDefaultLayout ();
            } // else
            
            /**
             *  If all else fails or the layout that's set for a page is not valid
             *  we fall back to the default layout.
             */
            if (empty ($layout) === true) {
                $layout = $this->_getDefaultLayout ();
            } // if ()

            return $layout;
        } // getCurrentLayout ()
        
        
        
        
        /**
         *  Get the number of sidebars set for this layout.
         *
         *  return int The sidebar count.
         */
        public function getSidebarCount () {
            $count = 0;
            
            if ($this->left_sidebar_1 !== false) $count++;
            if ($this->left_sidebar_2 !== false) $count++;
            if ($this->right_sidebar_1 !== false) $count++;
            if ($this->right_sidebar_2 !== false) $count++;
            
            return $count;
        } // getsidebarcount ()




        /**
         *  Get the layout for the current post type.
         *
         *  @return Fuse\Layout\Layout The layout to use for the current post
         *  type.
         */
        protected function _getPostTypeLayout () {
            $post_type = $this->_getPostType ();

            $layout = NULL;

            if (strlen ($post_type) > 0) {
                $layout = get_option ('fuse_layout_defaults_posttypes_'.$post_type, 0);
            } // if ()

            if (empty ($layout)) {
                $layout = $this->_getDefaultLayout ();
            } // if ()

            return $layout;
        } // _getPostTypeLayout ()

        /**
         *  Get the layout for the current post type archive.
         *
         *  @return Fuse\Layout\Layout The layout to use for the current post
         *  type archive.
         */
        protected function _getPostTypeArchiveLayout () {
            $post_type = $this->_getPostType ();

            $layout = NULL;

            if (strlen ($post_type) > 0) {
                $layout = get_option ('fuse_layout_defaults_posttypesarchives_'.$post_type, 0);
            } // if ()

            if (empty ($layout)) {
                $layout = $this->_getDefaultLayout ();
            } // if ()

            return $layout;
        } // _getPostTypeArchiveLayout ()

        /**
         *  Get the layout for the taxonomy.
         *
         *  @return Fuse\Layout\Layout The layout for the taxonomy page.
         */
        protected function _getTaxonomyLayout () {
            $qo = get_queried_object ();

            $layout = get_option ('fuse_layout_defaults_taxonomies_'.$qo->taxonomy, 0);

            if ($layout == 0) {
                $layout = $this->_getDefaultLayout ();
            } // if ()

            return $layout;
        } // _getTaxonomyLayout ()

        /**
         *  Get the layout for date-bsed archive pages.
         *
         *  @return Fuse\Layout\Layout The archive page layout.
         */
        protected function _getArchiveLayout () {
            $qo = get_queried_object ();

            if (property_exists ($qo, 'taxonomy') && strlen ($qo->taxonomy) > 0) {
                $layout = $this->_getTaxonomyLayout ();
            } // if ()
            else {
                $layout = get_option ('fuse_layout_defaults_other_archive', 0);

                if ($layout_id > 0) {
                    $layout = $this->_getDefaultLayout ();
                } // if ()
            } // else

            return $layout;
        } // _getARchiveLayout ()

        /**
         *  Get the layout for search pages.
         *
         *  @return Fuse\Layout\Layout The search page layout.
         */
        protected function _getSearchLayout () {
            $layout = get_option ('fuse_layout_defaults_other_search', 0);

            if ($layout == 0) {
                $layout = $this->_getDefaultLayout ();
            } // if ()

            return $layout;
        } // _getSearchLayout ()

        /**
         *  Get the layout for author pages.
         *
         *  @return Fuse\Layout\Layout The author page layout.
         */
        protected function _getAuthorLayout () {
            $layout = get_option ('fuse_layout_defaults_other_author', 0);

            if ($layout == 0) {
                $layout = $this->_getDefaultLayout ();
            } // if ()

            return $layout;
        } // _getAuthorLayout ()

        /**
         *  Get the layout for 404 pages.
         *
         *  @return Fuse\Layout\Layout The 404 page layout.
         */
        protected function _get404Layout () {
            $layout = get_option ('fuse_layout_defaults_other_404', 0);

            if ($layout == 0) {
                $layout = $this->_getDefaultLayout ();
            } // if ()

            return $layout;
        } // _get404Layout ()

        /**
         *  Get the system-default layout.
         *
         *  @return Fuse\Layout\Layout The default layout.
         */
        protected function _getDefaultLayout () {
            global $wpdb;

            $layout = intval (get_option ('fuse_layout_defaults_global', 0));

            if ($layout == 0) {
                trigger_error (__ ('No default layout is set', 'fuse'), E_USER_ERROR);
                $layout = false;
            } // if ()

            return $layout;
        } // _getDefaultLayout ()

        /**
         *  Get the post type layout from the current post.
         *
         *  @return string The post type or NULL if no post type exists.
         */
        protected function _getPostType () {
            global $post;

            $post_type = NULL;

            if ($post && empty ($post->post_type) === false) {
                $post_type = $post->post_type;
            } // if ()

            return $post_type;
        } // _getPostType ()

    } // class Layout