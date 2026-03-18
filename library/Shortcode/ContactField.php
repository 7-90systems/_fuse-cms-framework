<?php
    /**
     *  @package fusecms
     *
     *  Set up our content_block shortcode.
     */
    
    namespace Fuse\Shortcode;
    
    use Fuse\Shortcode;
    
    
    class ContactField extends Shortcode {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            parent::__construct ('contact_field', 'contact-field', array (
                'field' => '',
                'location' => 'default',
                'link_text' => '',
                'url' => false,
                'phone' => false,
                'email' => false
            ));
        } // __construct ()
        
    } // class ContactField