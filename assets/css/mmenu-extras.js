/**
 *  This function lets you remove the <a href=""> tags from empty placeholder links.
 *  This replaces them with <span> tags to conform to teh Mmenu way of things.
 */

function fuse_mmenu_fix_placeholder_links (menu_el) {
    menu_el.find ('a[href="#"]').each (function () {
        var el = jQuery (this);
        var span = jQuery ('<span>' + el.html () + '</span>');
       
        el.replaceWith (span);
    });
} // fuse_menu_fix_placeholder_links ()