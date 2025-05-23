<?php
    /**
     *  Set up our sliders slide post type.
     */
    
    namespace Fuse\PostType\Slider;
    
    use Fuse\PostType;
    use Fuse\Forms\Component\Field;;
    
    
    class Slide extends PostType {
        
        protected $_parent_post_type = 'fuse_slider';
        
        protected $_dates;
        
        
        
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('fuse_slide', __ ('Slide', 'fuse'), __ ('Slides', 'fuse'), array (
                'public' => false,
                'publicly_queryable' => false,
                'show_in_rest' => true,
                'supports' => array (
                    'title',
                    'editor',
                    'page-attributes',
                    'thumbnail'
                )
            ));
            
            $this->_dates = array (
                'start' => __ ('Start time', 'fuse'),
                'end' => __ ('End time', 'fuse')
            );
        } // __construct ()
        
        
        
        
        /**
         *  Add our meta boxes.
         */
        public function addMetaBoxes () {
            add_meta_box ('fuse_slider_slide_display_meta', __ ('Display Settings', 'fuse'), array ($this, 'displayMeta'), $this->getSlug (), 'normal', 'default');
        } // addMetaBoxes ()
        
        /**
         *  Set up the display settings meta box.
         */
        public function displayMeta ($post) {
            ?>
                <table class="form-table">
                    <?php foreach ($this->_dates as $key => $label): ?>
                        <?php
                            $time = get_post_meta ($post->ID, 'fuse_slide_'.$key, true);
                        ?>
                    
                        <tr>
                            <th><?php echo $label; ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" class="fuse_slide_set_date" name="fuse_slide_set_<?php esc_attr_e ($key); ?>" value="set"<?php if (strlen ($time) > 0) echo ' checked="checked"'; ?> />
                                    <?php printf (__ ('Set %s for this slide', 'fuse'), strtolower ($label)); ?>
                                </label>
                                <p>
                                    <input type="datetime-local" name="fuse_slide_<?php esc_attr_e ($key); ?>" value="<?php esc_attr_e ($time); ?>" />
                                </p>
                            </td>
                        </tr>
                    
                    <?php endforeach; ?>
                </table>
                <script type="text/javascript">
                    jQuery (document).ready (function () {
                        jQuery ('.fuse_slide_set_date').each (function () {
                            checkDateVislbility (jQuery (this));
                        });
                    
                        jQuery ('.fuse_slide_set_date').on ('change', function () {
                            checkDateVislbility (jQuery (this));
                        });
                    });
                    
                    function checkDateVislbility (el) {
                        let input = el.closest ('td').find ('p');
                            
                            if (el.prop ('checked')) {
                                input.show ();
                            } // if ()
                            else {
                                input.hide ();
                            } // else
                    } // checkDateVisibility ()
                </script>
            <?php
        } // displayMeta ()
        
        
        
        
        /**
         *  Save the posts values.
         */
        public function savePost ($post_id, $post) {
            // Display dates
            foreach ($this->_dates as $key => $label) {
                if (array_key_exists ('fuse_slide_set_'.$key, $_POST) && $_POST ['fuse_slide_set_'.$key] == 'set') {
                    update_post_meta ($post_id, 'fuse_slide_'.$key, $_POST ['fuse_slide_'.$key]);
                } // if ()
                else {
                    delete_post_meta ($post_id, 'fuse_slide_'.$key);
                } // else
            } // foreach ()
        } // savePost ()
        
        
        
        
        /**
         *  Allow others to over-ride the existing admin list columns.
         *
         *  @param array $columns The existing columns.
         *
         *  @return array The completed column list.
         */
        public function adminListColumns ($columns) {
            $columns ['fuse_slide_order'] = __ ('Display Order', 'fuse');
            $columns ['fuse_slide_status'] = __ ('Display Status', 'fuse');
            
            return $columns;
        } // adminListColumns ()
        
        /**
         *  Output the values for our custom admin list columns.
         *
         *  @param string $column The name of the column
         *  @param int $post_id The ID of the post.
         */
        public function adminListValues ($column, $post_id) {
            switch ($column) {
                case 'fuse_slide_order':
                    $post = get_post ($post_id);
                    echo $post->menu_order;
                    break;
                case 'fuse_slide_status':
                    $this->_statusColumn ($post_id);
                    break;
            } // switch ()
        } // adminListValues ()
        
        
        
        
        /**
         *  Display the status column
         */
        protected function _statusColumn ($post_id) {
            $sep = '';
            $now = new \DateTime (current_time ('Y-m-d H:i:s'));
            
            foreach ($this->_dates as $key => $label) {
                $date = get_post_meta ($post_id, 'fuse_slide_'.$key, true);
                
                echo $sep;
                
                if (strlen ($date) > 0) {
                    $date = new \DateTime ($date);
                    
                    if (($key == 'start' && $date < $now) || ($key == 'end' && $date > $now)) {
                        $class = 'admin-bold admin-green';
                    } // if ()
                    else {
                        $class = 'admin-red';
                    } // else
                    
                    echo '<span class="'.$class.'">'.sprintf (__ ('%s: ', 'fuse'), $label).$date->format ('j/n/Y h:ia').'</span>';
                } // if ()
                else {
                    echo '<span class="admin-light">'.sprintf (__ ('%s not set', 'fuse'), $label).'</span>';
                } // else
                
                $sep = '<br />';
            } // foreach ()
        } // _statusColumn ()
        
    } // class Slide