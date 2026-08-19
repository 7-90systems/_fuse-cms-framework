<?php
    /**
     *  @package fusecms
     *
     *  This class sets up our theme fragments functionality.
     *
     *  Theme fragments allow us to use an AJAX request to update HTML blocks
     *  on our website after a page has loaded. This is mainly intended to be
     *  used by sites that have aggressive caching enabled but still need to
     *  have some areas on their pages be dynamic.
     *
     *  Fragments are loaded using an element ID that related to a HTML code
     *  block. That elements content will be replaced with the HTML code in the
     *  response.
     *
     *  @filter fuse_theme_fragments
     */
    
    namespace Fuse\Setup\Theme;
    
    
    class Fragments {
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            /**
             *  Add our JavaSsript code into the footer. We add this as late as
             *  possible so we can ensure that it gets executed after everything
             *  else on the page has been set up.
             */
            add_action ('wp_footer', array ($this, 'footerScripts'), PHP_INT_MAX);
            
            // Listen for our AJAX calls
            add_action ('wp_ajax_fuse_html_fragments', array ($this, 'sendFragments'));
            add_action ('wp_ajax_nopriv_fuse_html_fragments', array ($this, 'sendFragments'));
        } // __construct ()
        
        
        
        
        /**
         *  Add our JavaScript to the page footer.
         */
        public function footerScripts () {
            ?>
                <!-- <?php _e ('Start Fuse CMS HTML fragments', 'fuse'); ?> -->
                <script id="fuse-html-fragments" type="text/javascript">
                    function fuseHtmlFragments (section = 'default') {
                        jQuery.ajax ({
                            url: '<?php echo esc_url (admin_url ('admin-ajax.php')); ?>',
                            type: 'post',
                            data: {
                                action: 'fuse_html_fragments',
                                section: section
                            },
                            dataType: 'json',
                            success: function (response) {
                                for (var i in response) {
                                    jQuery ('#' + i).html (response [i]);
                                } // for ()
                                
                                jQuery ('body').trigger ('fuse_theme_fragments_load', [section]);
                            }
                        });
                    } // fuseHtmlFragments ()
                    
                    // Get all of our fragments!
                    fuseHtmlFragments ();
                </script>
                <!-- <?php _e ('End Fuse CMS HTML fragments', 'fuse'); ?> -->
            <?php
        } // footerScripts ()
        
        
        
        
        /**
         *  Send the HTML fragments to the system.
         *
         *  Fragments are an associative array with the key being the ID of the
         *  associated element, and the value being the inner HTML content for
         *  that element. eg:
         *      $fragments ['fuse-test-fragment'] = 'This is a <strong>test</strong> HTML fragment!';
         *
         *  This will replace the inner content for this element:
         *      <div id="fuse-test-fragment">Content before replacement.</div>
         *
         *  Developers can use the 'section' value to determine what fragments
         *  they wish to add. The standard used is 'default' which is the
         *  standard for a page load, and can be changed to any other value
         *  to load other fragment sections.
         */
        public function sendFragments () {
            $section = 'default';
            
            if (array_key_exists ('section', $_POST)) {
                /**
                 *  This is passed straight on to whatever is hooked to the
                 *  filter below, so it is reduced to a plain key here rather
                 *  than handed on as arbitrary request input.
                 */
                $section = sanitize_key ($_POST ['section']);
            } // if ()
            
            if ($section === '') {
                $section = 'default';
            } // if ()
            
            $fragments = apply_filters ('fuse_theme_fragments', array (), $section);
            
            wp_send_json ($fragments);
        } // sendFragments ()
        
    } // class Fragments