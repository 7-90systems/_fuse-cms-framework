/**
 *  @package fuse-cms
 *
 *  This file contains the functions for buttons.
 */

jQuery (document).ready (function () {
    fuseButtonsSetup ();
});




/**
 *  Fix the dashicons for buttons to be on the link and not the container
 */
function fuseButtonsSetup () {
    jQuery ('.wp-block-button').filter ('[class*="dashicons"]').each (function () {
        var el = jQuery (this);
        var class_name = jQuery (this).attr ('class').match (/dashicons-[A-Za-z\-]+/);
        
        el.removeClass (class_name);
        el.find ('a').addClass (class_name);
    });
} // fuseButtonsSetup ()