<?php
    /**
     *  To use your own template, copy this file to your theme at:
     *
     *  /templates/slider/slider.php
     */
    
    if (!defined ('ABSPATH')) {
        die ();
    } // if ()
    
    $slides = $this->geSlides ();
?>
<?php if (count ($slides) > 0): ?>

    <?php
        $id = esc_attr (uniqid ('fuse_slider_'));
        $slide_template = $this->_getTemplateUri ('slide.php');
        
        wp_enqueue_script ('slick');
        wp_enqueue_style ('slick');
    ?>
    <div id="<?php echo $id; ?>" class="fuse-slider">
        <?php
            foreach ($slides as $slide) {
                include ($slide_template);
            } // foreach ()
        ?>
    </div>
    <script type="text/javascript">
        jQuery (document).ready (function () {
            jQuery ('#<?php echo $id; ?>').slick ({
                <?php
                    echo stripslashes (get_post_meta ($this->_post->ID, 'fuse_slider_settings', true));
                ?>
            });
        });
    </script>

<?php endif; ?>