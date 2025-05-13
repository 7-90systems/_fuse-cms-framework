<?php
    /**
     *  @package fusecms
     *
     *  This is the base model class. This interacts with a post object and allows easy
     *  data retrival.
     */
    
    namespace Fuse;
    
    
    class Model {
        
        /**
         *  @var WP_Post The post object associated with this model.
         */
        protected $_post;
        
        
        
        
        /**
         *  Object constructor.
         *
         *     @param int|WP_Post $post The post ID or WP_Post object.
         */
        public function __construct ($post) {
            if (is_numeric ($post)) {
                $post = get_post ($post);
            } // if ()
            
            $this->_post = $post;
        } // if ()
        
        
        
        
        /**
         *  Get a value from this models post object.
         *
         *  This can return either a value directly set on the post object (eg: ID, post_type, etc) and
         *  if that does not exist it will search for a meta value set for the post object. If no value
         *  is found FALSE will be returned. The return values may differ if meta values are requested.
         *  See @link https://developer.wordpress.org/reference/functions/get_post_meta/ for more
         *  information.
         *
         *  @param string $name The name of the value to be returned.
         *  @param bool $single_meta_value Used when retrieving meta values for this model.
         *
         *  @return mixed The requested value or NULL if no value is found.
         */
        public function get ($name, $single_meta_value = true) {
            $value = false;
            
            if (property_exists ($this->_post, $name)) {
                $value = $this->_post->$name;
            } // if ()
            else {
                $value = get_post_meta ($this->_post->ID, $name, $single_meta_value);
            } // else
            
            return $value;
        } // get ()
        
        
        
        
        
        /**
         *  Get a value. This can be either a value of the main post object or a meta value.
         *
         *  @param string $name The name of the value to return.
         *
         *  @return mixed The value.
         */
        public function __get ($name) {
            $value = NULL;
            
            if (property_exists ($this->_post, $name)) {
                $value = $this->_post->$name;
            } // if ()
            else {
                $value = get_post_meta ($this->_post->ID, $name, true);
            } // else
            
            return $value;
        } // __get ()
        
    } // class Model