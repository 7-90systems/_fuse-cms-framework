<?php
    /**
     *  @package fuse-cms-framework
     *
     *  This is our base for figuring out what files to enqueue.
     *
     *  This is extended by the JavaScript and Css classes.
     */
    
    namespace Fuse\Setup\Theme;
    
    
    abstract class Enqueue {
        
        /**
         *  @var string The file extension that we will search for.
         */
        protected $_file_extension;
        
        /**
         *  @var array The URI locations of the folders that we will search for
         *  files in.
         */
        protected $_base_folder_uri;
        
        /**
         *  @var array This array holds the files that we have found for this
         *  search.
         */
        protected $_files = array ();
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $file_extension This is the file extension that we
         *  will search for. Please include the leading period as not providing
         *  this may produce unexpected results.
         */
        public function __construct ($file_extension) {
            $this->_file_extension = $file_extension;
        } // __construct ()
        
        
        
        
        /**
         *  Set the folders that we will search in.
         */
        abstract protected function _setFolders ();
        
        /**
         *  Enqueue all of our files as required.
         */
        abstract protected function _enqueue ();
        
        
        
        
        /**
         *  Load the files in the resource directory.
         *
         *  @return Fuse\Setup\Theme\Enqueue This object.
         */
        public function load () {
            $this->_setFolders ();
            
            $this->_files = array ();
            
            $extension_length = strlen ($this->_file_extension);
                    
            $default_index = 1;
            
            foreach ($this->_base_folder_uri as $location) {
                $path = $location ['path'];
                $url = $location ['url'];
                
                if (file_exists ($path)) {
                    $path_string_length = strlen ($path);
                    $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator ($path, \RecursiveDirectoryIterator::SKIP_DOTS));
                   
                    $files = array ();
                   
                    foreach ($rii as $file) {
                        if ($file->isDir () === false) {
                            $files [] = substr ($file->getPathname (), $path_string_length);
                        } // if ()
                    } // foreach ()
					
					/**
					 *	Don't trust the file system to get the order right... yes it has happened!
					 */
					sort ($files);
                    
                    /**
                     *  For JavaScript files, the .js file is found after the .dep file, so reverse the array.
                     */
                    if ($this->_file_extension == '.js') {
                        $files = array_reverse ($files);
                    } // if ()
                   
                    foreach ($files as $file) {
                        $id = trim ($file, '\\/');
                        $id = substr ($id, 0, strpos ($id, '.'));
                        $id = str_replace (array ('\\', '/'), '_', $id);
                        
                        // We want to have all of the 'default' files included
                        if ($id == 'default' && substr ($file, -4, 4) != '.dep') {
                            $id.= '_'.$default_index;
                            $default_index++;
                        } // if ()
                       
                        if (substr ($file, $extension_length * -1, $extension_length) == $this->_file_extension) {
                            $this->_files [$id] = array (
                                'file' => $url.str_replace ('\\', '/', $file),
                                'deps' => array ()
                            );
                        } // if ()
                        elseif (substr ($file, -4, 4) == '.dep') {
                            if (array_key_exists ($id, $this->_files)) {
                                $this->_files [$id]['deps'] = explode ('|', file_get_contents ($path.$file));
                            } // if ()
                            elseif ($id == 'default' && array_key_exists ('default_1', $this->_files)) {
                                $this->_files ['default_1']['deps'] = explode ('|', file_get_contents ($path.$file));
                            } // elseif ()
                        } // elseif ()
                    } // foreach ()
                } // if ()
            } // foreach ()
            
            return $this;
        } // load ()
        
        
        
        
        /**
         *  Get the files that we have loaded.
         *
         *  @return array The files that we have found.
         */
        public function getRequiredFiles () {
            $this->_enqueue ();
            
            $files = array ();
            
            // Add our common files
            foreach ($this->_files as $alias => $file) {
                if (substr ($alias, 0, 7) == 'default') {
                    $files [$alias] = $file;
                } // if ()
            } // foreach ()
            
            // Header & Footer
            if (array_key_exists ('header', $this->_files)) {
                $files ['header'] = $this->_files ['header'];
            } // if ()
            
            if (array_key_exists ('footer', $this->_files)) {
                $files ['footer'] = $this->_files ['footer'];
            } // if ()
            
            if (is_singular ()) {
                // Get files for the post type and post
                global $post;
                
                $post_type = get_post_type ();
                $page_uri = get_page_uri ($post);
                
                // Overall post type
                if (array_key_exists ('posttype_'.$post_type, $this->_files)) {
                    $files ['posttype_'.$post_type] = $this->_files ['posttype_'.$post_type];
                } // if ()
                
                // Post slug
                $search_slug = $post_type.'_'.str_replace (array ('\\', '/'), '_', $page_uri);
                
                if (array_key_exists ($search_slug, $this->_files)) {
                    $files [$search_slug] = $this->_files [$search_slug];
                } // if ()
                
                // ID
                $search_slug = $post_type.'_'.$post->ID;
                
                if (array_key_exists ($search_slug, $this->_files)) {
                    $files [$search_slug] = $this->_files [$search_slug];
                } // if ()
                
                // Front page?
                if ((is_front_page () || is_home ()) && array_key_exists ('page_home', $this->_files)) {
                    $files ['page_home'] = $this->_files ['page_home'];
                } // if ()
                
                // Blocks
                $content = get_the_content ();
                
                foreach (parse_blocks ($content) as $block) {
                    if (strlen ($block ['blockName'].'') > 0) {
                        $name = 'blocks_'.str_replace ('/', '_', $block ['blockName']);
                        
                        if (array_key_exists ($name, $this->_files)) {
                            $files [$name] = $this->_files [$name];
                        } // if ()
                    } // if ()
                } // foreach ()
                
                // Shortcodes
                foreach ($this->_parseShortcodes ($content) as $shortcode) {
                    if(array_key_exists ('shortcode_'.$shortcode, $this->_files)) {
                        $files ['shortcode_'.$shortcode] = $this->_files ['shortcode_'.$shortcode];
                    } // if ()
                } // foreach ()
            } // if ()
            elseif (is_post_type_archive ()) {
                // Get the files for this post type archive
                $type = get_queried_object ()->name;
                
                if (array_key_exists ('posttypearchive_'.$type, $this->_files)) {
                    $files ['posttypearchive_'.$type] = $this->_files ['posttypearchive_'.$type];
                } // if ()
            } // elseif ()
            elseif (is_category ()) {
                $slug = get_queried_object ()->slug;

                // Get files for the category
                if (array_key_exists ('taxonomy_category', $this->_files)) {
                    $files ['taxonomy_category'] = $this->_files ['taxonomy_category'];
                } // if ()
                
                if (array_key_exists ('taxonomy_category_'.$slug, $this->_files)) {
                    $files ['taxonomy_category_'.$slug] = $this->_files ['taxonomy_category_'.$slug];
                } // if ()
            } // elseif ()
            elseif (is_tax ()) {
                // Get files for the taxonomy
                if (function_exists ('is_product_category') && is_product_category ()) {
                    $type = 'product_cat';
                    $slug = get_query_var ('product_cat');
                } // if ()
                else {
                    $type = get_query_var ('taxonomy');
                    $slug = get_query_var ('term');
                } // else
                    

                if (array_key_exists ('taxonomy_'.$type, $this->_files)) {
                    $files ['taxonomy_'.$type] = $this->_files ['taxonomy_'.$type];
                } // if ()
                
                if (array_key_exists ('taxonomy_'.$type.'_'.$slug, $this->_files)) {
                    $files ['taxonomy_'.$type.'_'.$slug] = $this->_files ['taxonomy_'.$type.'_'.$slug];
                } // if ()
            } // elseif ()
            elseif (is_tag ()) {
                $tag = get_queried_object ()->slug;
                
                if (array_key_exists ('tag_tag', $this->_files)) {
                    $files ['tag_tag'] = $this->_files ['tag_tag'];
                } // if ()

                if (array_key_exists ('tag_'.$tag, $this->_files)) {
                    $files ['tag_'.$tag] = $this->_files ['tag_'.$tag];
                } // if ()
            } // elseif ()
            elseif (is_404 ()) {
                // Get the 404 files
                if (array_key_exists ('404', $this->_files)) {
                    $files ['404'] = $this->_files ['404'];
                } // if ()
            } // elseif ()
            
            return $files;
        } // getFiles ()
        
        
        
        
        /**
         *  Parse the given text to return any shortcodes.
         *
         *  @param string $content The content to parse.
         *
         *  @return array An array of shortcodes.
         */
        protected function _parseShortcodes ($content) {
            global $shortcode_tags;
            
            if (false === strpos ($content, '[') || empty ($shortcode_tags) || !is_array ($shortcode_tags)) {
                $shortcodes = array ();
            } // if ()
            else {
                preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
                $shortcodes = $matches [1];
            } // else
            
            return $shortcodes;
        } // _parseShortcodes ()
        
    } // class Enqueue