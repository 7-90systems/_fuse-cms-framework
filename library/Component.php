<?php
    /**
     *  This is our base component class. A component is a block of HTML code that can also 
     *  connect to any PHP functions as needed to manage the display. A component can be 
     *  something simple, like a button or text string, or it can be a complicated HTML-heavy
     *  full layout.
     * 
     *  To ensure that all components have the same behaviour, when adding functions to set
     *  values, you should always return the component instance so that calls can be daisy
     *  chained together.
     * 
     *  eg:  $grid_component->columns (3)->dataSource ('post')
     * 
     *  @filter fuse_component_template_uri
     */

    namespace Fuse;

    use WP_Error;


    class Component {

        /**
         *  @var array This array contains any CSS classes that are needed for this component.
         */
        protected $_css_classes;




        /**
         *  Object constructor.
         */
        public function __construct () {
            $this->_css_classes = [];
        } // __construct ()




        /**
         *  Add a CSS class or array of CSS classes.
         * 
         *  @param string|array $class The CSS class/classes to add.
         * 
         *  @return self This component object.
         */
        public function css ($class) {
            if (empty ($class) === false) {
                if (is_array ($class)) {
                    $this->_css_classes = array_merge ($this->_css_classes, $class);
                } // if ()
                else {
                    $this->_css_classes [] = $class;
                } // else
            } // if ()

            return $this;
        } // css ()




        /**
         *  Get the renders the HTML for this component.
         */
        final public function render () {
            $template_file = false;

            $class = explode ('\\', get_class ($this));

            if (count ($class) > 2) {
                array_pop ($class);
                array_pop ($class);

                $template_file = apply_filters ('fuse_component_template_uri', FUSE_BASE_URI.DIRECTORY_SEPARATOR.'templates/components/'.implode (DIRECTORY_SEPARATOR, $class).'.php', $this);
            } // if ()
            
            if (file_exists ($template_file)) {
                // Wrap every component in it's wrapper class
                $cls = trim (str_replace ('\\', '-', strtolower (get_class ($this))));

                if (count ($this->_css_classes) > 0) {
                    $cls.= ' '.implode (' ', $this->_css_classes);
                } // if ()

                echo '<div class="fuse-component '.$cls.'">'.PHP_EOL;

                // We use include so that the template file has access to all of the components functions and data.
                include ($template_file);

                echo '<div>'.PHP_EOL;
            } // if ()
            else {
                return new WP_Error ('fct01', __ ('No template file available for component '.get_class ($this), 'fuse'));
            } // else
        } // render ()

    } // class Component