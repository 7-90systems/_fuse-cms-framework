<?php
    /**
     *  Set up our slider post type.
     */
    
    namespace Fuse\PostType;
    
    use Fuse\PostType;
    
    
    class Slider extends PostType {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('fuse_slider', __ ('Slider', 'fuse'), __ ('Sliders', 'fuse'), array (
                'public' => false,
                'publicly_queryable' => false,
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-cover-image',
                'supports' => array (
                    'title'
                )
            ));
        } // __construct ()
        
    } // class Slider