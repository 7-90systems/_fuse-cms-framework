<?php
    /**
     *  @package fusecms
     *
     *  This class is used to install the various settings and posts for the
     *  Fuse CMS Frameowork.
     */
    
    namespace Fuse;
    
    
    class Install {

        /**
         *  @var string The option recording that the settings have been
         *  repaired, so it is done once rather than on every admin page.
         */
        const OPTION_UNSLASHED = 'fuse_settings_unslashed';




        /**
         *  Object constructor.
         */
        public function __construct () {
            // Set up our initial page layout.
            $this->_setupLayout ();
        } // __construct ()




        /**
         *  Take the stray backslashes out of settings saved before the fix.
         *
         *  Form::save () used to write $_POST values to the options table with
         *  the slashes WordPress adds still on them, so any setting containing
         *  a quote is sitting in the database with backslashes that were never
         *  typed. The save path is fixed, but a value already stored stays
         *  wrong until somebody happens to save that form again -- and a
         *  Content-Security-Policy written from one is invalid rather than
         *  merely untidy, which is not something to leave for later.
         *
         *  Runs once, flagged by an option.
         */
        public static function repairSlashedOptions () {
            if (get_option (self::OPTION_UNSLASHED, '') === 'yes') {
                return;
            } // if ()

            global $wpdb;

            /**
             *  Only our own settings, and the shortlist is small enough
             *  that the backslash test is better done in PHP than as a
             *  LIKE with its own escaping to get wrong.
             */
            $rows = $wpdb->get_results ($wpdb->prepare (
                "SELECT option_name, option_value
                FROM $wpdb->options
                WHERE option_name LIKE %s",
                $wpdb->esc_like ('fuse_setting_').'%'
            ));

            if (is_array ($rows) === true) {
                foreach ($rows as $row) {
                    if (self::looksSlashed ($row->option_value) === false) {
                        continue;
                    } // if ()

                    update_option ($row->option_name, stripslashes ($row->option_value));
                } // foreach ()
            } // if ()

            update_option (self::OPTION_UNSLASHED, 'yes');
        } // repairSlashedOptions ()

        /**
         *  Does this value look like addslashes () output rather than typed?
         *
         *  addslashes () only ever puts a backslash in front of a quote,
         *  another backslash or a NUL. A backslash in front of anything else --
         *  a Windows path, a regular expression -- was meant, and stripping it
         *  would break a setting rather than repair one. So a value is only
         *  touched when every backslash in it is one addslashes () could have
         *  put there.
         *
         *  @param string $value The stored value.
         *
         *  @return bool True when it is safe to strip.
         */
        protected static function looksSlashed ($value) {
            if (is_string ($value) === false || strpos ($value, '\\') === false) {
                return false;
            } // if ()

            // Every backslash in it escapes something addslashes () escapes.
            return preg_match ('/^(?:[^\\\\]|\\\\[\'"\\\\])*$/', $value) === 1;
        } // looksSlashed ()
        
        
        
        
        /**
         *  Set up our initial page layout.
         *
         *  We only set up an initial layout if no layouts exist.
         */
        protected function _setupLayout () {
            $layouts = get_posts (array (
                'numberposts' => 1,
                'post_type' => 'fuse_layouts'
            ));
            
            if (count ($layouts) == 0) {
                $layout_id = wp_insert_post (array (
                    'post_title' => __ ('Global Default Layout', 'fuse'),
                    'post_type' => 'fuse_layouts',
                    'post_status' => 'publish'
                ));
                
                if ($layout_id > 0) {
                    // Created successfully
                    add_post_meta ($layout_id, 'fuse_layout_parts', array (
                        'header' => true,
                        'left_1' => false,
                        'left_2' => false,
                        'right_1' => true,
                        'right_2' => false,
                        'footer' => true
                    ));
                    
                    add_post_meta ($layout_id, 'fuse_parts_sidebar_left_1', 'default');
                    add_post_meta ($layout_id, 'fuse_parts_sidebar_left_2', 'default');
                    add_post_meta ($layout_id, 'fuse_parts_sidebar_right_1', 'default');
                    add_post_meta ($layout_id, 'fuse_parts_sidebar_right_2', 'default');
                    
                    add_option ('fuse_layout_defaults_global', $layout_id);
                } // if ()
            } // if ()
        } // _setupLayout ()
        
    } // class Install