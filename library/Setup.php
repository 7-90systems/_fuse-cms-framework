<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our set up class.
     *
     *  @filter fuse_load_functions_from Set the folder locations to load
     *  function files from.
     *  @filter fuse_slider_template_locations Set the folder locations for
     *  slider templates.
     *
     *  @action fuse_before_load_functions Run before function files are loaded.
     *  @action fuse_efter_load_functions Run after function files are loaded.
     *  @action fuse_init Run after the Fuse system is initialised and is ready.
     *  @action fuse_register_posttypes Run when ready to register post types.
     */
    
    namespace Fuse;
    
    use Fuse\Traits\Singleton;
    
    
    class Setup {
        
        use Singleton;
        
        
        
        
        /**
         *  Set up our class.
         */
        private function _init () {
            /**
             *  Load our functions
             */
            add_action ('after_setup_theme', array ($this, 'loadFunctions'));
            
            // Load our function files
            add_action ('after_setup_theme', array ($this, 'loadExtraFunctions'), 11);
            /**
             *  Load our post types
             */
            add_action ('after_setup_theme', array ($this, 'loadPostTypes'), 11);
            
            /**
             *  Set up our various additions.
             */
            $setup_theme = new Setup\Theme ();
            
            /**
             *  Set the email sender details for the site.
             */
            $email_sender = new Setup\EmailSender ();
            
            // Enable optional editor blocks
            add_action ('after_setup_theme', array ($this, 'enableOptionalEditorBlocks'), 11);
            
            if (is_admin ()) {
                $admin = new Admin ();
            } // if ()
            
            // Set up the Google API link in the header when the API key is set.
            add_action ('wp_head', array ($this, 'googleApiKeyLink'));
            add_action ('admin_head', array ($this, 'googleApiKeyLink'));
            
            /**
             *  When we are finished we can call the action related to Fuse.
             */
            do_action ('fuse_init');
        } // _init ()
        
        
        
        
        /**
         *  Load our standard functions by including the files in the
         *  'functions' folder.
         */
        public function loadFunctions () {
            do_action ('fuse_before_load_functions');
            
            // Load core Fuse function files
            $functions_dirs = apply_filters ('fuse_load_functions_from', array (
                FUSE_BASE_URI.DIRECTORY_SEPARATOR.'functions'
            ));
                       
            foreach ($functions_dirs as $dir) {
                $files = glob (trailingslashit ($dir).'*.php');
                
                foreach ($files as $file) {
                    if (basename ($file) != 'index.php') {
                        require_once ($file);
                    } // if ()
                } // foreach ()
            } // foreach ()
            
            do_action ('fuse_after_load_actions');
        } // loadFunctions ()
        
        
        
        
        /**
         *  Load our extra functionality.
         */
        public function loadExtraFunctions () {
            if (get_fuse_option ('pagetype_builder') == 'yes') {
                $posttype_builder = new PostType\Builder ();
                $setup_builder = Setup\PostType\Builder::getInstance ();
            } // if ()
        } // loadExtraFunctions ()
        
        
        
        
        /**
         *  Load ourpost types.
         */
        public function loadPostTypes () {
            $posttype_layout = new PostType\Layout ();
            
            // FAQs
            if (get_fuse_option ('faq_posttype', false) == 'yes') {
                $posttype_faqs = new PostType\FAQ ();
            } // if ()
            
            // Sliders
            if (get_fuse_option ('sliders_posttype', false) == 'yes') {
                
                
                $posttype_slider = new PostType\Slider ();
                $posttype_slider_slide = new PostType\Slider\Slide ();
                
                // Add our base slider locations
                add_filter ('fuse_slider_template_locations', array ($this, 'setSliderTemplateFolders'), 1);
            } // if ()
            
            do_action ('fuse_register_posttypes');
        } // loadPostTypes ()
        
        /**
         *  Enable our additional editor blocks if required.
         */
        public function enableOptionalEditorBlocks () {
            // Tabs
            if (get_fuse_option ('tabs_block', false) == 'yes') {
                $block_tabs = new Block\Tabs ();
            } // if ()
            
            // FAQs
            if (get_fuse_option ('faq_posttype', false) == 'yes') {
                $block_faqs = new Block\FAQs ();
            } // if ()
            
            // Sliders
            if (get_fuse_option ('sliders_posttype', false) == 'yes') {
                $block_sliders = new Block\Slider ();
            } // if ()
        } // enableOptionalEditorBlocks ()
        
        
        
        
        /**
         *  Add the Google API key to the header area.
         */
        public function googleApiKeyLink () {
            $api_key = get_fuse_option ('google_api_key', '');
            
            if (strlen ($api_key) > 0) {
                ?>
<script>
    (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
    ({key: "<?php esc_attr_e ($api_key); ?>", v: "weekly"});
</script>
                <?php
            } // if ()
        } // googleApiKeyLink ()
        
        
        
        
        /**
         *  Set up the slider template folders.
         *
         *  @param array $folders the folders to add in.
         *
         *  @return array The folder structure.
         */
        public function setSliderTemplateFolders ($folders) {
            if (is_array ($folders) === false) {
                $folders = array ();
            } // if ()
            
            $folders = array_merge (array (
                get_stylesheet_directory ().DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'slider'.DIRECTORY_SEPARATOR => get_stylesheet_directory_uri ().'/templates/slider/',
                get_template_directory ().DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'slider'.DIRECTORY_SEPARATOR => get_template_directory_uri ().'/templates/slider/',
                FUSE_BASE_URI.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'slider'.DIRECTORY_SEPARATOR => FUSE_BASE_URL.'/templates/slider/'
            ), $folders);
            
            return $folders;
        } // setSliderTemplateFolders ()
        
    } // class Setup