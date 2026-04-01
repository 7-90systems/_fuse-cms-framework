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
     *  @filter fuse_sidebar_classes
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

        <section class="secondary sidebar widget-area sidebar-<?php echo $location; ?> <?php echo implode (' ', apply_filters ('fuse_sidebar_classes', array (), 1, $location, $layout)); ?>" role="complementary">

            <?php
                $sidebar = get_post_meta ($layout->getLayout (), 'fuse_parts_sidebar_'.$location.'_1', true);
            ?>
            <ul class="sidebar-container <?php echo apply_filters ('fuse_layout_sidebar_class', $col_1, $location, $layout); ?>">
                <?php dynamic_sidebar ($sidebar); ?>
            </ul>

        </section><!-- #secondary -->
    
    <?php endif; ?>
    
    <?php if ($layout->$col_2 == 1): ?>
    
        <section class="secondary sidebar widget-area sidebar-<?php echo $location; ?> <?php echo implode (' ', apply_filters ('fuse_sidebar_classes', array (), 2, $location, $layout)); ?>" role="complementary">
        
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
    if (function_exists ('fuse_get_feature_image_url') === false) {
        function fuse_get_feature_image_url ($post, $size = 'full', $fallback = true) {
            $image = NULL;
            $image_id = 0;
            
            if (has_post_thumbnail ($post)) {
                $image_id = get_post_thumbnail_id ($post);
            } // if ()
            
            return fuse_get_image_url ($image_id, $size, $fallback);
        } // fuse_get_feature_image_url ()
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
            $image = '';
            
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
     *  @return array|NULL Returns the image details or a NULL value if no image
     *  is available.
     */
    if (function_exists ('fuse_get_feature_image') === false) {
        function fuse_get_feature_image ($post, $size = 'full', $fallback = true) {
            $image = NULL;
            $image_id = 0;
            
            if (has_post_thumbnail ($post)) {
                $image_id = get_post_thumbnail_id ($post);
            } // if ()
            
            return fuse_get_image ($image_id, $size, $fallback);
        } // fuse_get_feature_image ()
    } // if ()
    
    /**
     *  Get the feature image for a term.
     *
     *  @param WP_Term|int $term The term or term ID.
     *  @param string $size The image size.
     *  @param bool $use_fallback Boolean 'true' to use a fallback image.
     */
    if (function_exists ('fuse_get_term_feature_image') === false) {
        function fuse_get_term_feature_image ($term, $size = 'full', $fallback = true) {
            if (is_numeric ($term) === false) {
                $temr = $term->term_id;
            } // if ()
            
            return fuse_get_image (get_term_meta ($term, 'fuse_term_featured_image', true), $size, $fallback);
        } // fuse_get_term_feature_image ()
    } // if ()
    
    /**
     *  Get an image URL given the image ID or return a fallback if none exists.
     *
     *  @param int $image_id the ID of the image.
     *  @param string $size The image size.
     *  @param bool $use_fallback Boolean 'true' to use a fallback image.
     *
     *  @return array|NULL Returns the image details or a NULL value if no image
     *  is available.
     */
    if (function_exists ('fuse_get_image') === false) {
        function fuse_get_image ($image_id, $size = 'full', $fallback = false) {
            $image = '';
            
            if ($image_id > 0) {
                $image = wp_get_attachment_image_src ($image_id, $size);
            } // if ()
            
            if (empty ($image) && $fallback !== false) {
                $fallback_id = intval (get_fuse_option ('fallback_image'));
                
                if ($fallback_id > 0) {
                    $image = wp_get_attachment_image_src ($fallback_id, $size);
                } // if ()
            } // if ()
            
            return $image;
        } // fuse_get_image ()
    } // if ()
    
    
    
    
    /**
     *  Output a list of FAQs from the given FAQ section.
     *
     *  @param int $section_id The FAQs section ID.
     */
    if (function_exists ('fuse_faqs_list') === false) {
        function fuse_faqs_list ($section_id) {
            $questions = get_posts (array (
                'numberposts' => -1,
                'post_type' => 'fuse_faq',
                'orderby' => 'menu_order title',
                'order' => 'ASC',
                'tax_query' => array (
                    'taxonomy' => 'fuse_faq-section',
                    'field' => 'term_id',
                    'terms' => $section_id
                )
            ));
            
            if (count ($questions) > 0) {
                include (FUSE_BASE_URI.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'faqs.php');
            } // if ()
        } // fuse_faqs_list ()
    } // if ()
        
        
        
        
    /**
     *  Output a responsive image.
     *
     *  @param array $args The arguemnts for the image
     *      image       Image ID
     *      size        WordPress image size
     *      alt         ALT text
     *      class       Additional CSS classes for the image figure tag
     *      caption     Optional caption for the image
     */
    if (function_exists ('fuse_responsive_image') === false) {
        function fuse_responsive_image ($args) {
			$fallback = get_fuse_option ('fallback_image', 0);

			$args = array_merge (array (
				'image' => $fallback,
				'size' => 'full',
				'alt' => '',
				'class' => '',
				'caption' => ''
			), $args);

			if (empty ($args ['image'])) {
				$args ['image'] = $fallback;
			} // if ()

			$image = wp_get_attachment_image_src ($args ['image'], $args ['size']);
			?>
				<figure class="fuse-responsive-image <?php esc_attr_e ($args ['class']); ?>">

					<img src="<?php echo esc_url ($image [0]); ?>" alt="<?php esc_attr_e ($args ['alt']); ?>" width="<?php echo $image [1]; ?>" height="<?php echo $image [2]; ?>" />

					<?php if (strlen ($args ['caption']) > 0): ?>

						<figcaption><?php echo $args ['caption']; ?></figcaption>

					<?php endif; ?>

				</figure>
			<?php
		} // fuse_responsive_image ()
    } // if ()