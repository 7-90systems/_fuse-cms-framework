<?php
    /**
     *  Set up our tabs block.
     */
    
    namespace Fuse\Block;
    
    use Fuse\Block;
    
    
    class Tabs extends Block {
        
        /**
         *  Register our block
         */
        public function registerBlock () {
            wp_register_script (
                'fuse-tab-block',
                FUSE_BASE_URL.'/blocks/tabs/block.js',
                [
                    'wp-blocks',
                    'wp-element',
                    'wp-editor',
                    'wp-components',
                    'wp-i18n'
                ]
            );
        
            /**
             *  There is no 'stb-style' handle registered anywhere -- it came in
             *  with the block this one was based on. The block's styles are
             *  enqueued by enqueueScriptsAndStyles () below.
             */
            register_block_type( 'fuse/tabs', [
                'editor_script' => 'fuse-tab-block',
                'render_callback' => array ($this, 'renderTabs')
            ] );
            
            register_block_type( 'fuse/tab', [
                'editor_script' => 'fuse-tab-block'
            ] );
        } // registerBlock ()
        
        /**
         *  Render the tabs container content.
         */
        public function renderTabs ($attributes, $content) {
            ob_start ();
            
            include ($this->_getTemplateFileUri ('tabs/render-tabs.php'));
            
            return ob_get_clean ();
        } // render ()
        
        
        
        
        /**
         *  Enqueue our front-end scripts and stylesehets.
         */
        public function enqueueScriptsAndStyles () {
            wp_enqueue_script ('fuse-block-tabs', FUSE_BASE_URL.'/assets/javascript/tabs.js', array ('jquery'));
            wp_enqueue_style ('fuse-block-tabs', FUSE_BASE_URL.'/assets/css/tabs.css');
        } // enqueueScriptsAndStyles ()

    } // class Tabs