<?php
    /**
     *  @package fuse-cms
     *
     *  @version 1.0
     *  
     *  This file contains our formatting functions.
     *
     *  @filter fuse_phone_number_link_remove
     */
    
    /**
     *  Format a HTML tag attribute and return the completed attribute HTML.
     *
     *  @param mixed $value The value to be displayed in the attribute.
     *  @param string $name The name of this attribute.
     *  @param bool $render True to render the attribute or false to return the
     *  HTML value. Defaults to true.
     *
     *  @return string|NULL This returns the attribute code if requested.
     */
    function fuse_format_attribute ($value, $name, $render = true) {
        if (strlen ($name) > 0) {
            if (is_array ($value)) {
                $value = implode (' ', $value);
            } // if ()
            
            $attribute = $name.'="'.esc_attr ($value).'"';
        } // if ()
        else {
            $attribute = '';
        } // else
        
        if ($render === true) {
            echo $attribute;
        } // if ()
        else {
            return $attribute;
        } // else
    } // fuse_format_attribute ()
    
    /**
     *  Format a set of  HTML tag attributes and return the completed attribute
     *  HTML.
     *
     *  @param array $attributes The attribute values. This must be an
     *  associative array with the key as the attribute name and the value as
     *  the attribute value.
     *  @param bool $render True to render the attributes or false to return the
     *  HTML value. Defaults to true.
     *
     *  @return string|NULL This returns the attribute code if requested.
     */
    function fuse_format_attributes ($attributes, $hide_empty = false, $render = true) {
        $attribute_list = [];
        
        foreach ($attributes as $key => $value) {
            if (empty ($value) === false || $hide_empty === false) {
                $attribute_list [] = fuse_format_attribute ($value, $key, false);
            } // if ()
        } // foreach ()
        
        $attributes = ' '.implode (' ', $attribute_list);
        
        if ($render === true) {
            echo $attributes;
        } // if ()
        else {
            return $attributes;
        } // else
    } // fuse_format_attributes ()
    
    /**
     *  Fomat a phone number for a HTML link field.
     *
     *  @param string $phone The phone number to format.
     *
     *  @return string The formatted phone number.
     */
    function fuse_format_phone_number_link ($phone) {
        // Remove bad characters
        $remove = apply_filters ('fuse_phone_number_link_remove', [
            ' ',
            '(',
            ')',
            '-'
        ]);
        $phone = str_replace ($remove, '', $phone);
        
        // Remove alphabetical characters. eg: ext 43
        $phone = preg_replace ('/[a-zA-Z].*/', '', $phone);
        
        return $phone;
    } // fuse_format_phone_number_link ()