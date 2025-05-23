<?php
    /**
     *  To use your own template, copy this file to your theme at:
     *
     *  /templates/slider/slider.php
     */
    
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
?>
<div class="fuse-slider-slide fuse-slider-slide-<?php echo $slide->ID; ?>">

    <div class="slide-background" style="background-image: url('<?php echo esc_url (wp_get_attachment_image_url (get_post_thumbnail_id ($slide->ID), 'full')); ?>');"></div>

    <div class="wrap">
        
        <?php if (strlen ($slide->post_content) > 0): ?>
        
            <div class="slide-content fuse-container">
                <div class="wrap">
                    
                    <?php
                        echo apply_filters ('the_content', $slide->post_content);
                    ?>
                    
                </div>
            </div>
        
        <?php endif; ?>
        
    </div>
</div>