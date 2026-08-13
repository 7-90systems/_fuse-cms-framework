<?php
    /**
     *  This trait takes care of our settings objects.
     * 
     *  Values should be set with a leading underscore.
     */

    namespace Fuse\Traits;

    use Fuse\Traits\Singleton;


    trait Settings {




        use Singleton;




        /**
         *  Get a value from the set values.
         * 
         *  @param string $name The name of the setting to retieve.
         * 
         *  @return mixed Returns the setitng value or NULL of the setting does not exist.
         */
        public function get ($name) {
            $value = NULL;
            $name = '_'.$name;

            if (property_exists ($this, $name)) {
                $value = $this->$name;
            } // if ()

            return $value;
        } // get ()

        /**
         *  Get a value from the set values.
         * 
         *  @param string $name The name of the setting to retieve.
         * 
         *  @return mixed Returns the setitng value or NULL of the setting does not exist.
         */
        public function __get ($name) {
            return $this->get ($name);
        } // __get ()

    } // trait Settings