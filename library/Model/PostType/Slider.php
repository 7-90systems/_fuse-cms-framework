<?php
    /**
     *  Set up our slider model.
     */
    
    namespace Fuse\Model\PostType;
    
    use Fuse\Model;
    
    
    class Slider extends Model {
        
        /**
         *  Get the slides for this slider.
         *
         *  @param string $active Set as 'all' or 'active'.
         *
         *  @return array An array of slides set for this slider.
         */
        public function getSlides ($active = 'active') {
            global $wpdb;
            
            $now = current_time ('mysql');
            
            if ($active == 'active') {
                // Only get ACTIVE slides
                $query = $wpdb->prepare ("SELECT
                    slide.ID AS ID
                FROM ".$wpdb->posts." AS slide

                LEFT JOIN ".$wpdb->postmeta." AS start_time
                    ON start_time.post_id = slide.ID
                    AND start_time.meta_key = 'fuse_slide_start'
                    
                LEFT JOIN ".$wpdb->postmeta." AS end_time
                    ON end_time.post_id = slide.ID
                    AND end_time.meta_key = 'fuse_slide_end'
                    
                WHERE slide.post_type = 'fuse_slide'
                    AND slide.post_status = 'publish'
                    AND slide.post_date <= %s
                    AND slide.post_parent = %d
                    -- AND (
                        -- start_time.meta_value IS NULL
                        -- OR UNIX_TIMESTAMP(start_time.meta_value) <= UNIX_TIMESTAMP(%s)
                    -- )
                    -- AND (
                        -- end_time.meta_value IS NULL
                        -- OR UNIX_TIMESTAMP(end_time.meta_value) >= UNIX_TIMESTAMP(%s)
                    -- )
                ORDER BY slide.menu_order ASC, slide.post_date DESC", $now, $this->_post->ID, $now, $now);
            } // if ()
            else {
                // Get ALL slides
                $query = $wpdb->prepare ("SELECT
                    slide.ID AS ID
                FROM ".$wpdb->posts." AS slide
                WHERE slide.post_type = 'fuse_slide'
                    AND slide.post_status = 'publish'
                    AND slide.post_date <= %s
                    AND slide.post_parent = %d
                ORDER BY slide.menu_order ASC, slide.post_date DESC", $now, $this->_post->ID);
            } // else
            
            $slides = array ();
            
            foreach ($wpdb->get_results ($query) as $row) {
                $slides [] = get_post ($row->ID);
            } // foreach ()
                
            return $slides;
        } // getSlides ()
        
        
        
        
        /**
         *  Render our slide!
         *
         *  @param string $output Set as true to output the HTML, or false to return the HTML.
         *
         *  @return string|NULL Returns or outputs the sliders HTML code.
         */
        public function render ($output = true) {
            ob_start ();
            include ($this->_getTemplateUri ('slider.php'));
            $html = ob_get_contents ();
            ob_end_clean ();
            
            if ($output === true) {
                echo $html;
            } // if ()
            else {
                return $html;
            } // else
        } // render ()
        
        
        
        
        /**
         *  Get the HTML code for this slider.
         */
        public function __toString () {
            return $this->render (false);
        } // __toString ()
        
        
        
        
        /**
         *  Get the URI of the template file to be used.
         *
         *  This taken from the first file found in these locations:
         *      {child theme}/templates/slider/{file}
         *      {parent theme}/templates/slider/{file}
         *      {Fuse}/templates/slider/{file}
         *
         *  The files for this can be either slider.php or slide.php or other
         *  template files such as slider-main.php in your themes template folders.
         *
         *  @param string $file The template file that we want to find.
         *
         *  @return string The template file URI.
         */
        protected function _getTemplateUri ($file) {
            // Get the template files that we want to use
            $template = NULL;
            $template_file = get_post_meta ($this->_post->ID, 'fuse_slider_template_'.substr ($file, 0, strlen ($file) - 4), true);
            
            foreach (apply_filters ('fuse_slider_template_locations', array ()) as $folder_uri => $folder_url) {
                if (empty ($template) && file_exists ($folder_uri.$template_file)) {
                    $template = $folder_uri.$template_file;
                } // if ()
            } // foreach ()
            
            if (empty ($template)) {
                $template = FUSE_BASE_URI.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'slider'.DIRECTORY_SEPARATOR.$file;
            } // if ()
            
            return $template;
        } //_getTemplateUri ();
        
    } // class Slider