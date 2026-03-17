<?php
    /**
     *  This class sets up our custom post type builder post types.
     */
    
    namespace Fuse\Setup\PostType;
    
    use Fuse\Traits\Singleton;
    use Fuse\Model;
    
    
    class Builder {
        
        use Singleton;
        
        
        
        
        /**
         *  Initialise our class.
         */
        protected function _init () {
            add_action ('init', array ($this, 'registerPostTypes'));
            
            add_action ('add_meta_boxes', array ($this, 'addMetaBoxes'));
            
            add_action ('save_post', array ($this, 'savePost'), 10, 2);
        } // _init ()
        
        
        
        
        /**
         *  Register our post types
         */
        public function registerPostTypes () {
            $posttypes = get_posts (array (
                'numberposts' => -1,
                'post_type' => 'fuse_posttype'
            ));
            
            foreach ($posttypes as $posttype) {
                $model = new Model\PostType\Builder ($posttype);
                
                $args = $this->_formatSettings ($model->getSettings ());
                $args ['labels'] = $this->_formatLabels ($model->getLabels ());
                
                $slug = get_post_meta ($posttype->ID, 'fuse_posttype_builder_slug', true);
                
                if (strlen ($slug) > 0) {
                    register_post_type ($slug, $args);
                } // if ()
            } // foreach ()
        } // registerPostTypes ()
        
        
        
        
        /**
         *  Add our meta boxes.
         */
        public function addMetaBoxes () {
            $posttypes = get_posts (array (
                'numberposts' => -1,
                'post_type' => 'fuse_posttype'
            ));
            
            foreach ($posttypes as $posttype) {
                $metaboxes = json_decode (get_post_meta ($posttype->ID, 'fuse_builder_metaboxes', true));
            
                if (is_array ($metaboxes) === true) {
                    foreach ($metaboxes as $metabox) {
                        $section = $metabox->section;
                        $name = $metabox->name;
                        $slug = get_post_meta ($posttype->ID, 'fuse_posttype_builder_slug', true);
                        
                        if ($section != 'side') {
                            $section = 'normal';
                        } // if ()
                        
                        add_meta_box ('fuse_builder_metabox_'.uniqid (), $name, array ($this, 'metabox'), $slug, $section, 'default', array (
                            'posttype' => $posttype,
                            'name' => $name
                        ));
                    } // foreach ()
                } // if ()
            } // foreach ()
        } // addMetaBoxes ()
        
        /**
         *  Set up our meta boxes!
         */
        public function metabox ($post, $args = array ()) {
            $args = $args ['args'];
            $name = $args ['name'];
            $posttype = $args ['posttype'];
            $metaboxes = json_decode (get_post_meta ($posttype->ID, 'fuse_builder_metaboxes', true));
            
            foreach ($metaboxes as $metabox) {
                if ($metabox->name == $name) {
                    $fields = $metabox->fields;
                    ?>
                        <?php if (count ($fields) > 0): ?>
                        
                            <table class="form-table">
                                
                                <?php foreach ($fields as $field): ?>
                                
                                    <tr>
                                        <th><?php echo $field->name; ?></th>
                                        <td>
                                            <?php
                                                $this->_showField ($post->ID, $field);
                                            ?>
                                        </td>
                                    </tr>
                                
                                <?php endforeach; ?>
                                
                            </table>
                        
                        <?php else: ?>
                        
                            <p><?php _e ('There are no settings for this post type', 'fuse'); ?></p>
                        
                        <?php endif; ?>
                    <?php
                } // if ()
            } // foreach ()
        } // metabox ()
        
        
        
        
        /**
         *  Save the posts values.
         */
        public function savePost ($post_id, $post) {
            $post_types = get_posts (array (
                'numberposts' => -1,
                'post_type' => 'fuse_posttype'
            ));
            
            foreach ($post_types as $post_type) {
                $slug = get_post_meta ($post_type->ID, 'fuse_posttype_builder_slug', true);
                
                if ($slug == $post->post_type) {
                    $metaboxes = json_decode (get_post_meta ($post_type->ID, 'fuse_builder_metaboxes', true));
                    
                    if (is_array ($metaboxes)) {
                        foreach ($metaboxes as $metabox) {
                            foreach ($metabox->fields as $field) {
                                if (array_key_exists ($field->key, $_POST)) {
                                    update_post_meta ($post_id, $field->key, $_POST [$field->key]);
                                } // if ()
                            } // foreach ()
                        } // foreach ()
                    } // if ()
                } // if ()
            } // foreach ()
        } // savePost ()
        
        
        
        
        /**
         *  Format our settings
         */
        protected function _formatSettings ($settings) {
            $formatted = array ();
            
            foreach ($settings as $key => $setting) {
                switch ($setting ['type']) {
                    case 'toggle':
                        if ($setting ['value'] == 'yes') {
                            $value = true;
                        } // if ()
                        elseif ($setting ['value'] == 'no') {
                            $value = false;
                        } // elseif ()
                        else {
                            $value = NULL;
                        } // else
                        
                        break;
                    case 'options':
                        $value = $setting ['value'];
                        break;
                    default:
                        if (strlen ($setting ['value']) > 0) {
                            $value = $setting ['value'];
                        } // if ()
                        else {
                            $value = NULL;
                        } // else
                } // switch ()
                
                if (is_null ($value) === false) {
                    $formatted [$key] = $value;
                } // if ()
            } // foreach ()
            
            return $formatted;
        } // _formatSettings ()
        
        /**
         *  Format our labels.
         */
        protected function _formatLabels ($labels) {
            $formatted = array ();
            
            foreach ($labels as $key  => $label) {
                $value = $label ['value'];
                
                if (strlen ($value) > 0) {
                    $formatted [$key] = $label ['value'];
                } // if ()
            } // foreach ()
            
            return $formatted;
        } // labels ()
        
        
        
        
        /**
         *  Show a metabox field.
         */
        protected function _showField ($post_id, $field) {
            if (strlen ($field->key) > 0) {
                switch ($field->type) {
                    case 'textarea':
                        $this->_showTextAreaField ($post_id, $field);
                        break;
                    case 'number':
                        $this->_showNumberField ($post_id, $field);
                        break;
                    case 'select':
                        $this->_showSelectField ($post_id, $field);
                        break;
                    case 'posttype':
                        $this->_showPostTypeField ($post_id, $field);
                        break;
                    case 'taxonomy':
                        $this->_showTaxonomyField ($post_id, $field);
                        break;
                    default:
                        $this->_showTextField ($post_id, $field);
                } // switch ()
            } // if ()
        } // _showField ()
        
        /**
         *  Show a text field.
         */
        protected function _showTextField ($post_id, $field) {
            ?>
                <input type="<?php esc_attr_e ($field->type); ?>" name="<?php esc_attr_e ($field->key); ?>" value="<?php esc_attr_e (get_post_meta ($post_id, $field->key, true)); ?>" class="large-text" />
            <?php
        } // _showTextField ()
        
        /**
         *  Show a textarea field.
         */
        protected function _showTextAreaField ($post_id, $field) {
            $rows = 4;
            
            if (property_exists ($field->settings, $rows) && is_numeric ($field->settings->rows) && $field->settings->rows > 0) {
                $rows = $field->settings->rows;
            } // if ()
            ?>
                <textarea name="<?php esc_attr_e ($field->key); ?>" class="large-text" rows="<?php echo intval ($rows); ?>"><?php echo stripslashes (get_post_meta ($post_id, $field->key, true)); ?></textarea>
            <?php
        } // _showTextAreaField ()
        
        /**
         *  Show a number field.
         */
        public function _showNumberField ($post_id, $field) {
            $atts = array ();
            $att_keys = array ('min', 'max', 'step');
            
            foreach ((array) $field->settings as $key => $val) {
                if (in_array ($key, $att_keys) === true) {
                    $atts [] = $key.'="'.$val.'"';
                } // if ()
            } // foreach ()
            ?>
                <input type="number" name="<?php esc_attr_e ($field->key); ?>" value="<?php esc_attr_e (get_post_meta ($post_id, $field->key, true)); ?>" class="regular-text" <?php echo implode (' ', $atts); ?> />
            <?php
        } // _showNumberField ()
        
        /**
         *  Show a select field.
         */
        protected function _showSelectField ($post_id, $field) {
            $options = explode ("\n", $field->settings->options);
            
            $field_options = array ();
                        
            foreach ($options as $option) {
                $option = explode ('|', $option);
                            
                if (count ($option) == 1) {
                    $option [1] = $option [0];
                } // if ()
                
                $field_options [trim ($option [0])] = trim ($option [1]);
            } // foreach ()
            
            if ($field->settings->selecttype == 'multi') {
                $this->_buildMultiSelect ($field->key, $field_options, get_post_meta ($post_id, $field->key, true));
            } // if ()
            else {
                $this->_buildSelect ($field->key, $field_options, get_post_meta ($post_id, $field->key, true));
            } // else
        } // _showSelectField ()
        
        /**
         *  Show a post type field.
         */
        protected function _showPostTypeField ($post_id, $field) {
            $post_type = $field->settings->posttype;

            if (is_post_type_hierarchical ($post_type)) {
                $options = $this->_getPostTypeOptions ($post_type, true);
            } // if ()
            else {
                $options = $this->_getPostTypeOptions ($post_type, false);
            } // else
            
            if ($field->settings->selecttype == 'multi') {
                $this->_buildMultiSelect ($field->key, $options, get_post_meta ($post_id, $field->key, true));
            } // if ()
            else {
                $this->_buildSelect ($field->key, $options, get_post_meta ($post_id, $field->key, true));
            } // else
        } // _showPostTypeField ()
        
        /**
         *  Show a taxonomy field.
         */
        protected function _showTaxonomyField ($post_id, $field) {
            $tax = $field->settings->taxonomy;

            if (is_taxonomy_hierarchical ($tax)) {
                $options = $this->_getTaxonomyOptions ($tax, true);
            } // if ()
            else {
                $options = $this->_getTaxonomyOptions ($tax, false);
            } // else
            
            if ($field->settings->selecttype == 'multi') {
                $this->_buildMultiSelect ($field->key, $options, get_post_meta ($post_id, $field->key, true));
            } // if ()
            else {
                $this->_buildSelect ($field->key, $options, get_post_meta ($post_id, $field->key, true));
            } // else
        } // _showTaxonomyField ()
        
        
        
        
        /**
         *  Build a standard SELECT field.
         */
        protected function _buildSelect ($name, $options, $value = '') {
            ?>
                <select name="<?php esc_attr_e ($name); ?>">
                    <option value="">&nbsp;</option>
                    <?php
                        foreach ($options as $key => $val) {
                            echo '    <option value="'.esc_attr (trim ($key)).'"'.selected ($value, $key).'>'.$val.'</option>'.PHP_EOL;
                        } // foreach ()
                    ?>
                </select>
            <?php
        } // _buildSelect ()
        
        /**
         *  Build a multiple SELECT field.
         */
        protected function _buildMultiSelect ($name, $options, $values = array ()) {
            if (is_array ($values) === false) {
                $values = array ();
            } // if 
            ?>
                <select name="<?php esc_attr_e ($name); ?>[]" class="widefat" multiple>
                    <?php
                        foreach ($options as $key => $val) {
                            $selected = in_array ($key, $values) ? ' selected="selected"' : '';
                            echo '    <option value="'.esc_attr (trim ($key)).'"'.$selected.'>'.$val.'</option>'.PHP_EOL;
                        } // foreach ()
                    ?>
                </select>
            <?php
        } // _buildMultiSelect ()
        
        
        
        
        /**
         *  Get the options for post types.
         */
        protected function _getPostTypeOptions ($post_type, $hierarchical = false, $parent_id = 0, $indent = 0) {
            $options = array ();
            
            $args = array (
                'numberposts' => -1,
                'post_type' => $post_type,
                'orderby' => 'title',
                'order' => 'ASC'
            );

            if ($hierarchical === true) {
                $args ['orderby'] = 'menu_order title';
                $args ['post_parent'] = $parent_id;
            } // if ()
            
            foreach (get_posts ($args) as $post) {
                $options [$post->ID] = str_repeat ('&nbsp;&nbsp;&nbsp;', $indent).$post->post_title;
                
                if ($hierarchical === true) {
                    $subs = $this->_getPostTypeOptions ($post_type, $hierarchical, $post->ID, $indent + 1);
                    
                    foreach ($subs as $key => $label) {
                        $options [$key] = $label;
                    } // foreach ()
                } // if ()
            } // foreach ()
            
            return $options;
        } // _getPostTypeOptions ()
        
        /**
         *  Get the options for taxonomies.
         */
        protected function _getTaxonomyOptions ($taxonomy, $hierarchical = false, $parent_id = 0, $indent = 0) {
            $options = array ();
            
            $args = array (
                'taxonomy' => $taxonomy,
                'hide_empty' => false
            );

            if ($hierarchical === true) {
                $args ['parent'] = $parent_id;
            } // if ()
            
            foreach (get_terms ($args) as $term) {
                $options [$term->term_id] = str_repeat ('&nbsp;&nbsp;&nbsp;', $indent).$term->name;
                
                if ($hierarchical === true) {
                    $subs = $this->_getTaxonomyOptions ($taxonomy, $hierarchical, $term->term_id, $indent + 1);
                    
                    foreach ($subs as $key => $label) {
                        $options [$key] = $label;
                    } // foreach ()
                } // if ()
            } // foreach ()
            
            return $options;
        } // _getTaxonomyOptions ()
        
    } // class Builder