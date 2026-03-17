<?php
    /**
     *  @package fusecms
     *
     *  This is the base class for our shortcodes.
     *
     *  Template files must be located in your themes (or child themes)
     *  /templates/shortcodes/ folder. If not found this will fall back to the
     *  same folder in the Fuse plugin.
     *
     *  @filter fuse_shortcode_template_folders Set additional template folder
     *  locations for shortcodes.
     */
    
    namespace Fuse;
    
    
    class Shortcode {
        
        /**
         *  @var string The shortcode.
         */
        private $_shortcode;
        
        /**
         *  @var string the template file name.
         */
        protected $_template;
        
        /**
         *  @var array The default arguments for this shortcode.
         */
        protected $_defaults;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $shortcode The shortcode string.
         *  @param string $template The template file (without the .php).
         *  @param array $defaults The defaults for this shortcode.
         */
        public function __construct ($shortcode, $template, $defaults = array ()) {
            $this->_shortcode = $shortcode;
            $this->_template = $template;
            $this->_defaults = $defaults;
        } // __construct ()
        
        
        
        
        /**
         *  Render the shortcodes content.
         *
         *  @param array $shortcode_args The arguments for this shortcode.
         *  @param string $shortcode_content The content passed to this
         *  shortcode.
         *
         *  return string The shortcodes HTML content.
         */
        public function render ($shortcode_args = array (), $shortcode_content = '') {
            global $args;
            global $content;
            
            $args = shortcode_atts ($this->_defaults, $shortcode_args);
            $content = $shortcode_content;
            
            $html = '';
            
            $template = self::getTemplateLocation ($this->_template);
                
            if (empty ($template) === false) {
                ob_start ();
                load_template ($template, false);
                $html = ob_get_contents ();
                ob_end_clean ();
            } // if ()
            
            return trim ($html);
        } // render ()
        
        
        
        
        /**
         *  Return the shortcode definition.
         *
         *  @return string the shortcode.
         */
        final public function getShortcode () {
            return $this->_shortcode;
        } // getShortcode ()
        
        
        
        
        /**
         *  Locate the template file for the given template.
         *
         *  @param string $template The template file name, without '.php'.
         *
         *  @return string|NULL Returns the file location or NULL if the file
         *  does not exist in the available locations.
         */
        static public function getTemplateLocation ($template) {
            $location = NULL;
            
            $template_locations = array ();
            
            if (is_child_theme ()) {
                $template_locations [] = get_stylesheet_directory ().DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'shortcodes'.DIRECTORY_SEPARATOR.$template.'.php';
            } // if ()
            
            $template_locations [] = get_template_directory ().DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'shortcodes'.DIRECTORY_SEPARATOR.$template.'.php';
            $template_locations [] = FUSE_BASE_URI.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'shortcodes'.DIRECTORY_SEPARATOR.$template.'.php';
                
            $template_found = false;
                
            foreach (apply_filters ('fuse_shortcode_template_locations', $template_locations, $template) as $loc) {
                if ($template_found === false && file_exists ($loc)) {
                    $location = $loc;
                    $template_found = true;
                } // if ()
            } // foreach ()
            
            return $location;
        } // getTemplateLocation ()
        
    } // class Shortcode
