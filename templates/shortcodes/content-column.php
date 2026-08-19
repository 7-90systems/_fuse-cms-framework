<?php
    /**
     *  @package fusecms
     *
     *  Template file for content_column shortcode.
     */
    
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
    
    global $args;
    global $content;
    
    $classes = array (
        'content-column',
        'position-'.esc_attr ($args ['position']),
        'size-'.esc_attr ($args ['size'])
    );
    
    if (strlen ($args ['class']) > 0) {
        $classes [] = $args ['class'];
    } // if ()
    
    $id = '';
    
    if (strlen ($args ['id']) > 0) {
        $id = ' id="'.esc_attr ($args ['id']).'"';
    } // if ()
    
    $clear = false;
    
    if ($args ['clear'] == 'clear' || $args ['position'] == 'right' && $args ['clear'] != 'none') {
        $clear = true;
    } // if ()
?>
<div<?php echo $id; ?> class="<?php echo esc_attr (implode (' ', $classes)); ?>">
    <div class="wrap">
        
        <?php echo apply_filters ('the_content', $content); ?>
        
    </div>
</div>

<?php if ($clear === true): ?>
    <div class="clear"></div>
<?php endif; ?>