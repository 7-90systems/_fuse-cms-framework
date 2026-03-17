<?php
    /**
     *  @package fusecms
     *
     *  This class performs our base administration tasks and sets up the admin
     *  pages for the system.
     *
     *  @action fuse_admin_menu
     *  @action fuse_form_metabox_*FIELD_NAME*_save
     */
    
    namespace Fuse;
    
    
    class Admin {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            // Set up our administration menu.
            add_action ('admin_menu', array ($this, 'adminMenu'), 9);
            
            // Set up our theme/plugins updater set up.
            $update = Update::getInstance ();
            
            // Add in our functionality to save the Fuse Form values for post types
            add_action ('save_post', array ($this, 'saveFuseFormMetaBoxValues'), 10, 2);
        } // __construct ()
        
        
        
        
        /**
         *  Set up our admin menu items.
         */
        public function adminMenu () {
            // Set up our main site settings page.
            add_menu_page (__ ('Fuse CMS Site Settings', 'fuse'), __ ('Fuse CMS', 'fuse'), 'manage_options', 'fusesettings', array ($this, 'sitesettings'), 'dashicons-fusecms');
            
            do_action ('fuse_admin_menu');
        } // adminMenu ()
        
        
        
        
        /**
         *  Set up the Fuse site settings page.
         */
        public function siteSettings () {
            $form = new \Fuse\Forms\Form\Settings ();
            ?>
                <div class="wrap">
                    
                    <h1><?php _e ('Site Settings', 'fuse'); ?></h1>
                    
                    <?php
                        if (count ($_POST) > 0) {
                            $form->save ($_POST ['fuseform']);
                        } // if ()
                        
                       $form->render (true);
                    ?>
                    
                </div>
            
            <?php
        } // siteSettings ()
        
        
        
        
        /**
         *  Save the values from any Fuse Forms that are set up for the post
         *  type.
         *
         *  @param int $post_id The ID of the post object.
         *  @param WP_Post $post The post object.
         */
        public function saveFuseFormMetaBoxValues ($post_id, $post) {
            // Don't update on autosave.
            if (defined ('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            } // if ()
            else {
                // Let's see if we have any Fuse form fields.
                if (array_key_exists ('fuseform', $_POST) && is_array ($_POST ['fuseform'])) {
                    // Save the values for each form field that we've got.
                    foreach ($_POST ['fuseform'] as $field_name => $value) {
                        update_post_meta ($post_id, 'fuse_form_'.$field_name, $value);
                        
                        do_action ('fuse_form_metabox_'.$field_name.'_save', $value, $post);
                    } // forech ()
                } // if ()
            } // else
        } // saveFuseFormMetaBoxValues ()
        
    } // class Admin