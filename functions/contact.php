<?php
    /**
     *  Set up the functions for our contact detail values.
     *
     *  @filter fuse_social_media_icon_locations
     */
    
    
    
    
    /**
     *  Get the value set for a contact detail field.
     *
     *  @param string $name the name of the field.
     *  @param string $location The location for the value. Defaults to 'default'.
     *
     *  return string Returns the value or an empty string if no value exists.
     */
    if (function_exists ('fuse_get_contact_field') === false) {
        function fuse_get_contact_field ($name, $location = 'default') {
            return get_fuse_option ('contact_'.$name.'_'.$location, '');
        } // fuse_get_contact_field ()
    } // if ()
    
    /**
     *  Get the value set for a phone number field.
     *
     *  @param string $name the name of the field. Defaults to 'phone'.
     *  @param string $location The location for the value. Defaults to 'default'.
     *  @param bool $link True to set a link or false to return only the phone number value.
     *  @param string $link_text Text to set for a link instead of the phone number.
     *
     *  return string Returns the value or an empty string if no value exists.
     */
    if (function_exists ('fuse_get_contact_phone') === false) {
        function fuse_get_contact_phone ($name = 'phone', $location = 'default', $link = true, $link_text = '') {
            $phone = fuse_get_contact_field ($name, $location);
            
            if (strlen ($phone) > 0 && $link === true) {
                if (strlen ($link_text) == 0) {
                    $link_text = $phone;
                } // if ()
                
                $phone_link = str_replace (array (
                    ' ',
                    '(',
                    ')'
                ), '', $phone);
                
                $phone = '<a href="tel:'.$phone_link.'">'.$link_text.'</a>';
            } // if ()
            
            return $phone;
        } // fuse_get_contact_phone ()
    } // if ()
    
    /**
     *  Get the value set for a URL field.
     *
     *  @param string $name the name of the field.
     *  @param string $location The location for the value. Defaults to 'default'.
     *  @param bool $link True to set a link or false to return only the link URL.
     *  @param string $link_text Text to set for a link instead of the link URL.
     *  @param bool $new_window True to open in a new window.
     *
     *  return string Returns the value or an empty string if no value exists.
     */
    if (function_exists ('fuse_get_contact_url') === false) {
        function fuse_get_contact_url ($name, $location = 'default', $link = true, $link_text = '', $new_window = false) {
            $url = fuse_get_contact_field ($name, $location);
            
            if (strlen ($url) > 0 && $link === true) {
                if (strlen ($link_text) == 0) {
                    $link_text = $url;
                } // if ()
                
                $target = '';
                
                if ($new_window === true) {
                    $target = ' target="_blank"';
                } // if ()
                
                $url = '<a href="'.esc_url ($url).'"'.$target.'>'.$link_text.'</a>';
            } // if ()
            
            return $url;
        } // fuse_get_contact_url ()
    } // if ()
    
    
    
    
    /**
     *  Get the social meda links/icons for the site.
     *
     *  @param bool $icons True to shwo icons. False to show text only.
     *
     *  @return string The HTML code ofr the icons list.
     */
    if (function_exists ('fust_get_social_links') === false) {
        function fust_get_social_links ($icons = true) {
            $fields = apply_filters ('fuse_contact_social_media_links', array (
                new \Fuse\Forms\Component\Field\Url ('contact_social_facebook', __ ('Facebook', 'fuse')),
                new \Fuse\Forms\Component\Field\Url ('contact_social_instagram', __ ('Instagram', 'fuse')),
                new \Fuse\Forms\Component\Field\Url ('contact_social_tiktok', __ ('TikTok', 'fuse')),
                new \Fuse\Forms\Component\Field\Url ('contact_social_youtube', __ ('YouTube', 'fuse')),
                new \Fuse\Forms\Component\Field\Url ('contact_social_linkedin', __ ('Linkedin', 'fuse'))
            ));
            
            ob_start ();
            ?>
                <ul class="fuse-social-media-links">
                    
                    <?php foreach ($fields as $field): ?>
                        <?php
                            $name = str_replace ('contact_social_', '', $field->name);
                            $link = get_fuse_option ($field->name);
                        ?>
                        
                        <?php if (strlen ($link) > 0): ?>
                        
                            <li>
                                <a href="<?php echo esc_url ($link); ?>" target="_blank">
                                    <?php
                                        if ($icons === true) {
                                            echo fuse_social_media_icon ($name);
                                        } // if ()
                                        else {
                                            echo $field->label;
                                        } // else
                                    ?>
                                </a>
                            </li>
                        
                        <?php endif; ?>
                        
                    <?php endforeach; ?>
                    
                </ul>
            <?php
            $html = ob_get_contents ();
            ob_end_clean ();
            
            return $html;
        } // fust_get_social_links ()
    } // if ()
    
    /**
     *  Get the social media icon file. This must be an SVG file that will be displayed inline.
     *  We sugget leaving stroke and fill colours to your theme's CSS syling where ever possible.
     *
     *  #param string $platform The name of the platform, eg 'facebook'. This will be sued to find the icons file URI.
     *
     *  @return string The SVG file contents.
     */
    if (function_exists ('fuse_social_media_icon') === false) {
        function fuse_social_media_icon ($platform) {
            $fuse_social_media_icon_locations = apply_filters ('fuse_social_media_icon_locations', array (
                FUSE_BASE_URI.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'social'
            ));
            
            $icon = '';
            
            foreach ($fuse_social_media_icon_locations as $dir) {
                if (strlen ($icon) == 0) {
                    $icon_file = untrailingslashit ($dir).DIRECTORY_SEPARATOR.$platform.'.svg';
                    
                    if (file_exists ($icon_file)) {
                        $icon = file_get_contents ($icon_file);
                    } // if ()
                } // if ()
            } // foreach ()
            
            return $icon;
        } // fuse_social_media_icon ()
    } // if ()