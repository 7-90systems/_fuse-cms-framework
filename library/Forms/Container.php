<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our main container class.
     */
    
    namespace Fuse\Forms;
    
    use Fuse\Forms\Component;
    
    
    class Container {
        
        const INSERT_BEFORE = 'before';
        const INSERT_AFTER = 'after';
        
        
        
        
        /**
         *  @var string The ID for this container.
         */
        public $id;
        
        /**
         *  @var string The CSS classes for this container.
         */
        public $class;
        
        /**
         *  @var array The items for this container.
         */
        protected $_items;
        
        /**
         *  @var Fuse\Forms\Component\ActionBar The action bar for this form.
         */
        protected $_action_bar;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param array $items The items to go inside this container. This
         *  should be an associative array so that items can be added before or
         *  after by referencing the items ID (key).
         *  @param array $args The arguments for this container. Valid values
         *  are:
         *      id
         *      class
         *      action_bar
         */
        public function __construct ($items = array (), $args = array ()) {
            $this->setItems ($items);
            
            $args = array_merge (array (
                'class' => 'fuse-forms-container'
            ), $args);
            
            foreach ($args as $key => $val) {
                switch ($key) {
                    case 'id':
                        $this->id = $val;
                        break;
                    case 'class':
                        $this->class = $val;
                        break;
                    case 'action_bar':
                        $this->setActionBar ($val);
                        break;
                } // switch ()
            } // foreach ()
        } // __construct ()
        
        
        
        
        /**
         *  Add an item to this container.
         *
         *  @param mixed $item The item to add.
         *  @param string $id The ID of this item.
         *  @param string $related_item_id The ID of the item to insert this
         *  item at. If the ID of this item does not exist the new item will be
         *  added at the end of the list.
         *  @param string $before_after This determines if the new item is added
         *  before or after the related item.
         *
         *  @return Fuse\Forms\Container This container object.
         */
        public function addItem ($item, $id = NULL, $related_item_id = NULL, $before_after = self::INSERT_AFTER) {
            if (empty ($related_item_id) === false && array_key_exists ($related_item_id, $this->_items)) {
                $items = array ();
                
                foreach ($this->_items as $tmp_id => $tmp_item) {
                    if ($tmp_id == $related_item_id) {
                        if ($before_after == self::INSERT_BEFORE) {
                            $items [$tmp_id] = $tmp_item;
                        } // if ()
                        
                        if (empty ($id)) {
                            $items [] = $item;
                        } // if ()
                        else {
                            $items [$id] = $item;
                        } // else
                        
                        if ($before_after == self::INSERT_AFTER) {
                            $items [$tmp_id] = $tmp_item;
                        } // if ()
                    } // if ()
                } // foreach ()
                
                $this->_items = $items;
            } // if (
            else {
                // Insert at the end of the items list
                if (empty ($id)) {
                    $this->_items [] = $item;
                } // if ()
                else {
                    $this->_items [$id] = $item;
                } // else
            } // else
            
            return $this;
        } // addItem ()
        
        /**
         *  Remove an item from the list.
         *
         *  @param string $id The ID of the item to remove.
         *
         *  @return Fuse\Forms\Container This container object.
         */
        public function removeItem ($id) {
            if (array_key_exists ($id, $this->_items)) {
                $items = array ();
                
                foreach ($this->_items as $tmp_id => $tmp_item) {
                    if ($tmp_id != $id) {
                        $items [$tmp_id] = $tmp_item;
                    } // if ()
                } // foreach ()
                
                $this->_items = $items;
            } // if ()
            
            return $this;
        } // removeItem ()
        
        /**
         *  Set the items in this container. This will clear any existing items.
         *
         *  @param array $items The items to set.
         *
         *  @return Fuse\Forms\Container This container object.
         */
        public function setItems ($items) {
            $this->_items = array ();
            
            foreach ($items as $key => $item) {
                $this->addItem ($item, $key);
            } // foreach ()
            
            return $this;
        } // setItems ()
        
        
        
        
        /**
         *  Get the action bar set for this container.
         *
         *  @return Fuse\Forms\Component\ActionBar|NULL The action bar or NULL
         *  if no action bar is set.
         */
        public function getActionBar () {
            return $this->_action_bar;
        } // getActionBar ()
        
        /**
         *  Set the action bar for this container.
         *
         *  @param Fuse\Form\Component\ActionBar $action_bar The action bar.
         *
         *  @return Fuse\Forms\Container This container object.
         */
        public function setActionBar (Component\ActionBar $action_bar) {
            $this->_action_bar = $action_bar;
            
            return $this;
        } // setActionBar ()
        
        
        
        
        /**
         *  Get the HTML code for this container.
         */
        public function render ($output = false) {
            $tabbed = count ($this->_items) > 1 ? true : false;
            
            ob_start ();
            ?>
                <div id="<?php echo esc_attr ($this->id); ?>" class="<?php echo esc_attr ($this->class); ?><?php echo $tabbed === true ? ' tabbed' : ' not-tabbed'; ?>">
                    <div class="fuse-forms-container-inner">
                        
                        <?php if ($tabbed === true): ?>
                            <ul class="fuse-form-panel-tabs">
                                <?php foreach ($this->_items as $item): ?>
                                    <li>
                                        <a href="#fuse-form-panel-<?php echo esc_attr ($item->id); ?>"><?php echo wp_kses_post ($item->label); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <?php
                            foreach ($this->_items as $item) {
                                $item->render (true);
                            } // foreach ()
                        ?>
                        
                    </div>
                </div>
                
                <?php
                    if (empty ($this->_action_bar) === false) {
                        echo $this->_action_bar->render (true);
                    } // if ()
                ?>
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
        
    } // class Container