<?php
    /**
     *  @package fusecms
     *
     *  Set up the select control.
     */
    
    namespace Fuse\Block\Control;
    
    use Fuse\Block\Control;
    use Fuse\Block\Control\Setting;
    
    
    class Select extends Control {
    
        /**
         * Select constructor.
         *
         * @return void
         */
        public function __construct() {
            parent::__construct ('select', __ ('Select', 'fuse'));
        } // __construct ()
    
    
    
    
        /**
         * Register settings.
         *
         * @return void
         */
        protected function _registerSettings () {
            $this->settings [] = new Setting ($this->settings_config ['location']);
            $this->settings [] = new Setting ($this->settings_config ['width']);
            $this->settings [] = new Setting ($this->settings_config ['help']);
            $this->settings [] = new Setting (array (
                'name' => 'options',
                'label' => __ ('Choices', 'fuse'),
                'type' => 'textarea_array',
                'default' => '',
                'help' => sprintf (
                    '%s %s<br />%s<br />%s',
                    __ ('Enter each choice on a new line.', 'fuse'),
                    __ ('To specify the value and label separately, use this format:', 'fuse'),
                    _x ('foo : Foo', 'Format for the menu values. option_value : Option Name', 'fuse'),
                    _x ('bar : Bar', 'Format for the menu values. option_value : Option Name', 'fuse')
                ),
                'sanitise' => array ($this, 'sanitizeTextareaAssocArray')
            ));
            $this->settings [] = new Setting (array (
                'name' => 'default',
                'label' => __ ('Default Value', 'fuse'),
                'type' => 'text',
                'default' => '',
                'sanitise' => 'sanitizeTextField',
                'validate' => array ($this, 'validateOptions')
            ));
        } // _registerSettings ()
        
    } // class Select