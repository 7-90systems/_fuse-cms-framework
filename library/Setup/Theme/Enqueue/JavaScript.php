<?php
    /**
     *  @package fuse-cms-framework
     *
     *  Set up our JavaScript enqueues.
     *
     *  @filter fuse_enqueue_javascript_folder_locations
     */
    
    namespace Fuse\Setup\Theme\Enqueue;
    
    use Fuse\Setup\Theme\Enqueue;
    
    
    class JavaScript extends Enqueue {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('.js');
        } // __construct ()
        
        
        
        
        /**
         *  Set the folders that we will search in.
         */
        protected function _setFolders () {
            $folders = array (
                array (
                    'path' => get_template_directory ().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'javascript',
                    'url' => untrailingslashit (get_template_directory_uri ().'/assets/'.'javascript')
                )
            );
            
            if (is_child_theme ()) {
                $folders [] = array (
                    'path' => get_stylesheet_directory ().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'javascript',
                    'url' => untrailingslashit (get_stylesheet_directory_uri ().'/assets/'.'javascript')
                );
            } // if ()
            
            $this->_base_folder_uri = apply_filters ('fuse_enqueue_javascript_folder_locations', $folders);
        } // _setFolders ()
        
        
        
        
        /**
         *  Enqueue our JavaScript files.
         */
        protected function _enqueue () {
            // Are we using the GSAP animations?
            if (get_fuse_option ('gsap', 'no') == 'yes') {
                $settings = \Fuse\Settings\Form\GSAP::getInstance ();
                
                wp_enqueue_script ('fuse_gsap', FUSE_BASE_URL.'/assets/external/gsap-public/minified/gsap.min.js');
                
                foreach ($settings->modules as $key => $label) {
                    if (get_fuse_option ('gsap_module_'.$key, 'no') == 'yes') {
                        wp_enqueue_script ('fuse_gsap_'.$key, FUSE_BASE_URL.'/assets/external/gsap-public/minified/'.$key.'.min.js', array ('fuse_gsap'));
                    } // if ()
                } // foreach ()
            } // if ()
            
            foreach ($this->_files as $alias => $file) {
                wp_register_script ($alias, $file ['file'], $file ['deps']);
            } // foreach ()
        } // _enqueue ()
        
    } // class JavaScript