<?php
    /**
     *  This sets up our taxonomy featured image functionality.
     *
     *  @filter fuse_add_taxonomy_feature_images_taxonomies
     */
    
    namespace Fuse\Setup\Theme;
    
    use Fuse\Forms\Field\Image;
    
    
    class TaxonomyImages {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            $taxonomies = apply_filters ('fuse_add_taxonomy_feature_images_taxonomies', get_taxonomies (array (
                'public' => true
            ), 'names'));
            
            foreach ($taxonomies as $tax) {
                if (get_fuse_option ('tax_images_'.$tax, false) == 'yes') {

                    add_action ($tax.'_add_form_fields', array ($this, 'addTermFields'));
                    add_action ($tax.'_edit_form_fields', array ($this, 'editTermFields'), 10, 2);
                    
                    add_action ('created_'.$tax, array ($this, 'saveTermFields'));
                    add_action ('edited_'.$tax, array ($this, 'saveTermFields'));
                    
                    add_filter ('manage_edit-'.$tax.'_columns', array ($this,'customAdminColumns'));
                    add_action ('manage_'.$tax.'_custom_column', array ($this, 'customColumnContent'), 10, 3);
                } // if ()
            } // foreach ()
        } // __construct ()
        
        
        
        
        /**
         *  Add the fields to the add form.
         */
        public function addTermFields () {
            $field = new Image ('fuse_term_featured_image', '');
            ?>
                <div class="form-field">
                    <label for="fuse_term_featured_image"><?php _e ('Featured Image', 'fuse'); ?></label>
                    <?php
                        $field->render ();
                    ?>
                </div>
            <?php
        } // addTermFields ()
        
        /**
         *  Add the fields to the edit form.
         */
        public function editTermFields ($term, $taxonomy) {
            $field = new Image ('fuse_term_featured_image', get_term_meta ($term->term_id, 'fuse_term_featured_image', true));
            ?>
                <tr class="form-field">
                    <th><label for="fuse_term_featured_image"><?php _e ('Featured Image', 'fuse'); ?></label></th>
                    <td>
                        <?php
                            $field->render ();
                        ?>
                    </td>
                </tr>
            <?php
        } // editTermFields ()
        
        
        
        
        /**
         *  Save the terms field values.
         */
        public function saveTermFields ($term_id) {
            if (array_key_exists ('fuse_term_featured_image', $_POST)) {
                update_term_meta ($term_id, 'fuse_term_featured_image', $_POST ['fuse_term_featured_image']);
            } // if ()
            else {
                delete_term_meta ($term_id, 'fuse_term_featured_image');
            } // else
        } // savetermFields ()
        
        
        
        
        /**
         *  Set up our additional columns.
         */
        public function customAdminColumns ($columns) {
            $columns ['fuse_tax_feature_image'] = __ ('Feature Image', 'fuse');
            
            return $columns;
        } // customAdminColumns ()
        
        /**
         *  Output the custom column content.
         */
        public function customColumnContent ($value, $column, $term_id) {
            switch ($column) {
                case 'fuse_tax_feature_image':
                    $image = intval (get_term_meta ($term_id, 'fuse_term_featured_image', true));
                    
                    if ($image > 0) {
                        $image = fuse_get_term_feature_image ($term_id, 'thumbnail');
                        ?>
                            <img src="<?php echo esc_url ($image [0]); ?>" alt="<?php esc_attr_e ('Fatured image', 'fuse'); ?>" width="70" height="70" style="width: 70px; height: 70px;" />
                        <?php
                    } // if ()
                    else {
                        echo '&nbsp;';
                    } // else
                    break;
            } // switch ()
        } // customColumnContent ()
        
    } // class TaxonomyImages ()