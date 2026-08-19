<?php
	/**
	 *	This class provides our utility functions. All functions are accessed
	 *	statically so you don't need to create a new object to use them.
	 */

	namespace Fuse;
	
	use Datetime;


	class Util {

		/**
		 *	Block direct instantiation.
		 */
		private function __construct () {}
		
		
		

		/**
		 *	Save attachment media file
		 *
		 *	@param array $file The file details, taken from the $_FILES array.
		 *	@param string $title The title for this media file.
		 *	@param WP_Post $post The post object to attach the media item to.
		 *
		 *	@return int Returns the attachment ID or a NULL value if an error
		 *	has occured.
		 */
		static public function saveAttachmentFile ($file, $title = 'Attachment', $post = NULL) {
			$id = NULL;
			
			/**
			 *  wp_handle_upload () and the metadata helpers only exist in the
			 *  admin by default, so make sure they are loaded before we reach
			 *  for them. Without this, calling this from the front end is a
			 *  fatal error.
			 */
			require_once (ABSPATH.'wp-admin/includes/file.php');
			require_once (ABSPATH.'wp-admin/includes/image.php');
			require_once (ABSPATH.'wp-admin/includes/media.php');
			
			if (is_array ($file) === false || array_key_exists ('name', $file) === false) {
				return NULL;
			} // if ()
			
			$arr_file_type = wp_check_filetype (basename ($file ['name']));
			$uploaded_file_type = $arr_file_type ['type'];
			
			// Refuse anything WordPress cannot identify as an allowed type.
			if (empty ($uploaded_file_type)) {
				return NULL;
			} // if ()
			
			$upload_overrides = array ('test_form' => false);
			
			$uploaded_file = wp_handle_upload ($file, $upload_overrides);
			
			if (is_array ($uploaded_file) && isset ($uploaded_file ['file'])) {
				$file_name_and_location = $uploaded_file ['file'];
				
				$attachment = array (
					/**
					 *  Trust the type wp_handle_upload () settled on rather
					 *  than the one guessed from the original file name.
					 */
					'post_mime_type' => isset ($uploaded_file ['type']) ? $uploaded_file ['type'] : $uploaded_file_type,
					/**
					 *  wp_insert_attachment () escapes this itself, so the old
					 *  addslashes () call here left backslashes in the title.
					 */
					'post_title' => sanitize_text_field ($title),
					'post_content' => '',
					'post_status' => 'inherit'
				);
				
				if (!is_null ($post)) {
					if (!is_numeric ($post)) {
						$post = $post->ID;
					} // if ()
					
					$attachment ['post_parent'] = intval ($post);
				} // if ()
				
				$id = wp_insert_attachment ($attachment, $file_name_and_location);
				
				if (is_wp_error ($id) || $id === 0) {
					return NULL;
				} // if ()
				
				$attach_data = wp_generate_attachment_metadata ($id, $file_name_and_location);
				wp_update_attachment_metadata ($id, $attach_data);
			} // if ()
			else {
				\Fuse\Debug::dump ($uploaded_file, 'Fuse attachment upload failed');
			} // if ()
			
			return $id;
		} // saveAttachmentFile ()




        /**
         *  Generate a randoms string.
         *
         *  @param int $length The number of characters in the string.
         *  @param bool $include_numbers True to include numbers in the string.
         *  @param book $include_upper True to include upper-case letters in the string.
         *  @param bool $include_symbols True to include symbols in the string.
         *
         *  return string The random string.
         */
        static public function randomString ($length = 8, $include_numbers = true, $include_upper = true, $include_symbols = true) {
            $chars = array ('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

            if ($include_numbers !== false) {
                $chars = array_merge ($chars, array ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'));
            } //if ()

            if ($include_upper !== false) {
                $chars = array_merge ($chars, array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'));
            } // if ()

            if ($include_symbols !== false) {
                $chars = array_merge ($chars, array ('!', '@', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '[', '{', ']', '}', '|', ';', ':', ',', '<', '.', '>', '/', '?'));
            } // if ()

            $char_count = count ($chars);

            $string = '';

            while (strlen ($string) < $length) {
                if (function_exists ('random_int')) {
                    // PHP 7 function, generates cryptographically secure pseudo-random integers
                    $index = random_int (0, $char_count - 1);
                } // if ()
                else {
                    // Older PHP versions
                    $index = mt_rand (0, $char_count - 1);
                } // else

                $string.= $chars [$index];
            } // while ()

            return $string;
        } // randomString ()
		
		
		
		
		/**
		 *	Get the months of the year.
		 *
		 *	@return array The months of the year with name and the number of days for that month.
		 */
		static public function getMonths ($year = NULL) {
			$months = array ();
			
			if (empty ($year) === true) {
				$year = current_time ('Y');
			} // if ()
			
			$date = new DateTime ();
			
			for ($i = 1; $i <= 12; $i++) {
				$date->setDate ($year, $i, 1);
				
				$months [$i] = array (
					'name' => $date->format ('F'),
					'days' => $date->  format ('t')
				);
			} // for ()
			
			return $months;
		} // getMonths ()

	} // class Util