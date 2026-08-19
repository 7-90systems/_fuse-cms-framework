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
         *  The alias names and prefixes a stylesheet or script can be found by.
         *
         *  A file's name becomes its alias, and the alias is what decides when
         *  the file is used. They are named here so that everything matching on
         *  them -- the front end, and the editor -- is reading from one list
         *  rather than repeating the strings.
         */
        const ALIAS_DEFAULT = 'default';
        const ALIAS_HEADER = 'header';
        const ALIAS_FOOTER = 'footer';
        const ALIAS_FRONT_PAGE = 'page_home';
        const ALIAS_NOT_FOUND = '404';
        const ALIAS_POST_TYPE = 'posttype_';
        const ALIAS_POST_TYPE_ARCHIVE = 'posttypearchive_';
        const ALIAS_TAXONOMY = 'taxonomy_';
        const ALIAS_TAG = 'tag_';
        const ALIAS_BLOCK = 'blocks_';
        const ALIAS_SHORTCODE = 'shortcode_';




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
                                $this->_files [$id]['deps'] = $this->_readDependencyFile ($path.$file);
                            } // if ()
                            elseif ($id == 'default' && array_key_exists ('default_1', $this->_files)) {
                                $this->_files ['default_1']['deps'] = $this->_readDependencyFile ($path.$file);
                            } // elseif ()
                        } // elseif ()
                    } // foreach ()
                } // if ()
            } // foreach ()
            
            return $this;
        } // load ()




        /**
         *  Read a .dep file and return the handles it lists.
         *
         *  @param string $file The full path to the .dep file.
         *
         *  @return array The dependency handles, or an empty array if the file
         *  cannot be read.
         */
        protected function _readDependencyFile ($file) {
            if (file_exists ($file) === false || is_readable ($file) === false) {
                return array ();
            } // if ()

            return $this->_parseDependencies (file_get_contents ($file));
        } // _readDependencyFile ()

        /**
         *  Parse the contents of a .dep file into a list of handles.
         *
         *  A .dep file may separate its handles with pipes, with line breaks,
         *  or with both:
         *
         *      superfish|mmenulight
         *
         *      superfish
         *      mmenulight
         *
         *      superfish|mmenulight
         *      colorbox
         *
         *  All three give the same result. Every line is still split on the
         *  pipe, so the original single-line format keeps working exactly as it
         *  did, and a file written either way -- or both ways at once -- is
         *  read correctly.
         *
         *  Handles are trimmed, so a trailing newline or a stray space no
         *  longer produces a broken handle, and blanks and duplicates are
         *  dropped.
         *
         *  @param string $contents The raw contents of the file.
         *
         *  @return array The dependency handles.
         */
        protected function _parseDependencies ($contents) {
            // file_get_contents () returns false on failure.
            if (is_string ($contents) === false) {
                return array ();
            } // if ()

            /**
             *  Split on pipes and on either kind of line ending, treating a run
             *  of separators as one so blank lines and empty entries fall out.
             */
            $handles = preg_split ('/[|\r\n]+/', $contents);

            if (is_array ($handles) === false) {
                return array ();
            } // if ()

            $dependencies = array ();

            foreach ($handles as $handle) {
                $handle = trim ($handle);

                if ($handle !== '' && in_array ($handle, $dependencies, true) === false) {
                    $dependencies [] = $handle;
                } // if ()
            } // foreach ()

            return $dependencies;
        } // _parseDependencies ()




        /**
         *  Get every file that load () found, without enqueueing anything.
         *
         *  getRequiredFiles () narrows the list down to the current request and
         *  enqueues as it goes, both of which need front-end query context. The
         *  editor has no such context, so it reads the raw list from here and
         *  decides for itself.
         *
         *  @return array The files found, keyed by alias.
         */
        public function getFiles () {
            return $this->_files;
        } // getFiles ()




        /**
         *  Get the aliases that apply to a single post.
         *
         *  A stylesheet is matched to a request by its file name, which becomes
         *  its alias. This builds the aliases one post answers to, so that the
         *  front end and the editor agree on what applies to it without each
         *  working the names out for itself.
         *
         *  @param \WP_Post $post The post.
         *
         *  @return array The aliases, most general first.
         */
        public function getPostAliases ($post = NULL) {
            if (is_object ($post) === false || isset ($post->ID) === false) {
                return array ();
            } // if ()

            $post_type = $post->post_type;

            // Everything of this post type.
            $aliases = array (
                self::ALIAS_POST_TYPE.$post_type
            );

            // This post, by its path.
            $page_uri = get_page_uri ($post);

            if (is_string ($page_uri) && $page_uri !== '') {
                $aliases [] = $post_type.'_'.str_replace (array ('\\', '/'), '_', $page_uri);
            } // if ()

            // This post, by ID.
            $aliases [] = $post_type.'_'.$post->ID;

            // The front page, which gets a name of its own.
            if (intval (get_option ('page_on_front')) === intval ($post->ID)) {
                $aliases [] = self::ALIAS_FRONT_PAGE;
            } // if ()

            /**
             *  A front page whose path is literally 'home' produces page_home
             *  twice, once from its path and once from the rule above.
             */
            return array_values (array_unique ($aliases));
        } // getPostAliases ()

        /**
         *  Get the alias for a block.
         *
         *  @param string $block_name The registered block name.
         *
         *  @return string The alias.
         */
        public function getBlockAlias ($block_name) {
            return self::ALIAS_BLOCK.str_replace ('/', '_', $block_name);
        } // getBlockAlias ()

        /**
         *  Get the alias for a shortcode.
         *
         *  @param string $shortcode The shortcode tag.
         *
         *  @return string The alias.
         */
        public function getShortcodeAlias ($shortcode) {
            return self::ALIAS_SHORTCODE.$shortcode;
        } // getShortcodeAlias ()




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

                // The post type, the post path, the post ID.
                foreach ($this->getPostAliases ($post) as $alias) {
                    // The front page is dealt with below, for every kind of home.
                    if ($alias == self::ALIAS_FRONT_PAGE) {
                        continue;
                    } // if ()

                    if (array_key_exists ($alias, $this->_files)) {
                        $files [$alias] = $this->_files [$alias];
                    } // if ()
                } // foreach ()

                // Blocks
                $content = get_the_content ();

                foreach (parse_blocks ($content) as $block) {
                    if (strlen ($block ['blockName'].'') > 0) {
                        $name = $this->getBlockAlias ($block ['blockName']);

                        if (array_key_exists ($name, $this->_files)) {
                            $files [$name] = $this->_files [$name];
                        } // if ()
                    } // if ()
                } // foreach ()

                // Shortcodes
                foreach ($this->_parseShortcodes ($content) as $shortcode) {
                    $name = $this->getShortcodeAlias ($shortcode);

                    if (array_key_exists ($name, $this->_files)) {
                        $files [$name] = $this->_files [$name];
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
                if (array_key_exists (self::ALIAS_NOT_FOUND, $this->_files)) {
                    $files [self::ALIAS_NOT_FOUND] = $this->_files [self::ALIAS_NOT_FOUND];
                } // if ()
            } // elseif ()

            /**
             *  The home page, checked on its own rather than inside any of the
             *  branches above.
             *
             *  A home page is only a single post when the site is set to show a
             *  static page there. Left as "your latest posts", or with a
             *  separate posts page, the home page is an archive and is_singular
             *  () is false -- so a page_home stylesheet used to be skipped
             *  entirely on exactly the sites most likely to have one.
             *
             *  It is added last so it wins over the more general files.
             */
            if (is_front_page () || is_home ()) {
                if (array_key_exists (self::ALIAS_FRONT_PAGE, $this->_files)) {
                    $files [self::ALIAS_FRONT_PAGE] = $this->_files [self::ALIAS_FRONT_PAGE];
                } // if ()
            } // if ()

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