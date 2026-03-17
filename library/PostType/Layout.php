<?php
    /**
     *  @package fusecms
     *
     *  @action before_fuse_post_layout
     *  @action after_fuse_post_layout
     *
     *  This is our Layout class. We use this to manage layouts for the various
     *  pages, content types, taxonomies, etc, of the site.
     */
    
    namespace Fuse\PostType;
    
    use Fuse\PostType;
    
    
    class Layout extends PostType {
        
        /**
         *  @var array These are the additional page types for the layouts.
         */
        protected $_other_pages = array (
            '404' => '404 Page',
            'archive' => 'Date-Based Archive Pages',
            'search' => 'Search Results Pages',
            'author' => 'Author Details Pages'
        );
        
        
        
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('fuse_layouts', 'Layout', 'Layouts', array (
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'query_var' => false,
                'show_in_menu' => 'themes.php',
                'rewrite' => false,
                'supports' => array (
                    'title'
                ),
                'text_domain' => 'fuse'
            ));
            
            // Add thea ction to save the post layuout values.
            add_action ('save_post', array ($this, 'savePostLayout'));
            
            // Add our layout chooer post types separately
            add_action ('add_meta_boxes', array ($this, 'addLayoutMetaBoxes'));
        } // __construct ()
        
        
        
        
        /**
         *  Set up our meta boxes.
         */
        public function addMetaBoxes () {
            add_meta_box ('fuse_layout_parts_meta', __ ('Display Areas', 'fuse'), array ($this, 'partsMeta'), $this->getSlug (), 'normal', 'default');
            add_meta_box ('fuse_layout_defaults_meta', __ ('Set Layout Defaults', 'fuse'), array ($this, 'defaultsMeta'), $this->getSlug (), 'normal', 'default');
            add_meta_box ('fuse_layout_advanced_meta', __ ('Advanced Layout Options', 'fuse'), array ($this, 'advancedMeta'), $this->getSlug (), 'normal', 'default');
        } // addMetaBoxes ()
        
        /**
         *  Add the meta boxes for the layout chooser.
         */
        public function addLayoutMetaBoxes () {
            // Let each public post type select their layout individually
            $public_post_types = get_post_types (array (
                'public' => true
            ));

            foreach ($public_post_types as $type) {
                add_meta_box ('fuse_post_layout_meta', __ ('Layout', 'fuse'), array ($this, 'postLayoutMeta'), $type, 'side', 'default');
            } // foreach ()
        } // addLayoutMetaBoxes ()
        
        /**
         *  Set up the display areas meta box.
         */
        public function partsMeta ($post) {
            global $wp_registered_sidebars;
            
            $parts = get_post_meta ($post->ID, 'fuse_layout_parts', true);
            
            if (is_array ($parts) == false) {
                $parts = array (
                    'header' => true,
                    'left_1' => false,
                    'left_2' => false,
                    'right_1' => true,
                    'right_2' => false,
                    'footer' => true
                );
            } // if ()
            
            $sidebar_left_1 = get_post_meta ($post->ID, 'fuse_parts_sidebar_left_1', true);
            $sidebar_left_2 = get_post_meta ($post->ID, 'fuse_parts_sidebar_left_2', true);
            $sidebar_right_1 = get_post_meta ($post->ID, 'fuse_parts_sidebar_right_1', true);
            $sidebar_right_2 = get_post_meta ($post->ID, 'fuse_parts_sidebar_right_2', true);
            ?>
                <script type="text/javascript">
                    var button_show = '<?php _e ('Show', 'fuse'); ?>';
                    var button_hide = '<?php _e ('Hide', 'fuse'); ?>';
                    
                    jQuery (document).ready (function () {
                        jQuery ('.layout-parts-table a').click (function (e) {
                            e.preventDefault ();
                            
                            var btn = jQuery (this);
                            var block = btn.attr ('id');
                            var form_field = jQuery ('#fuse_parts_' + block);

                            btn.toggleClass ('button-primary');
                            jQuery ('#layout-parts-' + block).toggleClass ('show');

                            if (btn.hasClass ('button-primary')) {
                                btn.html (button_hide);
                                form_field.val ('show');
                            } // if ()
                            else {
                                btn.html (button_show);
                                form_field.val ('hide');
                            } // else
                        });
                    });
                </script>
                
                <input type="hidden" id="fuse_parts_header" name="fuse_parts_header" value="<?php echo $parts ['header'] == true ? 'show' : 'hide'; ?>" />
                <input type="hidden" id="fuse_parts_footer" name="fuse_parts_footer" value="<?php echo $parts ['footer'] == true ? 'show' : 'hide'; ?>" />
                <input type="hidden" id="fuse_parts_left_1" name="fuse_parts_left_1" value="<?php echo $parts ['left_1'] == true ? 'show' : 'hide'; ?>" />
                <input type="hidden" id="fuse_parts_left_2" name="fuse_parts_left_2" value="<?php echo $parts ['left_2'] == true ? 'show' : 'hide'; ?>" />
                <input type="hidden" id="fuse_parts_right_1" name="fuse_parts_right_1" value="<?php echo $parts ['right_1'] == true ? 'show' : 'hide'; ?>" />
                <input type="hidden" id="fuse_parts_right_2" name="fuse_parts_right_2" value="<?php echo $parts ['right_2'] == true ? 'show' : 'hide'; ?>" />
                
                <table class="layout-parts-table">
                    <tr>
                        <td colspan="5" id="layout-parts-header" class="<?php if ($parts ['header'] === true) echo ' show'; ?>">
                            <h4><?php _e ('Header', 'fuse'); ?></h4>
                            <?php if ($parts ['header'] === true): ?>
                                <a id="header" class="button button-primary" href="#"><?php _e ('Hide', 'fuse'); ?></a>
                            <?php else: ?>
                                <a id="header" class="button" href="#"><?php _e ('Show', 'fuse'); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td id="layout-parts-left_1" class="<?php if ($parts ['left_1'] === true) echo ' show'; ?>">
                            <h4><?php _e ('Left Sidebar 1', 'fuse'); ?></h4>
                            <?php if ($parts ['left_1'] === true): ?>
                                <a id="left_1" class="button button-primary" href="#"><?php _e ('Hide', 'fuse'); ?></a>
                            <?php else: ?>
                                <a id="left_1" class="button" href="#"><?php _e ('Show', 'fuse'); ?></a>
                            <?php endif; ?>
                            <br />
                            <br />
                            <select name="fuse_parts_sidebar_left_1">
                                <?php foreach ($wp_registered_sidebars as $alias => $sidebar): ?>
                                    <option value="<?php esc_attr_e ($sidebar ['id']); ?>"<?php selected ($sidebar_left_1, $sidebar ['id']); ?>><?php echo $sidebar ['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td id="layout-parts-left_2" class="<?php if ($parts ['left_2'] === true) echo ' show'; ?>">
                            <h4><?php _e ('Left Sidebar 2', 'fuse'); ?></h4>
                            <?php if ($parts ['left_2'] === true): ?>
                                <a id="left_2" class="button button-primary" href="#"><?php _e ('Hide', 'fuse'); ?></a>
                            <?php else: ?>
                                <a id="left_2" class="button" href="#"><?php _e ('Show', 'fuse'); ?></a>
                            <?php endif; ?>
                            <br />
                            <br />
                            <select name="fuse_parts_sidebar_left_2">
                                <?php foreach ($wp_registered_sidebars as $alias => $sidebar): ?>
                                    <option value="<?php esc_attr_e ($sidebar ['id']); ?>"<?php selected ($sidebar_left_2, $sidebar ['id']); ?>><?php echo $sidebar ['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="show">
                            <h4><?php _e ('Page Content', 'fuse'); ?></h4>
                            <p><?php _e ('Always Shown', 'fuse'); ?></p>
                        </td>
                        <td id="layout-parts-right_1" class="<?php if ($parts ['right_1'] === true) echo ' show'; ?>">
                            <h4><?php _e ('Right Sidebar 1', 'fuse'); ?></h4>
                            <?php if ($parts ['right_1'] === true): ?>
                                <a id="right_1" class="button button-primary" href="#"><?php _e ('Hide', 'fuse'); ?></a>
                            <?php else: ?>
                                <a id="right_1" class="button" href="#"><?php _e ('Show', 'fuse'); ?></a>
                            <?php endif; ?>
                            <br />
                            <br/>
                            <select name="fuse_parts_sidebar_right_1">
                                <?php foreach ($wp_registered_sidebars as $alias => $sidebar): ?>
                                    <option value="<?php esc_attr_e ($sidebar ['id']); ?>"<?php selected ($sidebar_right_1, $sidebar ['id']); ?>><?php echo $sidebar ['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td id="layout-parts-right_2" class="<?php if ($parts ['right_2'] === true) echo ' show'; ?>">
                            <h4><?php _e ('Right Sidebar 2', 'fuse'); ?></h4>
                            <?php if ($parts ['right_2'] === true): ?>
                                <a id="right_2" class="button button-primary" href="#"><?php _e ('Hide', 'fuse'); ?></a>
                            <?php else: ?>
                                <a id="right_2" class="button" href="#"><?php _e ('Show', 'fuse'); ?></a>
                            <?php endif; ?>
                            <br />
                            <br />
                            <select name="fuse_parts_sidebar_right_2">
                                <?php foreach ($wp_registered_sidebars as $alias => $sidebar): ?>
                                    <option value="<?php esc_attr_e ($sidebar ['id']); ?>"<?php selected ($sidebar_right_2, $sidebar ['id']); ?>><?php echo $sidebar ['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" id="layout-parts-footer" class="<?php if ($parts ['footer'] === true) echo ' show'; ?>">
                            <h4><?php _e ('Footer', 'fuse'); ?></h4>
                            <?php if ($parts ['footer'] === true): ?>
                                <a id="footer" class="button button-primary" href="#"><?php _e ('Hide', 'fuse'); ?></a>
                            <?php else: ?>
                                <a id="footer" class="button" href="#"><?php _e ('Show', 'fuse'); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            <?php
        } // partsMeta ()
        
        /**
         *  Set up the defaults chooser meta.
         *
         *  @param WP_POST $post The post object.
         */
        public function defaultsMeta ($post) {
?>
    <p><?php _e ('Choose which items this layout will be the default for from the lists below.', 'fuse'); ?></p>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e ('Default', 'fuse'); ?></th>
                <th><?php _e ('Post Types', 'fuse'); ?></th>
                <th><?php _e ('Taxonomies', 'fuse'); ?></th>
                <th><?php _e ('Other Pages', 'fuse'); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th><?php _e ('Default', 'fuse'); ?></th>
                <th><?php _e ('Post Types', 'fuse'); ?></th>
                <th><?php _e ('Taxonomies', 'fuse'); ?></th>
                <th><?php _e ('Other Pages', 'fuse'); ?></th>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <td>
                    <ul>
                        <li>
                            <label>
                                <input type="checkbox" name="fuse_layout_defaults_global" value="<?php echo $post->ID; ?>"<?php checked ($post->ID, get_option ('fuse_layout_defaults_global', 0)); ?> />
                                <?php _e ('Global Default Layout', 'fuse'); ?>
                            </label>
                        </li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <?php
                            $archives = array ();
                        ?>
                        <?php foreach ($this->_getPublicPostTypes () as $type): ?>
                            <?php
                                $archives [] = $type;
                            ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="fuse_layout_defaults[posttypes][]" value="<?php echo $type->name; ?>"<?php checked ($post->ID, get_option ('fuse_layout_defaults_posttypes_'.$type->name, 0)); ?> />
                                    <?php echo $type->label; ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                        <?php if (count ($archives) > 0): ?>
                            <h4><?php _e ('Archive Types', 'fuse'); ?></h4>
                            <ul>
                                <?php foreach ($archives as $type): ?>
                                <li>
                                    <label>
                                        <input type="checkbox" name="fuse_layout_defaults[posttypesarchives][]" value="<?php echo $type->name; ?>"<?php checked ($post->ID, get_option ('fuse_layout_defaults_posttypesarchives_'.$type->name, 0)); ?> />
                                        <?php echo $type->label; ?>
                                    </label>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </ul>
                </td>
                <td>
                    <ul>
                        <?php foreach ($this->_getPublicTaxonomies () as $tax): ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="fuse_layout_defaults[taxonomies][]" value="<?php echo $tax->name; ?>"<?php checked ($post->ID, get_option ('fuse_layout_defaults_taxonomies_'.$tax->name, 0)); ?> />
                                    <?php echo $tax->label; ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </td>
                <td>
                    <ul>
                        <?php foreach ($this->_other_pages as $key => $val): ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="fuse_layout_defaults[other][]" value="<?php echo $key; ?>"<?php checked ($post->ID, get_option ('fuse_layout_defaults_other_'.$key, 0)); ?> />
                                    <?php _e ($val, 'fuse'); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>
<?php
        } // defaultsMeta ()
        
        /**
         *  Set up the advanced settings meta box.
         */
        public function advancedMeta ($post) {
            ?>
                <table class="form-table">
                    <tr>
                        <th><?php _e ('Additional CSS classes', 'fuse'); ?></th>
                        <td>
                            <input type="text" name="fuse_layout_advanced_css" value="<?php esc_attr_e (get_post_meta ($post->ID, 'fuse_layout_advanced_css', true)); ?>" class="large-text" />
                        </td>
                    </tr>
                </table>
            <?php
        } // advancedMeta ()
        
        /**
         *  Set up the post layout meta box.
         *
         *  Note that any fields that are added using the available actions will
         *  need to have their values saved manually as this is not an automated
         *  process.
         */
        public function postLayoutMeta ($post) {
            $selected_layout = get_post_meta ($post->ID, 'fuse_post_layout', true);

            $layouts = get_posts (array (
                'numberposts' => -1,
                'post_type' => 'fuse_layouts',
                'orderby' => 'title',
                'order' => 'ASC'
            ));
?>
    <?php
        do_action ('before_fuse_post_layout');
    ?>
    
    <select name="fuse_post_layout">
        <option value="default">Default</option>
        <?php foreach ($layouts as $layout): ?>
            <option value="<?php echo $layout->ID; ?>"<?php selected ($layout->ID, $selected_layout); ?>><?php echo $layout->post_title; ?></option>
        <?php endforeach; ?>
    </select>
    
    <?php
        do_action ('after_fuse_post_layout');
    ?>
<?php
        } // postLayoutMeta ()
        
        
        
        
        /**
         *  Save the posts values.
         */
        public function savePost ($post_id, $post) {
            if (array_key_exists ('fuse_parts_header', $_POST)) {
                $parts = array (
                    'header' => $_POST ['fuse_parts_header'] == 'show' ? true : false,
                    'left_1' => $_POST ['fuse_parts_left_1'] == 'show' ? true : false,
                    'left_2' => $_POST ['fuse_parts_left_2'] == 'show' ? true : false,
                    'right_1' => $_POST ['fuse_parts_right_1'] == 'show' ? true : false,
                    'right_2' => $_POST ['fuse_parts_right_2'] == 'show' ? true : false,
                    'footer' => $_POST ['fuse_parts_footer'] == 'show' ? true : false
                );
                
                update_post_meta ($post_id, 'fuse_layout_parts', $parts);
                
                update_post_meta ($post_id, 'fuse_parts_sidebar_left_1', $_POST ['fuse_parts_sidebar_left_1']);
                update_post_meta ($post_id, 'fuse_parts_sidebar_left_2', $_POST ['fuse_parts_sidebar_left_2']);
                update_post_meta ($post_id, 'fuse_parts_sidebar_right_1', $_POST ['fuse_parts_sidebar_right_1']);
                update_post_meta ($post_id, 'fuse_parts_sidebar_right_2', $_POST ['fuse_parts_sidebar_right_2']);
                
                /**
                 *  Check for defaults
                 */
                if (array_key_exists ('fuse_layout_defaults_global', $_POST) && $_POST ['fuse_layout_defaults_global'] == $post_id) {
                    update_option ('fuse_layout_defaults_global', $post_id);
                } // if ()
                
                // Post types
                foreach ($this->_getPublicPostTypes () as $type) {
                    // Check post type
                    if (array_key_exists ('fuse_layout_defaults', $_POST) && array_key_exists ('posttypes', $_POST ['fuse_layout_defaults']) && in_array ($type->name, $_POST ['fuse_layout_defaults']['posttypes'])) {
                        /**
                         *  Set this as the default layout.
                         */
                        update_option ('fuse_layout_defaults_posttypes_'.$type->name, $post->ID);
                    } // if ()
                    else {
                        /**
                         *  Not set so make sure that we un-set this as the
                         *  default if it is set.
                         */
                        $current = get_option ('fuse_layout_defaults_posttypes_'.$type->name, 0);

                        if ($current == $post->ID) {
                            update_option ('fuse_layout_defaults_posttypes_'.$type->name, 0);
                        } // if ()
                    } // else

                    // Check post type archives
                    if (array_key_exists ('fuse_layout_defaults', $_POST) && array_key_exists ('posttypesarchives', $_POST ['fuse_layout_defaults']) && in_array ($type->name, $_POST ['fuse_layout_defaults']['posttypesarchives'])) {
                        /**
                         *  Set this as the default layout.
                         */
                        update_option ('fuse_layout_defaults_posttypesarchives_'.$type->name, $post->ID);
                    } // if ()
                    else {
                        /**
                         *  Not set so make sure that we un-set this as the
                         *  default if it is set.
                         */
                        $current = get_option ('fuse_layout_defaults_posttypesarchives_'.$type->name, 0);

                        if ($current == $post->ID) {
                            update_option ('fuse_layout_defaults_posttypesarchives_'.$type->name, 0);
                        } // if ()
                    } // else
                } // foreach ()

                // Taxonomies
                foreach ($this->_getPublicTaxonomies () as $type) {
                    // Check post type
                    if (array_key_exists ('fuse_layout_defaults', $_POST) && array_key_exists ('taxonomies', $_POST ['fuse_layout_defaults']) && in_array ($type->name, $_POST ['fuse_layout_defaults']['taxonomies'])) {
                        /**
                         *  Set this as the default layout.
                         */
                        update_option ('fuse_layout_defaults_taxonomies_'.$type->name, $post->ID);
                    } // if ()
                    else {
                        /**
                         *  Not set so make sure that we un-set this as the
                         *  default if it is set.
                         */
                        $current = get_option ('fuse_layout_defaults_taxonomies_'.$type->name, 0);

                        if ($current == $post->ID) {
                            update_option ('fuse_layout_defaults_taxonomies_'.$type->name, 0);
                        } // if ()
                    } // else
                } // foreach ()

                // Other
                foreach ($this->_other_pages as $key => $val) {
                    // Check post type
                    if (array_key_exists ('fuse_layout_defaults', $_POST) && array_key_exists ('other', $_POST ['fuse_layout_defaults']) && in_array ($key, $_POST ['fuse_layout_defaults']['other'])) {
                        /**
                         *  Set this as the default layout.
                         */
                        update_option ('fuse_layout_defaults_other_'.$key, $post->ID);
                    } // if ()
                    else {
                        /**
                         *  Not set so make sure that we un-set this as the
                         *  default if it is set.
                         */
                        $current = get_option ('fuse_layout_defaults_other_'.$key, 0);

                        if ($current == $post->ID) {
                            update_option ('fuse_layout_defaults_other_'.$key, 0);
                        } // if ()
                    } // else
                } // foreach ()
                
                if (array_key_exists ('fuse_layout_advanced_css', $_POST)) {
                    update_post_meta ($post_id, 'fuse_layout_advanced_css', $_POST ['fuse_layout_advanced_css']);
                } // if ()
            } // if ()
        } // savePost ()
        
        /**
         *  Save the post layout option.
         */
        public function savePostLayout ($post_id) {
            if (defined ('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            } // if ()
            else {
                // Post layout
                if (array_key_exists ('fuse_post_layout', $_POST)) {
                    update_post_meta ($post_id, 'fuse_post_layout', $_POST ['fuse_post_layout']);
                } // if ()s
            } // else
        } // savePostLayout ()
        
        
        
        
        /**
         *  Add in any custom columns for this post type.
         *
         *  @param array $columns The list of existing columns.
         *
         *  @return array Your completed list of columns.
         */
        public function adminListColumns ($columns) {
            $columns ['fuse_layout_global'] = __ ('Global Default', 'fuse');
            $columns ['fuse_layout_posttypes'] = __ ('Post Types', 'fuse');
            $columns ['fuse_layout_taxonomies'] = __ ('Taxonomies', 'fuse');
            $columns ['fuse_layout_other'] = __ ('Other Pages', 'fuse');

            return $columns;
        } // adminListColumns ()

        /**
         *  Output the data values for your custom columns.
         *
         *  @param string $column The name of the column.
         *  @param int $post_id The ID of the current post.
         */
        public function adminListValues ($column, $post_id) {
            switch ($column) {
                case 'fuse_layout_global':
                    if (get_option ('fuse_layout_defaults_global', 0) == $post_id) {
                        echo '<span class="admin-bold">'.__ ('Global Default', 'fuse').'</span>';
                    } // if ()
                    else {
                        echo '&nbsp;';
                    } // else

                    break;
                case 'fuse_layout_posttypes':
                    $sep = '';

                    foreach ($this->_getPublicPostTypes () as $type) {
                        if (get_option ('fuse_layout_defaults_posttypes_'.$type->name, 0) == $post_id) {
                            echo $sep.$type->label;
                            $sep = '<br />';
                        } // if ()

                        if ($type->has_archive === true) {
                            if (get_option ('fuse_layout_defaults_posttypesarchives_'.$type->name, 0) == $post_id) {
                                echo $sep.$type->label.' '.__ ('Archives', 'fuse');
                                $sep = '<br />';
                            } // if ()
                        } // if ()
                    } // foreach ()

                    echo '&nbsp;';
                    break;
                case 'fuse_layout_taxonomies':
                    $sep = '';

                    foreach ($this->_getPublicTaxonomies () as $type) {
                        if (get_option ('fuse_layout_defaults_taxonomies_'.$type->name, 0) == $post_id) {
                            echo $sep.$type->label;
                            $sep = '<br />';
                        } // if ()
                    } // foreach ()

                    echo '&nbsp;';
                    break;
                case 'fuse_layout_other':
                    $sep = '';

                    foreach ($this->_other_pages as $key => $val) {
                        if (get_option ('fuse_layout_defaults_other_'.$key, 0) == $post_id) {
                            echo $sep.$val;
                            $sep = '<br />';
                        } // if ()
                    } // foreach ()

                    echo '&nbsp;';
                    break;
            } // switch ()
        } // adminListValues ()




        /**
         *  Get the list of public post types.
         *
         *  @return array An array of post type objects.
         */
        protected function _getPublicPostTypes () {
            return get_post_types (array (
                'public' => true
            ), 'objects');
        } // _getPublicPostTypes ()

        /**
         *  Get the list of public taxonomies.
         *
         *  @return array An array of taxonomy objects.
         */
        protected function _getPublicTaxonomies () {
            return get_taxonomies (array (
                'public' => true
            ), 'objects');
        } // _getPublicTaxonomies ()
        
    } // class Layout ()