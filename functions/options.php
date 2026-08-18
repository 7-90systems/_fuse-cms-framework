<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  These are the functions that we use to save the Fuse options.
     */
    
    
    /**
     *  Get an option value.
     *
     *  @param string $name The name of the option to get.
     *  @param mixed $default The default valeu if the requested value does not
     *  exist.
     *
     *  @return mixed Returns the value.
     */
    function get_fuse_option ($name, $default = '') {
        return get_option ('fuse_setting_'.$name, $default);
    } // get_fuse_option ()
    
    /**
     *  Save an option value.
     *
     *  @param string $name The name of the option to save.
     *  @param mixed $value The value to save.
     */
    function update_fuse_option ($name, $value) {
        update_option ('fuse_setting_'.$name, $value);
    } // update_fuse_option ()
    
    
    
    
    /**
     *  Get a post meta value.
     *
     *  @param int $post_id The post ID.
     *  @param string $meta_name The name of the meta value to retrieve.
     *  @param bool $single True to return a single value.
     */
    function get_fuse_post_meta ($post_id, $meta_name, $single = true) {
        return get_post_meta ($post_id, 'fuse_form_'.$meta_name, $single);
    } // _get_fuse_post_meta ()
    
    /**
     *  Save a post meta value.
     *
     *  @param int $post_id The post ID.
     *  @param string $meta_name The meta name to update.
     *  @param mixed $value The value to set.
     *  @param mixed $prev_value The previous value.
     */
    function update_fuse_post_meta ($post_id, $meta_name, $value, $prev_value = '') {
        update_post_meta ($post_id, 'fuse_form_'.$meta_name, $value, $prev_value);
    } // update_fuse_post_meta ()