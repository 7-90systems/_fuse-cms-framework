<?php
    /**
     *  @package fusecms
     *
     *  This file contains our security functions.
     */
    
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
    
    /**
     *  Ensure that any file is not accessed directly.
     * 
     *  @param bool $include_message Tre to include a message. Defaults to false.
     */
    function fuse_block_direct_access () {
        if (!defined ('ABSPATH')) {
            die ();
        } // if ()
    } // fuse_block_direct_access ()
    
    
    
    
    /**
     *  Check whether the current request is allowed to write our meta values
     *  for a post.
     *
     *  Every save_post handler in Fuse must call this before it touches
     *  anything. It covers the three things a save_post callback always has to
     *  rule out:
     *
     *      - autosaves, revisions and bulk edits, which carry no meta box data
     *      - users who cannot edit this particular post
     *      - requests that did not come from the post editor (CSRF)
     *
     *  The CSRF check uses WordPress's own post edit nonce rather than a Fuse
     *  one. That nonce is present on the classic editor form and on the block
     *  editor's meta box submission, so it covers both without every meta box
     *  having to render a field of its own.
     *
     *  @param int $post_id The ID of the post being saved.
     *
     *  @return bool True when it is safe to save, false otherwise.
     */
    function fuse_can_save_post_meta ($post_id) {
        // Autosaves and revisions never carry our meta box values.
        if (defined ('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        } // if ()
        
        if (wp_is_post_revision ($post_id) || wp_is_post_autosave ($post_id)) {
            return false;
        } // if ()
        
        // The user must be able to edit this specific post.
        if (current_user_can ('edit_post', $post_id) === false) {
            return false;
        } // if ()
        
        // The request must have come from the post editor.
        if (array_key_exists ('_wpnonce', $_POST) === false) {
            return false;
        } // if ()
        
        return wp_verify_nonce ($_POST ['_wpnonce'], 'update-post_'.$post_id) !== false;
    } // fuse_can_save_post_meta ()
    
    
    
    
    /**
     *  Sanitise a value on its way into the database.
     *
     *  Meta values come out of $_POST, so they can be a string or an array of
     *  strings (and arrays of arrays, for the grouped form fields). This walks
     *  whatever it is given and runs every scalar through sanitize_text_field
     *  (), which is what almost every Fuse field actually wants.
     *
     *  Use fuse_sanitise_html () instead for anything that is meant to hold
     *  markup.
     *
     *  @param mixed $value The value to sanitise.
     *
     *  @return mixed The sanitised value, with the same shape as the input.
     */
    function fuse_sanitise_meta ($value) {
        if (is_array ($value)) {
            return array_map ('fuse_sanitise_meta', $value);
        } // if ()
        
        if (is_scalar ($value) === false) {
            return '';
        } // if ()
        
        return sanitize_text_field ($value);
    } // fuse_sanitise_meta ()
    
    /**
     *  Sanitise a value that is allowed to contain markup.
     *
     *  @param mixed $value The value to sanitise.
     *
     *  @return string The sanitised value.
     */
    function fuse_sanitise_html ($value) {
        if (is_scalar ($value) === false) {
            return '';
        } // if ()
        
        return wp_kses_post ($value);
    } // fuse_sanitise_html ()
