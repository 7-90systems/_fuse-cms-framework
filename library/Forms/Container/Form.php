<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our form container.
     */
    
    namespace Fuse\Forms\Container;
    
    use Fuse\Forms\Container;
    
    
    abstract class Form extends Container {
        
        /**
         *  @var string The form method. This deafaults to 'post'.
         */
        public $method;
        
        /**
         *  @var string the forms action URL. This defaults to an empty string
         *  which will take you to the current URL.
         */
        public $action;
        
        /**
         *  @param string The encoding type for the form. This defaults to
         *  mutlipart form/data so we can upload files if needed.
         */
        public $enctype;
        
        /**
         *  @var string This is the permission level needed to save the forms
         *  values.
         */
        protected $_required_permission;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param array $items The items to go inside this container. This
         *  should be an associative array so that items can be added before or
         *  after by referencing the items ID (key).
         *  @param array $args The arguments for this container. The additional
         *  arguments available are:
         *      method
         *      action
         *      enctype
         *      permission
         */
        public function __construct ($items = array (), $args = array ()) {
            parent::__construct ($items, $args);
            
            $args = array_merge (array (
                'method' => 'post',
                'action' => '',
                'enctype' => 'multipart/form-data',
                'permission' => ''
            ), $args);
            
            foreach ($args as $key => $val) {
                switch ($key) {
                    case 'method':
                        $this->method = $val;
                        break;
                    case 'action':
                        $this->action = $val;
                        break;
                    case 'enctype':
                        $this->enctype = $val;
                        break;
                    case 'permission':
                        $this->_required_permission = $val;
                        break;
                } // switch ()
            } // foreach ()
        } // __construct ()
        
        
        
        
        /**
         *  Get the panels for this form.
         *
         *  return @array An array of form panels.
         */
        abstract public function getPanels ();
        
        
        
        
        /**
         *  Get the HTML code for this container.
         */
        public function render ($output = false) {
            ob_start ();
            ?>
                <form class="fuse-form-container" action="<?php esc_attr_e ($this->action); ?>" method="<?php esc_attr_e ($this->method); ?>" enctype="<?php esc_attr_e ($this->enctype); ?>">
                    <?php
                        $this->setItems ($this->getPanels ());
                        parent::render (true);
                        
                        wp_nonce_field ('fuse-forms', $this->id);
                    ?>
                </form>
            <?php
            $html = ob_get_contents ();
            ob_end_clean ();
            
            if ($output === true) {
                echo $html;
            } // if ()
            else {
                return $html;
            } // else
        } // render ()
        
        
        
        
        /**
         *  This is where we save the forms values.
         *
         *  @param array The values to save into this form.
         *
         *  @return Fuse\Forms\Container\Form This form object.
         */
        public function save ($values) {
            if ((empty ($this->_required_permission) === true || current_user_can ($this->_required_permission)) && wp_verify_nonce ($_REQUEST [$this->id], 'fuse-forms')) {
                foreach ($this->getPanels () as $panel) {
                    foreach ($panel->getFields () as $field) {
                        $value = '';
                        
                        if (array_key_exists ($field->name, $values)) {
                            $value = $values [$field->name];
                        } // if ()
    
                        $field->setValue ($value);
                    } // foreach ()
                    
                    foreach ($panel->getValues () as $key => $value) {
                        $value = '';
                        
                        if (array_key_exists ($key, $values)) {
                            $value = $values [$key];
                        } // if ()
    
                        update_fuse_option ($key, $value);
                    } // foreach ()
                } // foreach ()
                
                
                ?>
                    <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
                        <p><?php _e ('Settings saved', 'fuse'); ?></strong></p>
                        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e ('Dismiss this notice.', 'fuse'); ?></span></button>
                    </div>
                <?php
            } // if ()
            else {
                ?>
                    <div id="fuse-setting-error-settings_updated" class="notice notice-error settings-error is-dismissible">
                        <p><strong><?php _e ('There was an error saving. Please try again.', 'fuse'); ?></strong></p>
                        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e ('Dismiss this notice.', 'fuse'); ?></span></button>
                    </div>
                <?php
            } // else
            
            return $this;
        } // save ()
        
    } // abstract class Form