<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our form panel class. Panels contain the form fields.
     */
    
    namespace Fuse\Forms\Component;
    
    use Fuse\Forms\Container;
    use Fuse\Forms\Component;
    
    
    class Panel extends Component {
        
        /**
         *  @var string The ID of this panel.
         */
        public $id;
        
        /**
         *  @var string The label for this panel.
         */
        public $label;
        
        /**
         *  @var array The fields for this panel.
         */
        protected $_fields;
        
        /**
         *  @var array The arguments for this panel.
         */
        protected $_args;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $id The panel ID.
         *  @param string $label The panel label.
         *  @param array $fields The fields for this panel.
         *  @param array $args The arguments for this panel. Valid values are:
         *      label_position          top, left, hidden
         */
        public function __construct ($id, $label, $fields = array (), $args = array ()) {
            $args = array_merge (array (
                'label_position' => 'top'
            ), $args);
            
            $this->id = $id;
            $this->label = $label;
            $this->setFields ($fields);
            
            parent::__construct ($args);
        } // __construct ()
        
        
        
        
        /**
         *  Get the values from the panel.
         *
         *  @return array The panels values.
         */
        public function getValues () {
            $values = array ();
            
            foreach ($this->_fields as $field) {
                if (empty ($field) === false) {
                    $values = array_merge ($values, $field->getValues ());
                } // if ()
            } // foreach ()
            
            return $values;
        } // getValues ()
        
        
        
        
        /**
         *  Add an field to this panel.
         *
         *  @param \Fuse\Forms\Component\Field $field The field to add.
         *  @param string $id The ID of this item.
         *  @param string $related_item_id The ID of the item to insert this
         *  item at. If the ID of this item does not exist the new item will be
         *  added at the end of the list.
         *  @param string $before_after This determines if the new item is added
         *  before or after the related item.
         *
         *  @return Fuse\Forms\Component\Panel This panel object.
         */
        public function addField ($item, $id, $related_item_id = NULL, $before_after = Container::INSERT_AFTER) {
            if (empty ($related_item_id) === false && array_key_exists ($related_item_id, $this->_items)) {
                $fields = array ();
                
                foreach ($this->_items as $tmp_id => $tmp_item) {
                    if ($tmp_id == $related_item_id) {
                        if ($before_after == Container::INSERT_BEFORE) {
                            $fields [$tmp_id] = $tmp_item;
                        } // if ()
                        
                        $fields [$id] = $item;
                        
                        if ($before_after == Container::INSERT_AFTER) {
                            $fields [$tmp_id] = $tmp_item;
                        } // if ()
                    } // if ()
                } // foreach ()
                
                $this->_fields = $items;
            } // if (
            else {
                // Insert at the end of the items list
                $this->_fields [$id] = $item;
            } // else
            
            return $this;
        } // addField ()
        
        /**
         *  Remove a field from the list.
         *
         *  @param string $id The ID of the field to remove.
         *
         *  @return Fuse\Forms\Component\Panel This panel object.
         */
        public function removeField ($id) {
            if (array_key_exists ($id, $this->_fields)) {
                $fields = array ();
                
                foreach ($this->_fields as $tmp_id => $tmp_item) {
                    if ($tmp_id != $id) {
                        $fields [$tmp_id] = $tmp_item;
                    } // if ()
                } // foreach ()
                
                $this->_fields = $fields;
            } // if ()
            
            return $this;
        } // removeField ()
        
        /**
         *  Set the items in this container. This will clear any existing items.
         *
         *  @param array $items The items to set.
         *
         *  @return Fuse\Forms\Component\Panel This panel object.
         */
        public function setFields ($fields) {
            $this->_fields = array ();
            
            foreach ($fields as $key => $field) {
                $this->addField ($field, $key);
            } // foreach ()
            
            return $this;
        } // setFields ()
        
        /**
         *  Get the fields.
         *
         *  @return array The fields for this panel.
         */
        public function getFields () {
            return $this->_fields;
        } // getFields ()
        
        
        
        
        /**
         *  Render the panel.
         *
         *  @param bool $output True to output of false to return the HTML code.
         */
        public function render ($output = false) {
            $conditions = array_key_exists ('data-conditions', $this->_args) ? $this->_args ['data-conditions'] : '';
            
            ob_start ();
            ?>
                <div id="fuse-form-panel-<?php esc_attr_e ($this->id); ?>" class="fuse-forms-panel label-position-<?php esc_attr_e ($this->_args ['label_position']); ?>" data-conditions="<?php esc_attr_e ($conditions); ?>">
                    <div class="fuse-forms-panel-inner">
                        
                        <?php if (empty ($this->label) === false): ?>
                            <h2 class="fuse-forms-container-title"><?php echo $this->label; ?></h2>
                        <?php endif; ?>
                        
                        <?php
                            foreach ($this->_fields as $field) {
                                if (empty ($field) === false) {
                                    ?>
                                        <div class="fuse-forms-panel-field-container">
                                            <label for="<?php esc_attr_e ($field->getId ()); ?>"><?php echo $field->label; ?></label>
                                            <div class="fuse-form-panel-field-block">
                                                <?php
                                                    $field->render (true);
                                                ?>
                                            </div>
                                            <div class="fuse-form-panel-field-notice"></div>
                                            <?php if (strlen ($field->description) > 0): ?>
                                                <p class="fuse-field-description"><?php echo $field->description; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php
                                } // if ()
                            } // foreach ()
                        ?>
                        
                    </div>
                </div>
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
        
    } // class Panel