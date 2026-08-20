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

            /**
             *  Settings saved before the unslashing fix are still in the
             *  database with backslashes on them. Repaired once, here rather
             *  than on activation, because an existing site updates the plugin
             *  without ever deactivating it.
             */
            add_action ('admin_init', array ('\Fuse\Install', 'repairSlashedOptions'));

            /**
             *  The Topics API arrived after this header did, and a site that
             *  has saved the setting never sees a change to its default.
             */
            add_action ('admin_init', array ('\Fuse\Install', 'addBrowsingTopics'));
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
                        /**
                         *  add_menu_page () already gates this screen on
                         *  'manage_options', but the save path is checked again
                         *  here so it can never run off the back of a stray
                         *  POST to this callback.
                         */
                        if (current_user_can ('manage_options') && array_key_exists ('fuseform', $_POST)) {
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
            if (fuse_can_save_post_meta ($post_id) === false) {
                return;
            } // if ()
            
            // Let's see if we have any Fuse form fields.
            if (array_key_exists ('fuseform', $_POST) && is_array ($_POST ['fuseform'])) {
                // Save the values for each form field that we've got.
                foreach ($_POST ['fuseform'] as $field_name => $value) {
                    /**
                     *  The field name becomes part of the meta key, so it has
                     *  to be reduced to a key-safe string. Anything else would
                     *  let a request invent meta keys of its own.
                     */
                    $field_name = sanitize_key ($field_name);
                    
                    if ($field_name === '') {
                        continue;
                    } // if ()
                    
                    $value = fuse_sanitise_meta ($value);
                    
                    update_post_meta ($post_id, 'fuse_form_'.$field_name, $value);
                    
                    do_action ('fuse_form_metabox_'.$field_name.'_save', $value, $post);
                } // forech ()
            } // if ()
        } // saveFuseFormMetaBoxValues ()
        
    } // class Admin