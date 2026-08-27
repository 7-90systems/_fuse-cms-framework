<?php
    /**
     *  @package fuse-cms-framework
     *
     *  @version 1.0
     *
     *  This is a field for choosing another post: the venue a review is of, the
     *  company a job belongs to, the city a place sits in.
     *
     *  It is a select rather than anything cleverer because a select needs no
     *  JavaScript, works on a phone, and is reachable from a keyboard without
     *  being taught how.
     */

    namespace Fuse\Forms\Component\Field;

    use Fuse\Forms\Component\Field\Select;


    class PostSelect extends Select {

        /**
         *  @var int Above this many records a select stops being a sensible way
         *  to choose one, and the field says so rather than rendering a list
         *  nobody can use.
         */
        const TOO_MANY = 500;




        /**
         *  @var array The post types to choose from.
         */
        protected $_post_types;

        /**
         *  @var string The label on the empty option.
         */
        protected $_empty_label;




        /**
         *  Object constructor.
         *
         *  @param string $name The fields name.
         *  @param string $label The fields label.
         *  @param array|string $post_types The post type or types to list.
         *  @param mixed $value The chosen post ID.
         *  @param array $args The arguments for this field. See the parent
         *  class for valid argument values, plus one of ours:
         *      empty_label - the text on the "nothing chosen" option.
         */
        public function __construct ($name, $label, $post_types, $value = '', array $args = array ()) {
            $this->_post_types = (array) $post_types;

            $this->_empty_label = array_key_exists ('empty_label', $args)
                ? $args ['empty_label']
                : __ ('— none —', 'fuse');

            unset ($args ['empty_label']);

            parent::__construct ($name, $label, array (), $value, $args);
        } // __construct ()




        /**
         *  Build the list of records at the moment it is needed.
         *
         *  Not in the constructor: a post type's fields are often built to
         *  answer questions about themselves as well as to be shown, and a
         *  field that queried on construction would run that query on every
         *  save too.
         *
         *  @return array The options, as ID => title.
         */
        public function getOptions () {
            if (count ($this->_options) > 0) {
                return $this->_options;
            } // if ()

            $options = array (
                '' => $this->_empty_label
            );

            foreach ($this->_post_types as $post_type) {
                $options = $this->_addPostType ($options, $post_type);
            } // foreach ()

            $this->_options = $options;

            return $this->_options;
        } // getOptions ()

        /**
         *  Add one post type's records to the options.
         *
         *  @param array $options The options so far.
         *  @param string $post_type The post type.
         *
         *  @return array The options.
         */
        protected function _addPostType ($options, $post_type) {
            $object = get_post_type_object ($post_type);

            if ($object === NULL) {
                return $options;
            } // if ()

            $posts = get_posts (array (
                'post_type' => $post_type,
                'post_status' => array ('publish', 'private', 'draft', 'pending', 'future'),
                'posts_per_page' => self::TOO_MANY + 1,
                'orderby' => 'title',
                'order' => 'ASC',
                'no_found_rows' => true
            ));

            if (count ($posts) > self::TOO_MANY) {
                $options [''] = sprintf (
                    /*  translators: %s: the plural name of a post type.  */
                    __ ('Too many %s to list here', 'fuse'),
                    strtolower ($object->labels->name)
                );

                return $options;
            } // if ()

            $entries = array ();

            /**
             *  A hierarchical type is shown as its hierarchy, so a city sits
             *  under its state under its country rather than in an alphabetical
             *  run where three places of the same name look identical.
             */
            if ($object->hierarchical === true) {
                $entries = $this->_hierarchy ($posts);
            } // if ()
            else {
                foreach ($posts as $post) {
                    $entries [$post->ID] = $this->_title ($post);
                } // foreach ()
            } // else

            // Only group when there is more than one type to tell apart.
            if (count ($this->_post_types) > 1) {
                $options [$post_type] = array (
                    'label' => $object->labels->name,
                    'values' => $entries
                );

                return $options;
            } // if ()

            return $options + $entries;
        } // _addPostType ()

        /**
         *  Lay a hierarchical post type out as an indented list.
         *
         *  @param array $posts The posts.
         *
         *  @return array The entries, as ID => indented title.
         */
        protected function _hierarchy ($posts) {
            $children = array ();

            foreach ($posts as $post) {
                $children [$post->post_parent] [] = $post;
            } // foreach ()

            return $this->_hierarchyBranch ($children, 0, 0);
        } // _hierarchy ()

        /**
         *  Walk one level of the hierarchy.
         *
         *  @param array $children The posts, grouped by parent ID.
         *  @param int $parent The parent to walk.
         *  @param int $depth How deep we are.
         *
         *  @return array The entries, as ID => indented title.
         */
        protected function _hierarchyBranch ($children, $parent, $depth) {
            $entries = array ();

            if (array_key_exists ($parent, $children) === false) {
                return $entries;
            } // if ()

            $indent = str_repeat ('&nbsp;&nbsp;&nbsp;', $depth);

            foreach ($children [$parent] as $post) {
                $entries [$post->ID] = $indent.$this->_title ($post);

                $entries = $entries + $this->_hierarchyBranch ($children, $post->ID, $depth + 1);
            } // foreach ()

            return $entries;
        } // _hierarchyBranch ()

        /**
         *  The title to show for a record.
         *
         *  An unpublished record says so, so that choosing one is a decision
         *  rather than a surprise later on.
         *
         *  @param \WP_Post $post The post.
         *
         *  @return string The title.
         */
        protected function _title ($post) {
            $title = $post->post_title;

            if ($title === '') {
                $title = sprintf (
                    /*  translators: %d: a post ID.  */
                    __ ('(untitled — #%d)', 'fuse'),
                    $post->ID
                );
            } // if ()

            if ($post->post_status !== 'publish') {
                $status = get_post_status_object ($post->post_status);

                $title .= ' — '.($status === NULL ? $post->post_status : $status->label);
            } // if ()

            return $title;
        } // _title ()




        /**
         *  Render the field!
         *
         *  @param bool $output True to render the field, or false to return the
         *  HTML code.
         *
         *  @return string Returns the fields HTML code.
         */
        public function render ($output = true) {
            // Fill the options in before the parent asks for them.
            $this->getOptions ();

            return parent::render ($output);
        } // render ()

    } // class PostSelect
