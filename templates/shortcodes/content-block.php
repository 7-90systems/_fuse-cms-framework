<?php
    /**
     *  @package fusecms
     *
     *  Template file for content_block shortcode.
     */
    
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
    
    global $args;
    global $content;
    
    $classes = array (
        'content-block'
    );
    
    if (strlen ($args ['class']) > 0) {
        $classes [] = $args ['class'];
    } // if ()
    
    $id = '';
    
    if (strlen ($args ['id']) > 0) {
        $id = ' id="'.esc_attr ($args ['id']).'"';
    } // if ()
?>
<div<?php echo $id; ?> class="<?php echo esc_attr (implode (' ', $classes)); ?>">
    <div class="wrap">
        
        <?php echo apply_filters ('the_content', $content); ?>
        
    </div>
</div>