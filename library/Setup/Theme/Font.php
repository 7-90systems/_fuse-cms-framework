<?php
    /**
     *  This class is used to load our sites web font files.
     *
     *  Web fonts are stored in your themes under the /assets/fonts/ folder with a folder for
     *  each web font. There are filters available to set the folder locations as well as
     *  which fonts are to be set as available for your site.
     *
     *  Each font folder must have it's own font.css stylesheet with the CSS definitions for
     *  that font.
     *
     *  @filter fuse_font_folder_locations
     *  @filter fuse_available_fonts
     */
    
    namespace Fuse\Setup\Theme;
    
    use Fuse\Traits\Singleton;
    use Fuse\Forms\Component;
    
    
    class Font {
        
        use Singleton;
        
        
        
        
        /**
         *  Initailise our object.
         */
        protected function _init () {
            // add our administration settings options
            if (is_admin ()) {
                add_filter ('fuse_settings_form_theme_features_fields', array ($this, 'settingsFields'));
            } // if ()
            
            add_action ('wp_enqueue_scripts', array ($this, 'enqueueFonts'), 1);
        } // _init ()
        
        
        
        
        /**
         *  Add in our settings form fields.
         *
         *  @param array $fields The settings forms fields array.
         *
         *  @return array The completed fields array.
         */
        public function settingsFields ($fields) {
            $fonts = $this->getAvailableFonts ();
            
            $available_fonts = array ();
            
            foreach ($fonts as $key => $dir) {
                $available_fonts [$key] = $this->getFontName ($key, $dir);
            } // foreach ()

            $fields [] = new Component\Field\Checkbox ('load_fonts', __ ('Web fonts to load', 'fuse'), $available_fonts, get_fuse_option ('load_fonts', ''));
            
            return $fields;
        } // settingsFields ()
        
        /**
         *  Enqueue our fonts
         */
        public function enqueueFonts () {
            $active_fonts = get_fuse_option ('load_fonts', '');
            
            if (is_array ($active_fonts) == false) {
                $active_fonts = explode (',', $active_fonts);
                $active_fonts = array_filter ($active_fonts);
            } // if ()
            
            foreach ($this->getAvailableFonts () as $key => $dir) {
                if (in_array ($key, $active_fonts)) {
                    $dir = substr ($dir, strlen (ABSPATH));
                    $dir = str_replace ('\\', '/', $dir);
                    wp_enqueue_style ('fuse_font_'.$key, home_url ('/'.$dir.'/font.css'));
                } // if ()
            } // foreach ()
        } // enqueueFonts ()
        
        
        
        
        /**
         *  Get the list of available fonts.
         *
         *  @return array Returns an array of font folders.
         */
        public function getAvailableFonts () {
            $fonts = array ();
            
            $directories = array (
                get_template_directory ().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts'
            );
            
            if (is_child_theme ()) {
                $directories [] = get_stylesheet_directory ().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'fonts';
            } // if ()
            
            $font_directories = apply_filters ('fuse_font_folder_locations', $directories);

            foreach ($font_directories as $dir) {
                $dirs = glob ($dir.DIRECTORY_SEPARATOR.'*');

                foreach ($dirs as $dir) {
                    if (is_dir ($dir) && file_exists ($dir.DIRECTORY_SEPARATOR.'font.css')) {
                        $fonts [basename ($dir)] = $dir;
                    } // if ()
                } // foreach ()
            } // foreach ()
            
            return apply_filters ('fuse_available_fonts', $fonts);
        } // getAvailableFonts ()
        
        /**
         *  Get the name of the font. The name is part of the header of the font.css stylesheet.
         */
        public function getFontName ($key, $dir) {
            $name = $key;
            
            $font_data = get_file_data ($dir.DIRECTORY_SEPARATOR.'font.css', array (
                'font' => 'Font'
            ));
            
            if (array_key_exists ('font', $font_data)) {
                $name = $font_data ['font'];
            } // if ()
            
            return $name;
        } // getFontName ()
        
    } // class Font