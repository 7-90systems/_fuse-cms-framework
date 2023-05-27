<?php
    /**
     *  @package fuse-cms-framework
     *
     *  Set up our CSS enqueues.
     *
     *  @filter fuse_enqueue_css_folder_locations
     */
    
    namespace Fuse\Setup\Theme\Enqueue;
    
    use Fuse\Setup\Theme\Enqueue;
    
    
    class Css extends Enqueue {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('.css');
        } // __construct ()
        
        
        
        
        /**
         *  Set the folders that we will search in.
         */
        protected function _setFolders () {
            $folders = array (
                array (
                    'path' => get_template_directory ().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css',
                    'url' => untrailingslashit (get_template_directory_uri ().'/assets/css')
                )
            );
            
            if (is_child_theme ()) {
                $folders [] = array (
                    'path' => get_stylesheet_directory ().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css',
                    'url' => untrailingslashit (get_stylesheet_directory_uri ().'/assets/css')
                );
            } // if ()
            
            $this->_base_folder_uri = apply_filters ('fuse_enqueue_css_folder_locations', $folders);
        } // _setFolders ()
        
        
        
        
        /**
         *  Enqueue our JavaScript files.
         */
        protected function _enqueue () {
            // Are we using our layout CSS files?
            if (get_fuse_option ('theme_css_layout', 'no') == 'yes') {
                wp_enqueue_style ('fuse_theme_layout', FUSE_BASE_URL.'/assets/css/layout/layout.css');
            } // if ()
            
            if (get_fuse_option ('theme_css_buttons', 'no') == 'yes') {
                wp_enqueue_style ('fuse_theme_buttons', FUSE_BASE_URL.'/assets/css/layout/buttons.css', array  ('dashicons'));
                wp_enqueue_script ('fuse_theme_buttons', FUSE_BASE_URL.'/assets/javascript/buttons.js', array  ('jquery'));
            } // if ()
            
            // Load our normal stylesheets
            foreach ($this->_files as $alias => $file) {
                wp_register_style ($alias, $file ['file'], $file ['deps']);
            } // foreach ()
        } // _enqueue ()
        
    } // class Css