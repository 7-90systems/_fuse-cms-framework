<?php
    /**
     *  @package fuse-cms-framework
     *
     *  This class is used to display a list of post types related to the current post type.
     */
    
    namespace Fuse\Admin\Table;
    
    
    class PostType {
        
        /**
         *  @var string This is the lost type that we are listing.
         */
        protected $_post_type;
        
        /**
         *  @var string This is tha nme of the field that we use to store the values.
         */
        protected $_field_name;
        
        /**
         *  @var array These are the ID's of the current post types set. These must be in order.
         */
        protected $_current_items;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $post_type The post type that we are choosing from.
         *  @param string $field_name The name of the field that we use to store the values.
         *  @param array $current_items The ID's of the current items to display.
         */
        public function __construct ($post_type, $field_name, $current_items = array ()) {
            $this->_post_type = $post_type;
            $this->_field_name = $field_name;
            $this->_current_items = $current_items;
        } // __construct ()
        
        
        
        
        /**
         *  Render our table and additional code.
         *
         *  @param array $current_items If set thsi will override the items set for this class.
         *  @param bool $return True to return the HTML code or false to output the code. Defaults to false.
         *
         *  @return string|NULL Returns the HTML code or NULL.
         */
        public function render ($current_items = array (), $return = false) {
            if (empty ($current_items) === true) {
                $current_items = $this->_current_items;
            } // if ()
            
            $post_type = get_post_type_object ($this->_post_type);
            $items = $this->_getPostTypeList ();
            
            ob_start ();
            ?>
                <div class="fuse-post-type-table-container">
                    
                    <table class="widefat fuse-post-type-table">
                        
                        <thead>
                            <tr>
                                <th class="fuse-post-type-table-column-item"><?php echo $post_type->labels->singular_name; ?></th>
                                <th class="fuse-post-type-table-column-delete">&nbsp;</th>
                            </tr>
                        </thead>
                        
                        <tfoot>
                            <tr>
                                <th class="fuse-post-type-table-column-item"><?php echo $post_type->labels->singular_name; ?></th>
                                <th class="fuse-post-type-table-column-delete">&nbsp;</th>
                            </tr>
                        </tfoot>
                        
                        <tbody>
                            
                            <?php if (count ($current_items) > 0): ?>
                            
                                <?php foreach ($current_items as $item): ?>
                                    <?php
                                        $item = get_post ($item);
                                    ?>
                                
                                    <tr class="fuse-post-type-row-item" data-id="<?php echo $item->ID; ?>">
                                        <td class="fuse-post-type-table-column-item">
                                            <a href="<?php echo esc_url (admin_url ('post='.$item->ID.'&action=edit')); ?>"><?php echo $item->post_title; ?></a>
                                        </td>
                                        <td class="fuse-post-type-table-column-delete">
                                            <span class="dashicons dashicons-dismiss"></span>
                                            <span class="screen-reader-text"><?php _e ('Delete', 'mrg'); ?></span>
                                        </td>
                                    </tr>
                                
                                <?php endforeach; ?>
                                
                            <?php endif; ?>
                            
                            <tr class="fuse-post-type-row-empty"<?php if (count($current_items) > 0) echo ' style="display: none;"'; ?>>
                                <th class="fuse-post-type-table-column-empty" colspan="2"><?php printf (__ ('No %s available', 'mrg'), strtolower ($post_type->labels->name)); ?></th>
                            </tr>
                                
                        </tbody>
                        
                        <template>
                            <tr class="fuse-post-type-row-item" data-id="%%ID%%">
                                    <td class="fuse-post-type-table-column-item">
                                        <a href="<?php echo esc_url (admin_url ('post=%%ID%%&action=edit')); ?>">%%TITLE%%</a>
                                    </td>
                                    <td class="fuse-post-type-table-column-delete">
                                        <span class="dashicons dashicons-dismiss"></span>
                                        <span class="screen-reader-text"><?php _e ('Delete', 'mrg'); ?></span>
                                    </td>
                                </tr>
                        </template>
                        
                    </table>
                    
                    <input type="hidden" name="<?php esc_attr_e ($this->_field_name); ?>" value="<?php echo implode (',', $current_items); ?>" class="fuse-post-type-table-ids" />
                    
                    <p class="fuse-post-type-table-add">
                        <select name="fuse-post-type-add-select">
                            <option value="">&nbsp;</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo $item->ID; ?>"<?php if (in_array ($item->ID, $current_items)) echo ' disabled="disabled"'; ?>><?php echo $item->post_title; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="button fuse-post-type-table-add-button">
                            <?php printf (__ ('Add a new %s', 'fuse'), $post_type->labels->singular_name); ?>
                        </button>
                    </p>
                    
                </div>
            <?php
            $html = ob_get_contents ();
            ob_end_clean ();
            
            if ($return === true) {
                return $html;
            } // if ()
            else {
                echo $html;
            } // else
        } // render ()
        
        
        
        
        /**
         *  Get the list of post types.
         *
         *  @return array An array of post objects.
         */
        protected function _getPostTypeList () {
            global $wpdb;
            
            $query = $wpdb->prepare ("SELECT
                ID,
                post_title
            FROM ".$wpdb->posts."
            WHERE post_status = 'publish'
                AND post_type = %s
            ORDER BY post_title ASC, ID ASC", $this->_post_type);
            
            return $wpdb->get_results ($query);
        } // _getPostTypeList ()
        
    } // class PostType