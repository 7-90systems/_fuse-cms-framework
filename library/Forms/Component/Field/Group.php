<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This class represents a group of fields.
     */
    
    namespace Fuse\Forms\Component\Field;
    
    use Fuse\Forms\Component;
    use Fuse\Forms\Component\Field;
    
    
    class Group extends Field {
        
        /**
         *  @var array The fields held by this group.
         */
        protected $_fields;
        
        /**
         *  @var int The number of columns for this group.
         */
        protected $_columns;
        
        
        
        
        /**
         *  Object constructor.
         *
         *  @param string $name The name of this group.
         *  @param string $label The label for this group.
         *  @param array $fields The fields to set for this group.
         *  @param array $args The arguments for this group. Additional
         *  arguments are:
         *      columns     - Integer value for how many rows to display.
         */
        public function __construct (string $name, string $label, array $fields = array (), array $args = array ()) {
            $columns = 1;
            
            if (array_key_exists ('columns', $args)) {
                $columns = $args ['columns'];
                unset ($args ['columns']);
            } // if ()
            
            parent::__construct ($name, $label, '', $args);
            
            $this->setFields ($fields);
            $this->setColumns ($columns);
        } // __construct ()
        
        
        
        
        /**
         *  Get the values from this group.
         *
         *  return array The values from this group.
         */
        public function getValues () {
            $values = array ();
            
            foreach ($this->_fields as $field) {
                $values = array_merge ($values, $field->getValues ());
            } // foreach ()
            
            return $values;
        } // getValues ()
        
        
        
        /**
         *  Set the value for this field.
         *
         *  @param mixed $value The value to set - not used here.
         *  @param array $values The full list of values to set.
         *
         *  @return Fuse\Form\Component\Field\Group This group object.
         */
        public function setValue ($values) {
            if (is_array ($values)) {
                foreach ($this->_fields as $field) {
                    $value = array_key_exists ($field->name, $values) ? $values [$field->name] : '';
                    
                    $field->setValue ($value, $values);
                } // foreach ()
            } // if ()
            
            return $this;
        } // setValue ()
        
        
        
        
        /**
         *  Add a field to this group.
         *
         *  @param Fuse\Form\Component $field The field to add.
         *
         *  @return Fuse\Form\Component\Field\Group This group object.
         */
        public function addField (Component $field) {
            $this->_fields [] = $field;
            
            return $this;
        } // addField ()
        
        /**
         *  Set the fields.
         *
         *  @param array $fields The fields to add.
         *
         *  @return Fuse\Form\Component\Field\Group This group object.
         */
        public function setFields ($fields) {
            $this->_fields = array ();
            
            foreach ($fields as $field) {
                $this->addField ($field);
            } // foreach ()
            
            return $this;
        } // setFields ()
        
        
        
        
        /**
         *  Set the number of columns for this group.
         *
         *  @param int $columns The number of columns to display
         *
         *  @return Fuse\Form\Component\Field\Group This group object.
         */
        public function setColumns (int $columns) {
            $this->_columns = $columns;
            
            return $this;
        } // setColumns ()
        
        
        
        
        /**
         *  Render this field group.
         *
         *  @param bool $render True to render the field, or false to return the
         *  HTML code.
         *
         *  @return string Returns the groups HTML code.
         */
        public function render ($output = true) {
            ob_start ();
            
            if (array_key_exists ('class', $this->_args) === true) {
                if (is_array ($this->_args ['class']) === true) {
                    $this->_args ['class'][] = 'fuse-field-group columns-'.$this->_columns;
                } // if ()
                else {
                    $this->_args ['class'] = array (
                        $this->_args ['class'],
                        'fuse-field-group columns-'.$this->_columns
                    );
                } // else
            } // if ()
            else {
                $this->_args ['class'] = ' fuse-field-group columns-'.$this->_columns;
            } // else
            ?>
                <div <?php echo fuse_format_attributes ($this->_args, true, true); ?>">
                    <?php foreach ($this->_fields as $field): ?>
                        <div class="fuse-field-group-column">
                            <label for="<?php echo $field->getId (); ?>"><?php echo $field->label; ?></label>
                            <?php
                                echo $field->render ();
                            ?> 
                        </div>
                    <?php endforeach; ?>
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
        
    } // class Group