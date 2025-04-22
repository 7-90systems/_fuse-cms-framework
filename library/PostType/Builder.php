<?php
    /**
     *  @package fusecms
     *
     *  This is our post tyep builder class. We use this to allow non-technical
     *  users to create new post types quickly and easily in the admin area.
     */
    
    namespace Fuse\PostType;
    
    use Fuse\PostType;
    use Fuse\Model;
    use Fuse\Forms;
    
    
    class Builder extends PostType {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('fuse_posttype', 'Post Type', 'Post Type Builder', array (
                'public' => false,
                'publicly_queryable' => false,
                'rewrite' => false,
                'show_in_rest' => false,
                'show_in_menu' => 'fusesettings',
                'supports' => array (
                    'title'
                ),
                'text_domain' => 'fuse'
            ));
        } // __construct ()
        
        
        
        
        /**
         *  Set up our meta boxes.
         */
        public function addMetaBoxes () {
            add_meta_box ('fuse_posttype_builder_labels_meta', __ ('Post Type Labels', 'fuse'), array ($this, 'labelsMeta'), $this->getSlug (), 'normal', 'default');
            add_meta_box ('fuse_posttype_builder_settings_meta', __ ('Post Type Settings', 'fuse'), array ($this, 'settingsMeta'), $this->getSlug (), 'normal', 'default');
            add_meta_box ('fuse_postytpe_builder_metaboxes_meta', __ ('Meta Boxes', 'fuse'), array ($this, 'metaboxesMeta'), $this->getSlug (), 'normal', 'default');
        } // addMetaBoxes ()
        
        /**
         *  Add the settings meta box.
         */
        public function labelsMeta ($post) {
            $use_advanced = get_post_meta ($post->ID, 'fuse_posttype_builder_labels_advanced', true);
            $model = new Model\PostType\Builder ($post);
            
            if ($use_advanced != 'yes') {
                $use_advanced = 'no';
            } // if ()
            ?>
                <table class="form-table">
                    
                    <tr class=".fuse-forms-container">
                        <th><?php _e ('Label types', 'fuse'); ?></th>
                        <td>
                            <ul>
                               <li>
                                    <label>
                                        <input class="fuse-postytpe-builder-labels-advanced" type="radio" name="fuse_posttype_builder_labels_advanced" value="no"<?php checked ('no', $use_advanced); ?> />
                                        <?php _e ('Use standard labels', 'fuse'); ?>
                                    </label>
                               </li>
                               <li>
                                    <label>
                                        <input class="fuse-postytpe-builder-labels-advanced" type="radio" name="fuse_posttype_builder_labels_advanced" value="yes"<?php checked ('yes', $use_advanced); ?> />
                                        <?php _e ('Use advanced labels', 'fuse'); ?>
                                    </label>
                               </li>
                            </ul>
                        </td>
                    </tr>
                    
                    <?php foreach ($model->getLabels () as $key => $label): ?>

                        <tr class="label-<?php echo $label ['type']; ?>"<?php if ($label ['type'] == 'advanced' && $use_advanced == 'no') echo ' style="display: none;"'; ?>>
                            <th><?php echo $label ['label']; ?></th>
                            <td>
                                <input type="text" name="fuse_posttype_builder_labels[<?php esc_attr_e ($key); ?>]" value="<?php esc_attr_e ($label ['value']); ?>" class="regular-text"<?php if (array_key_exists ('placeholder', $label)) echo ' placeholder="'.esc_attr ($label ['placeholder']).'"'; ?> />
                            </td>
                        </tr>
                    
                    <?php endforeach; ?>

                </table>
                <script type="text/javascript">
                    jQuery (document).ready (function () {
                        let advanced_labels = jQuery ('tr.label-advanced');
                        
                        jQuery ('.fuse-postytpe-builder-labels-advanced').click (function () {
                            let val = jQuery (this).val ();
                            
                            if (val == 'yes') {
                                advanced_labels.show ();
                            } // if ()
                            else {
                                advanced_labels.hide ();
                            } // else
                        });
                    });
                </script>
            <?php
        } // labelsMeta ()
        
        /**
         *  Set up the settings meta box.
         */
        public function settingsMeta ($post) {
            $model = new Model\PostType\Builder ($post);
            ?>
                <table class="form-table">
                    <tr>
                        <th><?php _e ('Post type slug', 'fuse'); ?></th>
                        <td>
                            <input type="text" name="fuse_posttype_builder_slug" value="<?php esc_attr_e (get_post_meta ($post->ID, 'fuse_posttype_builder_slug', true)); ?>" maxlength="20" />
                            <p class="description"><?php _e ('Must not exceed 20 characters and may only contain lowercase alphanumeric characters, dashes, and underscores.', 'fuse'); ?></p>
                        </td>
                    </tr>
                    
                    <?php foreach ($model->getSettings () as $key => $setting): ?>
                    
                        <tr>
                            <th><?php echo $setting ['label']; ?></th>
                            <td>
                                <?php
                                    switch ($setting ['type']) {
                                        case 'toggle':
                                            $this->_toggleSetting ($key, $setting);
                                            break;
                                        case 'options':
                                            $this->_optionsSetting ($key, $setting);
                                            break;
                                        default:
                                            $this->_fieldSetting ($key, $setting);
                                    } // switch ()
                                ?>
                            </td>
                        </tr>
                    
                    <?php endforeach; ?>
                </table>
            <?php
        } // settingsMeta ()
        
        // Add the meta boxes meta box.
        public function metaboxesMeta ($post) {
            $metaboxes = json_decode (get_post_meta ($post->ID, 'fuse_builder_metaboxes', true));
            
            if (is_array ($metaboxes) === false) {
                $metaboxes = array ();
            } // if ()
            ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th style="width: 60%;"><?php _e ('Main Column', 'fuse'); ?></th>
                            <th style="width: 40%;"><?php _e ('Side Column', 'fuse'); ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th style="width: 60%;"><?php _e ('Main Column', 'fuse'); ?></th>
                            <th style="width: 40%;"><?php _e ('Side Column', 'fuse'); ?></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <tr>
                            <td style="width: 60%;">
                                <div id="fuse_posttype_builder_metaboxes_main" class="fuse_posttype_builder_metaboxes" data-location="normal">
                                    <?php
                                        foreach ($metaboxes as $metabox) {
                                            if ($metabox->section == 'main') {
                                                echo $this->_metaboxTemplateHtml ($metabox->name, $metabox->fields);
                                            } // if ()
                                        } // foreach ()
                                    ?>
                                </div>
                                <button class="fuse_builder_new_metabox_add button"><?php _e ('Add Meta Box', 'fuse'); ?></button>
                            </td>
                            <td style="width: 40%;">
                                <div id="fuse_posttype_builder_metaboxes_side" class="fuse_posttype_builder_metaboxes" data-location="side">
                                    <?php
                                        foreach ($metaboxes as $metabox) {
                                            if ($metabox->section == 'side') {
                                                echo $this->_metaboxTemplateHtml ($metabox->name);
                                            } // if ()
                                        } // foreach ()
                                    ?>
                                </div>
                                <button class="fuse_builder_new_metabox_add button"><?php _e ('Add Meta Box', 'fuse'); ?></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- These are our HTML templates  -->
                <template id="fuse_builder_meta_box">
                    <?php
                        echo $this->_metaboxTemplateHtml ();
                    ?>
                </template>
                <template id="fuse_builder_meta_box_field">
                    <?php
                        echo $this->_metaboxFieldTemplateHtml ();
                    ?>
                </template>
                
                <input type="hidden" id="fuse_builder_metaboxes" name="fuse_builder_metaboxes" value="" />
            <?php
        } // metaboxesMeta ()
        
        
        
        
        /**
         *  Save our posts values.
         */
        public function savePost ($post_id, $post) {
            global $wpdb;
            
            // Labels
            if (array_key_exists ('fuse_posttype_builder_labels_advanced', $_POST)) {
                $advanced = $_POST ['fuse_posttype_builder_labels_advanced'];
                $labels = $_POST ['fuse_posttype_builder_labels'];
                
                if ($advanced != 'yes') {
                    $advanced = 'no';
                    
                    $model = new Model\PostType\Builder ($post);
                    
                    foreach ($model->getLabels () as $key => $label) {
                        if ($label ['type'] == 'advanced') {
                            unset ($labels [$key]);
                        } // if ()
                    } // foreach ()
                } // if ()
                
                update_post_meta ($post_id, 'fuse_posttype_builder_labels_advanced', $advanced);
                update_post_meta ($post_id, 'fuse_posttype_builder_labels', $labels);
            } // if ()
            
            // Settings
            if (array_key_exists ('fuse_posttype_builder_slug', $_POST)) {
                // Check slug
                $new_slug = sanitize_key ($_POST ['fuse_posttype_builder_slug']);
                $current_slug = get_post_meta ($post_id, 'fuse_posttype_builder_slug', true);
                
                $existing_post_types = get_posts (array (
                    'numberposts' => 1,
                    'post_type' => 'any',
                    'meta_query' => array (
                        array (
                            'key' => 'fuse_posttype_builder_slug',
                            'value' => $new_slug
                        )
                    )
                ));
                
                if (count ($existing_post_types) == 0) {
                    if ($new_slug != $current_slug) {
                        
                        $wpdb->update ($wpdb->posts, array (
                            'post_type' => $new_slug
                        ), array (
                            'post_type' => $current_slug
                        ), array (
                            '%s'
                        ), array (
                            '%s'
                        ));
                        
                        update_post_meta ($post_id, 'fuse_posttype_builder_slug', $new_slug);
                    } // if ()
                } // if ()
                
                // Save settings
                update_post_meta ($post_id, 'fuse_posttype_builder_settings', $_POST ['fuse_posttype_builder_settings']);
                
                // Metaboxes
                if (array_key_exists ('fuse_builder_metaboxes', $_POST)) {
                    update_post_meta ($post_id, 'fuse_builder_metaboxes', $_POST ['fuse_builder_metaboxes']);
                } // if ()
            } // if ()
        } // savePost ()
        
        
        
        
        /**
         *  Set up a toggle setting.
         */
        protected function _toggleSetting ($key, $setting) {
            $options = array (
                '' => __ ('Default', 'fuse'),
                'no' => __ ('No', 'fuse'),
                'yes' => __ ('Yes', 'fuse')
            );
            
            $value = $setting ['value'];
            
            if ($value == '' && array_key_exists ('default', $setting)) {
                $value = $setting ['default'];
            } // if ()
            ?>
                <select name="fuse_posttype_builder_settings[<?php esc_attr_e ($key); ?>]">
                    <?php foreach ($options as $op_key => $op_label): ?>
                        <option value="<?php esc_attr_e ($op_key); ?>"<?php selected ($value, $op_key); ?>><?php echo $op_label; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php
        } // _toggleSetting ()
        
        /**
         *  Set up an options setting.
         */
        public function _optionsSetting ($key, $setting) {
            $values = array ();
            
            if (array_key_exists ('value', $setting) && is_array ($setting ['value'])) {
                $values = $setting ['value'];
            } // if ()
            elseif (array_key_exists ('default', $setting)) {
                $values = $setting ['default'];
            } // elseif ()
            ?>
                <ul style="margin-top: 0; margin-bottom: 0;">
                    <?php foreach ($setting ['options'] as $op_key => $op_label): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="fuse_posttype_builder_settings[<?php esc_attr_e ($key); ?>][]" value="<?php esc_attr_e ($op_key); ?>"<?php if (in_array ($op_key, $values)) echo ' checked="checked"'; ?> />
                                <?php echo $op_label; ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php
        } // optionsSetting ()
        
        /**
         *  Set up a standard field setting.
         */
        protected function _fieldSetting ($key, $setting) {
            $value = $setting ['value'];
            ?>
                <input type="<?php esc_attr_e ($setting ['type']); ?>" name="fuse_posttype_builder_settings[<?php esc_attr_e ($key); ?>]" value="<?php esc_attr_e ($value); ?>" class="regular-text" />
            <?php
        } // _fieldsetting ()
        
        
        
        
        /**
         *  Get a meta box element template.
         *
         *  @param string $name The name of the meta box.
         *  @param array $fields The fields to add to this meta box.
         *
         *  return string The HTML for the metabox area.
         */
        protected function _metaboxTemplateHtml ($name = '', $fields = array ()) {
            if (strlen ($name) == 0) {
                $name = __ ('New Metabox', 'fuse');
            } // if ()
            
            ob_start ();
            ?>
                <div class="fuse-builder-metabox">
                    
                    <div class="fuse_builder_metabox_title">
                        
                        <a href="#" class="move">
                            <span class="dashicons dashicons-menu"></span>
                        </a>
                        <a href="#" class="expand">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </a>
                        <h4 class="title"><?php echo $name; ?></h4>
                        <a href="#" class="delete">
                            <span class="dashicons dashicons-dismiss"></span>
                        </a>
                        
                    </div>
                    
                    <div class="fuse_builder_metabox_fields" style="display: none;">
                        <table class="form-table">
                            <tr>
                                <th><?php _e ('Metabox Name', 'fuse'); ?></th>
                                <td>
                                    <input type="text" name="metabox-name" value="<?php esc_attr_e ($name); ?>" class="widefat metabox-name" />
                                </td>
                            </tr>
                        </table>
                        <div class="fuse_builder_metabox_fields_list">
                            <?php
                                foreach ($fields as $field) {
                                    echo $this->_metaboxFieldTemplateHtml ($field->name, $field->key, $field->type, $field->settings);
                                } // foreach ()
                            ?>
                        </div>
                        <div style="text-align: right;">
                            <button class="button fuse_builder_field_add"><?php _e ('Add a new field'); ?></button>
                        </div>
                    </div>
                        
                </div>
            <?php
            $html = ob_get_contents ();
            ob_end_clean ();
            
            return $html;
        } // _metaboxTemplateHtml ()
        
        /**
         *  This function sets up our metabox field template.
         */
        protected function _metaboxFieldTemplateHtml ($name = '', $key = '', $type = 'text', $settings = array ()) {
            if (strlen ($name) == 0) {
                $name = __ ('New Field', 'fuse');
            } // if ()
            
            $field_types = array (
                'text' => __ ('Text field', 'fuse'),
                'textarea' => __ ('Text area field', 'fuse'),
                'number' => __ ('Number field', 'fuse'),
                'email' => __ ('Email address', 'fuse'),
                'url' => __ ('Website/page URL', 'fuse'),
                'select' => __ ('Drop down', 'fuse'),
                'posttype' => __ ('Post type', 'fuse'),
                'taxonomy' => __ ('Taxonomy', 'fuse')
            );
            
            $post_types = get_post_types (array (), 'objects');
            $taxonomies = get_taxonomies (array (), 'objects');
            
            ob_start ();
            ?>
                <div class="fuse-builder-metabox-field">
                    
                    <div class="fuse_builder_metabox_field_title">
                        
                        <a href="#" class="move">
                            <span class="dashicons dashicons-menu"></span>
                        </a>
                        <a href="#" class="expand">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </a>
                        <h4 class="title"><?php echo $name; ?></h4>
                        <a href="#" class="delete">
                            <span class="dashicons dashicons-dismiss"></span>
                        </a>
                        
                    </div>
                    
                    <div class="fuse_builder_metabox_field_settings" style="display: none;">
                        <table class="form-table">
                            <tr>
                                <th><?php _e ('Field Name', 'fuse'); ?></th>
                                <td>
                                    <input type="text" name="" value="<?php esc_attr_e ($name); ?>" class="widefat metabox-field-name" />
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e ('Data Key', 'fuse'); ?></th>
                                <td>
                                    <input type="text" name="" value="<?php esc_attr_e ($key); ?>" class="widefat metabox-data-key" />
                                </td>
                            </tr>
                        </table>
                        <div class="fuse_builder_metabox_field_settings_list">
                            <table class="form-table">
                                <tr>
                                    <th><?php _e ('Field type', 'fuse'); ?></th>
                                    <td>
                                        <select name="field_type" class="fuse_builder_field_type widefat">
                                            <?php foreach ($field_types as $key => $label): ?>
                                                <option value="<?php esc_attr_e ($key); ?>"<?php selected ($key, $type); ?>><?php echo $label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                
                                <!-- Textarea field options -->
                                <tr class="fuse_field_options fuse_field_option_textarea"<?php if ($type != 'textarea') echo ' style="display: none;"'; ?>>
                                    <th><?php _e ('Rows to display', 'fuse'); ?></th>
                                    <td>
                                        <input type="number" name="rows" class="widefat" value="<?php echo $this->_getFieldValue ('rows', $settings, 4); ?>" />
                                    </td>
                                </tr>
                                
                                <!-- Number field options -->
                                <tr class="fuse_field_options fuse_field_option_number"<?php if ($type != 'number') echo ' style="display: none;"'; ?>>
                                    <th><?php _e ('Minimum value', 'fuse'); ?></th>
                                    <td>
                                        <input type="number" name="min" class="widefat" value="<?php echo $this->_getFieldValue ('min', $settings); ?>" />
                                    </td>
                                </tr>
                                <tr class="fuse_field_options fuse_field_option_number"<?php if ($type != 'number') echo ' style="display: none;"'; ?>>
                                    <th><?php _e ('Maximum value', 'fuse'); ?></th>
                                    <td>
                                        <input type="number" name="max" class="widefat" value="<?php echo $this->_getFieldValue ('max', $settings); ?>" />
                                    </td>
                                </tr>
                                <tr class="fuse_field_options fuse_field_option_number"<?php if ($type != 'number') echo ' style="display: none;"'; ?>>
                                    <th><?php _e ('Step amount', 'fuse'); ?></th>
                                    <td>
                                        <input type="number" name="step" class="widefat" value="<?php echo $this->_getFieldValue ('step', $settings); ?>" />
                                    </td>
                                </tr>
                                
                                <!-- Select field options -->
                                <tr class="fuse_field_options fuse_field_option_select"<?php if ($type != 'select') echo ' style="display: none;"'; ?>>
                                    <th><?php _e ('Options', 'fuse'); ?></th>
                                    <td>
                                        <textarea name="options" class="widefat" rows="6"><?php echo $this->_getFieldValue ('options', $settings); ?></textarea>
                                        <p class="description"><?php _e ('One option per line, pipe separated for value and label. eg: value | Label', 'fuse'); ?></p>
                                    </td>
                                </tr>
                                
                                <!-- Post type field options -->
                                <?php
                                    $selected_post_type = $this->_getFieldValue ('posttype', $settings);
                                ?>
                                <tr class="fuse_field_options fuse_field_option_posttype"<?php if ($type != 'posttype') echo ' style="display: none;"'; ?>>
                                    <th><?php _e ('Post type', 'fuse'); ?></th>
                                    <td>
                                        <select name="posttype" class="widefat">
                                            <?php foreach ($post_types as $op_type): ?>
                                                <option value="<?php esc_attr_e ($op_type->name); ?>"<?php selected ($op_type->name, $selected_post_type); ?>><?php echo $op_type->label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                
                                <!-- Taxonomy field options -->
                                <?php
                                    $selected_taxonomy = $this->_getFieldValue ('taxonomy', $settings);
                                ?>
                                <tr class="fuse_field_options fuse_field_option_taxonomy"<?php if ($type != 'taxonomy') echo ' style="display: none;"'; ?>>
                                    <th><?php _e ('Taxonomy', 'fuse'); ?></th>
                                    <td>
                                        <select name="taxonomy" class="widefat">
                                            <?php foreach ($taxonomies as $op_type): ?>
                                                <option value="<?php esc_attr_e ($op_type->name); ?>"<?php selected ($op_type->name, $selected_taxonomy); ?>><?php echo $op_type->label; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                
                                <!-- Select type - for select, posttype, taxonomy -->
                                <?php
                                    $select_type = $this->_getFieldValue ('selecttype', $settings);
                                ?>
                                <tr class="fuse_field_options fuse_field_option_select fuse_field_option_posttype fuse_field_option_taxonomy"<?php if (in_array ($type, array ('select', 'posttype', 'taxonomy')) === false) echo ' style="display: none;"'; ?>>
                                    <th><?php _e ('Select type', 'fuse'); ?></th>
                                    <td>
                                        <select name="selecttype" class="widefat">
                                            <option value="single"<?php selected ($select_type, 'single'); ?>><?php _e ('Single value', 'fuse'); ?></option>
                                            <option value="multi"<?php selected ($select_type, 'multi'); ?>><?php _e ('Multiple values', 'fuse'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                
                            </table>
                        </div>
                    </div>
                        
                </div>
            <?php
            $html = ob_get_contents ();
            ob_end_clean ();
            
            return $html;
        } // _metaboxFieldTemplateHtml ()
        
        
        
        
        /**
         *  get the value for a field from the settings.
         */
        protected function _getFieldValue ($name, $settings, $default = '') {
            $value = $default;
            $settings = (array) $settings;
            
            if (array_key_exists ($name, $settings)) {
                $value = $settings [$name];
            } // if ()
            
            return $value;
        } // _getFieldValue ()
        
    } // class Builder