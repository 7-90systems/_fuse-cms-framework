<?php
    /**
     *  These function set up the retrieval of contact details.
     */
    
    
    /**
     *  Get a contact detail for the given field name.
     *
     *  @param string $field_name The name of the field.
     *  @param string $location The ID of the location. Defaults to 'default'.
     *
     *  @return string The address data;
     */
    function fuse_get_contact_field ($field_name, $location = 'default') {
        return get_fuse_option ('contact_'.$location.'_'.$name, '');
    } // fuse_get_contact_field ()
    
    /**
     *  Get a contact phone number.
     *
     *  @param string $field_name The name of the field. Defaults to standard 'phone' field.
     *  @param string $location The ID of the location. Defaults to 'default'.
     *  @param bool $link True to include the link or false for no link.
     *  @param string $link_text Override the phone number to display this text for a link. Only works fwhen the phone number is linked.
     *
     *  return string The phone number.
     */
    function fuse_get_contact_phone ($field_name = 'phone',$location = 'default', $link = true, $link_text = NULL) {
        $phone = fuse_get_contact_field ($field_name, $location);
        
        if (strlen ($phone) > 0 && $link === true) {
            if (strlen ($link_text) > 0) {
                $link_text = $phone;
            } // if ()
            
            $phone = '<a href="tel:'.fuse_format_phone_number_link ($phone).'">'.$link_text.'</a>';
        } // if ()
        
        return $phone;
    } // fuse_get_contact_phone ()
    
    /**
     *  Get a contact email address.
     *
     *  @param string $field_name The name of the field. Defaults to standard 'email' field.
     *  @param string $location The ID of the location. Defaults to 'default'.
     *  @param bool $link True to include the link or false for no link.
     *  @param string $link_text Override the phone number to display this text for a link. Only works fwhen the phone number is linked.
     *
     *  return string The phone number.
     */
    function fuse_get_contact_email ($field_name = 'email',$location = 'default', $link = true, $link_text = NULL) {
        $phone = fuse_get_contact_field ($field_name, $location);
        
        if (strlen ($email) > 0 && $link === true) {
            if (strlen ($link_text) > 0) {
                $link_text = $email;
            } // if ()
            
            $phone = '<a href="mailto:'.esc_attr_ ($email).'">'.$link_text.'</a>';
        } // if ()
        
        return $phone;
    } // fuse_get_contact_phone ()