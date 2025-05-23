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
<div class="fuse-slider-slide fuse-slider-slide-<?php echo $slide->ID; ?> fuse-container">
    <div class="wrap">
        
        Slide '<?php echo $slide->post_title; ?>'
        
    </div>
</div>