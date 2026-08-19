<?php
    /**
     *  @package fusecms
     *
     *  Feeds the front-end stylesheets into the editor, so that what is being
     *  written looks like what will be published.
     *
     *  The styles are scoped so they cannot leak out onto the admin screen
     *  around the content. How that is done differs between the two editors:
     *
     *  Block editor -- the stylesheet contents are handed to the editor through
     *  the block_editor_settings_all filter, as 'theme' type styles. The editor
     *  rewrites every selector in those to sit under .editor-styles-wrapper, so
     *  a rule for .button only ever reaches a .button inside the content area
     *  and never the Publish button. This only happens when the theme declares
     *  add_theme_support ('editor-styles'), which Fuse does.
     *
     *  Enqueueing the same CSS on enqueue_block_editor_assets instead would put
     *  it on the page unscoped, which is what makes button and layout styles
     *  bleed over the editor chrome.
     *
     *  Classic editor -- the stylesheets are added to the mce_css list, which
     *  TinyMCE loads inside its own iframe. An iframe is its own document, so
     *  nothing there can reach the page around it.
     *
     *  The contents are read from disk rather than passed as URLs. Passing a
     *  full URL to add_editor_style () makes WordPress fetch it back over HTTP
     *  on every editor load -- see get_block_editor_theme_styles () in core.
     *
     *  @filter fuse_editor_stylesheets
     */

    namespace Fuse\Setup\Theme;


    class EditorStyles {

        /**
         *  @var Fuse\Setup\Theme\Enqueue\Css The CSS file finder.
         */
        protected $_css_enqueue;




        /**
         *  Object constructor.
         *
         *  @param Fuse\Setup\Theme\Enqueue\Css $css_enqueue The CSS finder the
         *  theme set up, so the editor sees the same files the front end does.
         */
        public function __construct ($css_enqueue = NULL) {
            $this->_css_enqueue = $css_enqueue;

            // Block editor.
            add_filter ('block_editor_settings_all', array ($this, 'addBlockEditorStyles'), 10, 2);

            // Classic editor.
            add_filter ('mce_css', array ($this, 'addClassicEditorStyles'));
        } // __construct ()




        /**
         *  Add our stylesheets to the block editor.
         *
         *  @param array $settings The editor settings.
         *  @param \WP_Block_Editor_Context $context The editor context.
         *
         *  @return array The settings with our styles added.
         */
        public function addBlockEditorStyles ($settings, $context = NULL) {
            if (is_array ($settings) === false) {
                return $settings;
            } // if ()

            if (array_key_exists ('styles', $settings) === false || is_array ($settings ['styles']) === false) {
                $settings ['styles'] = array ();
            } // if ()

            foreach ($this->getStylesheets ($this->_getEditedPost ($context)) as $stylesheet) {
                $css = $this->_read ($stylesheet ['path']);

                if ($css === '') {
                    continue;
                } // if ()

                /**
                 *  'theme' is what tells the editor to rewrite the selectors so
                 *  they only apply inside the content area. baseURL is what
                 *  keeps relative url () paths -- fonts, images -- resolving.
                 */
                $settings ['styles'][] = array (
                    'css' => $css,
                    'baseURL' => $stylesheet ['url'],
                    '__unstableType' => 'theme',
                    'isGlobalStyles' => false
                );
            } // foreach ()

            return $settings;
        } // addBlockEditorStyles ()




        /**
         *  Add our stylesheets to the classic editor.
         *
         *  @param string $mce_css The comma separated stylesheet list.
         *
         *  @return string The list with ours appended.
         */
        public function addClassicEditorStyles ($mce_css) {
            $urls = array ();

            foreach ($this->getStylesheets ($this->_getEditedPost ()) as $stylesheet) {
                /**
                 *  TinyMCE caches aggressively, so the file's modified time is
                 *  added to make an edited stylesheet show up.
                 */
                $urls [] = add_query_arg ('ver', $this->_getVersion ($stylesheet ['path']), $stylesheet ['url']);
            } // foreach ()

            if (empty ($urls)) {
                return $mce_css;
            } // if ()

            if (is_string ($mce_css) === false || trim ($mce_css) === '') {
                return implode (',', $urls);
            } // if ()

            return $mce_css.','.implode (',', $urls);
        } // addClassicEditorStyles ()




        /**
         *  Get the stylesheets the editor should show.
         *
         *  This is the same set the front end loads: the framework's own
         *  optional stylesheets, everything found in the theme's css folders,
         *  and the theme's style.css last so it still wins.
         *
         *  @param \WP_Post $post The post being edited, where known.
         *
         *  @return array Each entry has 'path' and 'url'.
         */
        public function getStylesheets ($post = NULL) {
            $stylesheets = array ();

            // The framework's optional front-end stylesheets.
            if (get_fuse_option ('theme_css_layout', 'no') == 'yes') {
                $stylesheets [] = $this->_pluginStylesheet ('layout/layout.css');
            } // if ()

            if (get_fuse_option ('theme_css_buttons', 'no') == 'yes') {
                $stylesheets [] = $this->_pluginStylesheet ('layout/buttons.css');
            } // if ()

            if (get_fuse_option ('sliders_posttype', 'no') == 'yes') {
                $stylesheets [] = $this->_pluginStylesheet ('sliders.css');
            } // if ()

            // Everything the theme's css folders hold.
            $stylesheets = array_merge ($stylesheets, $this->_getThemeStylesheets ($post));

            // The theme's own stylesheet, last so it can still override.
            foreach ($this->_getThemeRoots () as $root) {
                if (file_exists ($root ['path'].DIRECTORY_SEPARATOR.'style.css')) {
                    $stylesheets [] = array (
                        'path' => $root ['path'].DIRECTORY_SEPARATOR.'style.css',
                        'url' => trailingslashit ($root ['url']).'style.css'
                    );
                } // if ()
            } // foreach ()

            $stylesheets = apply_filters ('fuse_editor_stylesheets', $stylesheets, $post);

            return $this->_filterReadable ($stylesheets);
        } // getStylesheets ()




        /**
         *  Get the discovered theme stylesheets that make sense in the editor.
         *
         *  The finder keys files by when they apply -- 'default', 'header',
         *  'posttype_page' and so on. The editor is showing one post, so it
         *  wants the files that always apply plus the ones for that post type.
         *  Files tied to an archive or a taxonomy are left out; they would
         *  never be right for a single post.
         *
         *  @param string $post_type The post type being edited.
         *
         *  @return array Each entry has 'path' and 'url'.
         */
        protected function _getThemeStylesheets ($post = NULL) {
            if (is_object ($this->_css_enqueue) === false) {
                return array ();
            } // if ()

            $this->_css_enqueue->load ();

            $for_this_post = $this->_getPostAliases ($post);
            $wanted = array ();

            foreach ($this->_css_enqueue->getFiles () as $alias => $file) {
                /**
                 *  The files that always apply, plus every block and shortcode
                 *  file -- any of those can be inserted while editing, so the
                 *  editor wants them all rather than only the ones already in
                 *  the content.
                 */
                $include = substr ($alias, 0, 7) == 'default'
                    || $alias == 'header'
                    || $alias == 'footer'
                    || substr ($alias, 0, 7) == 'blocks_'
                    || substr ($alias, 0, 10) == 'shortcode_';

                // Plus the ones that apply to this particular post.
                if (in_array ($alias, $for_this_post, true)) {
                    $include = true;
                } // if ()

                if ($include === false || array_key_exists ('file', $file) === false) {
                    continue;
                } // if ()

                $path = $this->_urlToPath ($file ['file']);

                if ($path !== '') {
                    $wanted [] = array (
                        'path' => $path,
                        'url' => $file ['file']
                    );
                } // if ()
            } // foreach ()

            return $wanted;
        } // _getThemeStylesheets ()

        /**
         *  Describe one of the framework's own stylesheets.
         *
         *  @param string $relative The path below the framework's css folder.
         *
         *  @return array The entry, with 'path' and 'url'.
         */
        protected function _pluginStylesheet ($relative) {
            return array (
                'path' => FUSE_BASE_URI.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.str_replace ('/', DIRECTORY_SEPARATOR, $relative),
                'url' => FUSE_BASE_URL.'/assets/css/'.$relative
            );
        } // _pluginStylesheet ()

        /**
         *  Get the theme roots, parent first so the child can override.
         *
         *  @return array Each entry has 'path' and 'url'.
         */
        protected function _getThemeRoots () {
            $roots = array (
                array (
                    'path' => get_template_directory (),
                    'url' => get_template_directory_uri ()
                )
            );

            if (is_child_theme ()) {
                $roots [] = array (
                    'path' => get_stylesheet_directory (),
                    'url' => get_stylesheet_directory_uri ()
                );
            } // if ()

            return $roots;
        } // _getThemeRoots ()

        /**
         *  Turn a theme asset URL back into a path on disk.
         *
         *  @param string $url The stylesheet URL.
         *
         *  @return string The path, or an empty string if it is not ours.
         */
        protected function _urlToPath ($url) {
            foreach ($this->_getThemeRoots () as $root) {
                $base = trailingslashit ($root ['url']);

                if (strpos ($url, $base) === 0) {
                    $relative = substr ($url, strlen ($base));
                    $relative = str_replace ('/', DIRECTORY_SEPARATOR, $relative);

                    return trailingslashit ($root ['path']).$relative;
                } // if ()
            } // foreach ()

            return '';
        } // _urlToPath ()

        /**
         *  Drop anything that is not a readable file, and any repeats.
         *
         *  @param array $stylesheets The stylesheets to check.
         *
         *  @return array The ones that can actually be used.
         */
        protected function _filterReadable ($stylesheets) {
            $checked = array ();
            $seen = array ();

            if (is_array ($stylesheets) === false) {
                return $checked;
            } // if ()

            foreach ($stylesheets as $stylesheet) {
                if (is_array ($stylesheet) === false || array_key_exists ('path', $stylesheet) === false) {
                    continue;
                } // if ()

                if (in_array ($stylesheet ['path'], $seen, true)) {
                    continue;
                } // if ()

                if (is_file ($stylesheet ['path']) && is_readable ($stylesheet ['path'])) {
                    $seen [] = $stylesheet ['path'];
                    $checked [] = $stylesheet;
                } // if ()
            } // foreach ()

            return $checked;
        } // _filterReadable ()

        /**
         *  Read a stylesheet.
         *
         *  @param string $path The path to read.
         *
         *  @return string The contents, or an empty string.
         */
        protected function _read ($path) {
            $css = file_get_contents ($path);

            return is_string ($css) ? $css : '';
        } // _read ()

        /**
         *  Get a cache-busting version for a file.
         *
         *  @param string $path The path to check.
         *
         *  @return string The file's modified time.
         */
        protected function _getVersion ($path) {
            $time = filemtime ($path);

            return $time === false ? '' : strval ($time);
        } // _getVersion ()

        /**
         *  Get the aliases that apply to the post being edited.
         *
         *  These have to be built the same way Enqueue::getRequiredFiles ()
         *  builds them on the front end, or a stylesheet that applies to one
         *  page would show on the site but not while it is being written.
         *
         *  Aliases for an archive, a taxonomy, a tag or the 404 page are not
         *  here on purpose. The editor is showing one post, so those could
         *  never be the right styles for it.
         *
         *  @param \WP_Post $post The post being edited.
         *
         *  @return array The aliases that apply to it.
         */
        protected function _getPostAliases ($post) {
            if (is_object ($post) === false || isset ($post->ID) === false) {
                return array ();
            } // if ()

            $post_type = $post->post_type;

            $aliases = array (
                // Everything of this post type.
                'posttype_'.$post_type,
                // This post, by ID.
                $post_type.'_'.$post->ID
            );

            // This post, by its path.
            if (function_exists ('get_page_uri')) {
                $page_uri = get_page_uri ($post);

                if (is_string ($page_uri) && $page_uri !== '') {
                    $aliases [] = $post_type.'_'.str_replace (array ('\\', '/'), '_', $page_uri);
                } // if ()
            } // if ()

            // The front page, which the front end treats as a special case.
            if (intval (get_option ('page_on_front')) === intval ($post->ID)) {
                $aliases [] = 'page_home';
            } // if ()

            return $aliases;
        } // _getPostAliases ()

        /**
         *  Get the post being edited.
         *
         *  The block editor hands us a context object. The classic editor does
         *  not, so fall back to whatever the admin request is working on.
         *
         *  @param \WP_Block_Editor_Context $context The editor context.
         *
         *  @return \WP_Post|null The post, or null.
         */
        protected function _getEditedPost ($context = NULL) {
            if (is_object ($context) && isset ($context->post) && is_object ($context->post)) {
                return $context->post;
            } // if ()

            if (function_exists ('get_post') === false) {
                return NULL;
            } // if ()

            $post = get_post ();

            return is_object ($post) ? $post : NULL;
        } // _getEditedPost ()

    } // class EditorStyles
