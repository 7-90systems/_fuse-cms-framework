<?php
    /**
     *  @package fusecms
     *
     *  This file contains our template functions.
     *
     *  @filter fuse_fallback_image_url The fallback image URL.
     *  @filter fuse_header_template
     *  @filter fuse_footer_template
     *  @filter fuse_layout_sidebar_class
     */
    
    /**
     *  Output the paging navigation links
     */
    if (function_exists ('fuse_paging_nav') === false) {
        function fuse_paging_nav ($args = array ()) {
            $args = array_merge (array (
                'prev_text' => __ ('Previous', 'fuse'),
				'next_text' => __ ('Next', 'fuse'),
				'before_page_number' => __ ('Page ', 'fuse')
            ), $args);
            
            the_posts_pagination ($args);
        } // fuse_paging_nav ()
    } // if ()
    
    /**
     *  Output the comments paging navigation links.
     */
    if (function_exists ('fuse_comments_paging_nav') === false) {
        function fuse_comments_paging_nav ($args = array ()) {
            $args = array_merge (array (
                'prev_text' => __ ('Previous', 'fuse'),
                'next_text' => __ ('Next', 'fuse')
            ), $args);
            
            the_comments_pagination ($args);
        } // fuse_comments_paging_nav ()
    } // if ()
    
    
    
    
    /**
     *  Output the header area.
     *
     *  @param string $location The location of the header file.
     */
    if (function_exists ('fuse_get_header') === false) {
        function fuse_get_header ($name = '') {
            $fuse = \Fuse\Fuse::getInstance ();

            $layout = $fuse->layout;
            
            if (is_null ($layout) || $layout->header == 1) {
                get_header (apply_filters ('fuse_header_template', $name));
            } // if ()
            else {
                // Blank header
?>
<!DOCTYPE html>
<html >
<head>
    <?php
         wp_head ();
    ?>
</head>
<body>
<?php
            } // else
        } // fuse_get_header ()
    } // if ()
    
    /**
     *  Output the footer area.
     *
     *  @param string $location The location of the footer file.
     */
    if (function_exists ('fuse_get_footer') === false) {
        function fuse_get_footer ($name = '') {
            $fuse = \Fuse\Fuse::getInstance ();
            $layout = $fuse->layout;
            
            if (is_null ($layout) || $layout->footer == 1) {
                get_footer (apply_filters ('fuse_footer_template', $name));
            } // if ()
            else {
                // Blank footer
?>
<?php
    wp_footer ();
?>
</body>
<?php
            } // else
        } // fuse_get_footer ()
    } // if ()
    
    /**
     *  Output the sidebar area.
     *
     *  @param string $location The location of the sidebar, either 'left' or
     *  'right'.
     */
    if (function_exists ('fuse_get_sidebar') === false) {
        function fuse_get_sidebar ($location) {
            $fuse = \Fuse\Fuse::getInstance ();
            $layout = $fuse->layout;
            
            if (is_null ($layout) === false) {
                $col_1 = $location.'_sidebar_1';
                $col_2 = $location.'_sidebar_2';

                if ($layout->$col_1 == true || $layout->$col_2 == true) {
?>
    <?php if ($layout->$col_1 == 1): ?>

        <section class="secondary sidebar widget-area sidebar-<?php echo $location; ?>" role="complementary">

            <?php
                $sidebar = get_post_meta ($layout->getLayout (), 'fuse_parts_sidebar_'.$location.'_1', true);
            ?>
            <ul class="sidebar-container <?php echo apply_filters ('fuse_layout_sidebar_class', $col_1, $location, $layout); ?>">
                <?php dynamic_sidebar ($sidebar); ?>
            </ul>

        </section><!-- #secondary -->
    
    <?php endif; ?>
    
    <?php if ($layout->$col_2 == 1): ?>
    
        <section class="secondary sidebar widget-area sidebar-<?php echo $location; ?>" role="complementary">
        
            <?php
                $sidebar = get_post_meta ($layout->getLayout (), 'fuse_parts_sidebar_'.$location.'_2', true);
            ?>
            <ul class="sidebar-container <?php echo apply_filters ('fuse_layout_sidebar_class', $col_2, $location, $layout); ?>">
                <?php dynamic_sidebar ($sidebar); ?>
            </ul>
        
        </section><!-- #secondary -->

    <?php endif; ?>
<?php
                } // if ()
            } // if ()
        } // fuse_get_sidebar ()
    } // if ()
    
    
    
    
    /**
     *  Get the featured image of the given post. If no image is found a
     *  fallback image can be given instead.
     *
     *  Fallback images must be located in your themes
     *  '/assets/images/fallback/' folder with the same name as the image size.
     *  As an example, if the size of 'bigsquare' the fallback will be called
     *  'bigsquare.jpg'.
     *
     *  @param WP_Post|int $post The post object or ID.
     *  @param string $size The image size.
     *  @param bool $use_fallback Boolean 'true' to use a fallback image.
     *
     *  @return string|NULL Returns the image URL or a NULL value if no image
     *  is available.
     */
    if (function_exists ('fuse_get_feature_image') === false) {
        function fuse_get_feature_image ($post, $size = 'full', $fallback = true) {
            $image = NULL;
            $image_id = 0;
            
            if (has_post_thumbnail ($post)) {
                $image_id = get_post_thumbnail_id ($post);
            } // if ()
            
            return fuse_get_image_url ($image_id, $size, $fallback);
        } // fuse_get_feature_image ()
    } // if ()
    
    /**
     *  Get an image URL given the image ID or return a fallback if none exists.
     *
     *  @param int $image_id the ID of the image.
     *  @param string $size The image size.
     *  @param bool $use_fallback Boolean 'true' to use a fallback image.
     *
     *  @return string|NULL Returns the image URL or a NULL value if no image
     *  is available.
     */
    if (function_exists ('fuse_get_image_url') === false) {
        function fuse_get_image_url ($image_id, $size = 'full', $fallback = false) {
            $image = NULL;
            
            if ($image_id > 0) {
                $image = wp_get_attachment_image_url ($image_id, $size);
            } // if ()
            
            if (empty ($image) && $fallback !== false) {
                $fallback_image = apply_filters ('fuse_fallback_image_url', 'assets/images/fallback/'.esc_attr ($size).'.jpg', $size);
                    
                if (is_child_theme () && file_exists (trailingslashit (get_stylesheet_directory ()).$fallback_image)) {
                    $image = trailingslashit (get_stylesheet_directory_uri ()).$fallback_image;
                } // if ()
                
                if (empty ($image) && file_exists (trailingslashit (get_template_directory ()).$fallback_image)) {
                     $image = trailingslashit (get_template_directory_uri ()).$fallback_image;
                } // if ()
            } // if ()
            
            return $image;
        } // fuse_get_image_url ()
    } // if ()