<?php
    /**
     *  @package fusecms
     *
     *  This file contains our security functions.
     */
    
    /**
     *  Ensure that any file is not accessed directly.
     * 
     *  @param bool $include_message Tre to include a message. Defaults to false.
     */
    function fuse_block_direct_access () {
        if (!defined ('ABSPATH')) {
            die ();
        } // if ()
    } // fuse_block_direct_access ()