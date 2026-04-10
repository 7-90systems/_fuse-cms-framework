<?php
    /**
     *  This class contains our settings for the GSAP animation settings.
     */
    
    namespace Fuse\Settings\Form;
    
    use Fuse\Settings;
    
    
    class GSAP extends Settings {
        
        
        
        
        protected $_modules;
        
        
        
        
        /**
         *  Initialise our settings values.
         */
        protected function _initSettings () {
            $this->_modules = array (
                'CSSRulePlugin' => __ ('CSS Rule Plugin', 'fuse'),
                'CustomBounce' => __ ('Custom Bounce', 'fuse'),
                'CustomEase' => __ ('Custom Ease', 'fuse'),
                'CustomWiggle' => __ ('Custom Wiggle', 'fuse'),
                'Draggable' => __ ('Draggable', 'fuse'),
                'DrawSVGPlugin' => __ ('Draw SVG Plugin', 'fuse'),
                'EaselPlugin' => __ ('Easel Plugin', 'fuse'),
                'EasePack' => __ ('Ease Pack', 'fuse'),
                'Flip' => __ ('Flip', 'fuse'),
                'GSDevTools' => __ ('GS Dev Tools', 'fuse'),
                'InertiaPlugin' => __ ('Inertia Plugin', 'fuse'),
                'MorphSVGPlugin' => __ ('Morh SVG Plugin', 'fuse'),
                'MotionPathHelper' => __ ('MotionPathHelper', 'fuse'),
                'MotionPathPlugin' => __ ('MotionPathPlugin', 'fuse'),
                'Observer' => __ ('Observer', 'fuse'),
                'Physics2DPlugin' => __ ('Physics 2D Plugin', 'fuse'),
                'PhysicsPropPlugin' => __ ('Physics Prop Plugin', 'fuse'),
                'PixiPlugin' => __ ('Pixi Plugin', 'fuse'),
                'ScrambleTextPlugin' => __ ('Scramble Text Plugin', 'fuse'),
                'ScrollSmoother' => __ ('Scroll Smoother', 'fuse'),
                'ScrollToPlugin' => __ ('Scroll To Plugin', 'fuse'),
                'ScrollTrigger' => __ ('Scroll Trigger', 'fuse'),
                'SplitText' => __ ('Split Text', 'fuse'),
                'TextPlugin' => __ ('Text Plugin', 'fuse')
            );
        } // _initSettings ()
        
    } // class GSAP