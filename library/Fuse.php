<?php
    /**
     *  Set up our global Fuse object.
     */
    
    namespace Fuse;
    
    use Fuse\Traits\Singleton;
    use Fuse\Layout;
    
    
    class Fuse {
        
        use Singleton;
        
        
        
        
        /**
         *  @var Fuse\Layout The layout for the page.
         */
        public $layout;
        
        
        
        
        /**
         *  Object constructor.
         */
        protected function _init () {
            $this->layout = new Layout ();
        } // _init ()
        
    } // class Fuse