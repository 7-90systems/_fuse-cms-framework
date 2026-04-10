<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is our main settings form.
     *
     *  @filter fuse_settings_form_panels
     *  @filter fuse_settings_form_email_sender_fields
     *  @filter fuse_settings_form_google_api_fields
     *  @filter fuse_settings_form_submit_text
     *  @fitler fuse_contact_locations
     *  @filter fuse_contact_location_fields
     *  @filter fuse_contact_social_media_links
     */
    
    namespace Fuse\Forms\Form;
    
    use Fuse\Forms\Container\Form;
    use Fuse\Forms\Component;
    
    
    
    class Settings extends Form {
        
        protected $_gsap_modules;
        
        
        
        
        /**
         *  Object constructor.
         */
        public function __construct () {
            $gsap_modules = array ();
            
            $theme_style_options = array (
                    new Component\Field\Toggle ('theme_css_layout', __ ('Enable layout CSS styles'), get_fuse_option ('theme_css_layout', false)),
                    new Component\Field\Toggle ('theme_css_buttons', __ ('Enable button CSS styles'), get_fuse_option ('theme_css_buttons', false)),
                    new Component\Field\Toggle ('theme_css_block', __ ('Disable Gutenberg block editor stylesheets'), get_fuse_option ('theme_css_block', false))
            );
            
            if (function_exists ('WC')) {
                $theme_style_options [] = new Component\Field\Toggle ('theme_css_woo', __ ('Disable WooCommerce stylesheets'), get_fuse_option ('theme_css_woo', false));
            } // if ()
            
            $panels = apply_filters ('fuse_settings_form_panels', array (
                $this->_getContactPanel (),
                new Component\Panel ('email_sender', __ ('Email Sender', 'fuse'), apply_filters ('fuse_settings_form_email_sender_fields', array (
                    new Component\Field\Text ('fuse_email_from_name', __ ('Send from name', 'fuse'), get_fuse_option ('fuse_email_from_name', get_bloginfo ('name')), array (
                        'placeholder' => 'Enter the senders name here',
                        'required' => true
                    )),
                    new Component\Field\Email ('fuse_email_from_email', __ ('Send from email', 'fuse'), get_fuse_option ('fuse_email_from_email', ''), array (
                        'placeholder' => 'Enter the senders email address here',
                        'required' => true,
                        'class' => 'full'
                    ))
                ))),
                new Component\Panel ('theme_css', __ ('Theme CSS Styles', 'fuse'), apply_filters ('fuse_settings_form_theme_css_fields', $theme_style_options)),
                new Component\Panel ('theme_features', __ ('Theme Features', 'fuse'), apply_filters ('fuse_settings_form_theme_features_fields', array (
                    new Component\Field\Toggle ('faq_posttype', __ ('Enable FAQ post type', 'fuse'), get_fuse_option ('faq_posttype', false)),
                    new Component\Field\Toggle ('sliders_posttype', __ ('Enable Slider post type', 'fuse'), get_fuse_option ('sliders_posttype', false)),
                    new Component\Field\Toggle ('tabs_block', __ ('Enable Tabs editor block', 'fuse'), get_fuse_option ('tabs_block', false)),
                    new Component\Field\Toggle ('html_fragments', __ ('Enable HTML Fragments', 'fuse'), get_fuse_option ('html_fragments', false)),
                    new Component\Field\Toggle ('web_fonts', __ ('Auto-load web fonts', 'fuse'), get_fuse_option ('web_fonts', false)),
                    new Component\Field\Toggle ('term_images', __ ('Featured images for terms', 'fuse'), get_fuse_option ('term_images', false)),
                    $this->_getImageTerms (),
                    new Component\Field\Toggle ('gsap', __ ('Add GSAP animations', 'fuse'), get_fuse_option ('gsap', false)),
                    $this->_getGSAPModules (),
                    new Component\Field\Image ('fallback_image', __ ('Fallback image', 'fuse'), get_fuse_option ('fallback_image', 0))
                ))),
                new Component\Panel ('development_features', __ ('Development Features', 'fuse'), apply_filters ('fuse_settings_form_development_features_fields', array (
                    new Component\Field\Toggle ('pagetype_builder', __ ('Enable Page Type Builder', 'fuse'), get_fuse_option ('pagetype_builder', false))
                ))),
                new Component\Panel ('header_footer_scripts', __ ('Header &amp; Footer Scripts', 'fuse'), array (
                    new Component\Field\TextArea ('header_scripts', __ ('Code to be added inside the &lt;head&gt; tag', 'fuse'), get_fuse_option ('header_scripts', ''), array (
                        'description' => __ ('All scripts and styles must be included inside the relevant HTML tags (&lt;script&gt;, &lt;style&gt;).', 'fuse')
                    )),
                    new Component\Field\TextArea ('body_scripts', __ ('Code to be added at the start of the &lt;body&gt; tag', 'fuse'), get_fuse_option ('body_scripts', '')),
                    new Component\Field\TextArea ('footer_scripts', __ ('Code to be added before the closing &lt;body&gt; tag', 'fuse'), get_fuse_option ('footer_scripts', ''))
                )),
                new Component\Panel ('google_api', __ ('Google API', 'fuse'), apply_filters ('fuse_settings_form_google_api_fields', array (
                    new Component\Field\Text ('google_api_key', __ ('Google API Key'), get_fuse_option ('google_api_key', ''), array (
                        'class' => 'full',
                        'description' => __ ('Please make sure that this Google API key is available for every Google API that is needed for your site.', 'fuse')
                    ))
                )))
            ));
            
            $args = array (
                'id' => 'fuse-settings-form',
                'method' => 'post',
                'action' => esc_url (admin_url ('admin.php?page=fusesettings')),
                'action_bar' => new \Fuse\Forms\Component\ActionBar (array (
                    new Component\Button (apply_filters ('fuse_settings_form_submit_text', __ ('Save settings', 'fuse')))
                ))
            );
            
            parent::__construct ($panels, $args);
        } // __construct ()
        
        
        
        
        /**
         *  Get the contact details panel.
         */
        protected function _getContactPanel () {
            $locations = apply_filters ('fuse_contact_locations', array (
                'default' => __ ('Contact details', 'fuse')
            ));
            
            $location_panels = array ();
            
            foreach ($locations as $key => $label) {
                $location_panels [] = new Component\Field\Group ('contact_location_'.$key, $label, $this->_getContactLocationFields ($key));
            } // foreach ()
            
            $location_panels [] = new Component\Field\Group ('contact_social', __ ('Social Media Links', 'fuse'), apply_filters ('fuse_contact_social_media_links', array (
                new Component\Field\Url ('contact_social_facebook', __ ('Facebook', 'fuse'), get_fuse_option ('contact_social_facebook')),
                new Component\Field\Url ('contact_social_instagram', __ ('Instagram', 'fuse'), get_fuse_option ('contact_social_instagram')),
                new Component\Field\Url ('contact_social_tiktok', __ ('TikTok', 'fuse'), get_fuse_option ('contact_social_tiktok')),
                new Component\Field\Url ('contact_social_youtube', __ ('YouTube', 'fuse'), get_fuse_option ('contact_social_youtube')),
                new Component\Field\Url ('contact_social_linkedin', __ ('Linkedin', 'fuse'), get_fuse_option ('contact_social_linkedin'))
            )));
            
            return new Component\Panel ('contact_details', __ ('Contact Details', 'fuse'), $location_panels);
        } // _getContactPanel ()
        
        /**
         *  Get the fields for the given location.
         */
        protected function _getContactLocationFields ($location) {
            return apply_filters ('fuse_contact_location_fields', array (
                new Component\Field\Text ('contact_phone_'.$location, __ ('Phone number', 'fuse'), get_fuse_option ('contact_phone_'.$location)),
                new Component\Field\Email ('contact_email_'.$location, __ ('Email address', 'fuse'), get_fuse_option ('contact_email_'.$location)),
                new Component\Field\Textarea ('contact_street_'.$location, __ ('Street address', 'fuse'), get_fuse_option ('contact_street_'.$location)),
                new Component\Field\Text ('contact_town_'.$location, __ ('Suburb/Town', 'fuse'), get_fuse_option ('contact_town_'.$location)),
                new Component\Field\Text ('contact_state_'.$location, __ ('State', 'fuse'), get_fuse_option ('contact_state_'.$location)),
                new Component\Field\Text ('contact_postcode_'.$location, __ ('Postcode', 'fuse'), get_fuse_option ('contact_postcode_'.$location)),
                new Component\Field\Text ('contact_country_'.$location, __ ('Country', 'fuse'), get_fuse_option ('contact_country_'.$location))
            ), $location);
        } // _getContactLocationFields ()
        
        /**
         *  Get the image terms.
         */
        protected function _getImageTerms () {
            $image_terms = NULL;
            
            if (get_fuse_option ('term_images', 0) == 'yes') {
                $taxonomies = get_taxonomies (array (
                    'public' => true
                ), 'objects');
                
                $tax_fields = array ();
                
                foreach ($taxonomies as $tax) {
                    $tax_fields [] = new Component\Field\Toggle ('tax_images_'.$tax->name, $tax->label, get_fuse_option ('tax_images_'.$tax->name, false));
                } // foreach ()
                
                $image_terms = new Component\Field\Group ('taxonomys_for_images', __ ('Set taxonomies for featured images', 'fuse'), $tax_fields, array (
                    'columns' => 4
                ));
            } // if ()
            
            return $image_terms;
        } // _getImageTerms ()
        
        /**
         *  Get the image terms.
         */
        protected function _getGSAPModules () {
            $modules = NULL;
            
            if (get_fuse_option ('gsap', 0) == 'yes') {
                $settings = \Fuse\Settings\Form\GSAP::getInstance ();
                
                $fields = array ();
                
                foreach ($settings->modules as $key => $label) {
                    $fields [] = new Component\Field\Toggle ('gsap_module_'.$key, $label, get_fuse_option ('gsap_module_'.$key, false));
                } // foreach ()
                
                $modules = new Component\Field\Group ('gsap_modules', __ ('Enable optional GSAP modules', 'fuse'), $fields, array (
                    'columns' => 4
                ));
            } // if ()
            
            return $modules;
        } // _getGSAPModules ()
        
    } // class Settings