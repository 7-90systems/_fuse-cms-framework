<?php
    /**
     *  @package fusecms
     *
     *  Template file for contact_field shortcode.
     */
    
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
    
    global $args;
    
    if ($args ['url'] === true || $args ['url'] == 'true') {
        $url = fuse_get_contact_field ($args ['field'], $args ['location']);
        
        if (strlen ($url) > 0) {
            $link_text = $args ['link_text'];
            
            if (strlen ($link_text) == 0) {
                $link_text = $url;
            } // if ()
            
            echo  '<a href="'.esc_url ($url).'">'.$link_text.'</a>';
        } // if ()
    } // if ()
    elseif ($args ['phone'] === true || $args ['phone'] == 'true') {
        echo  fuse_get_contact_phone ($args ['field'], $args ['location'], true, $args ['link_text']);
    } // elseif ()
    elseif ($args ['email'] === true || $args ['email'] == 'true') {
        echo fuse_get_contact_email ($args ['field'], $args ['location'], true, $args ['link_text']);
    } // elseif ()
    else {
        echo fuse_get_contact_field ($args ['field'], $args ['location']);
    } // else