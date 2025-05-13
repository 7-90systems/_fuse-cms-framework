jQuery (document).ready (function () {
    
    jQuery ('.fuse-tabs-list').find ('.fuse-tab').hide ().first ().show ();
    jQuery ('.fuse-tabs-nav li:first-child a').addClass ('active');
    
    jQuery ('.fuse-tabs-nav li a, .fuse-tab-nav-mobile').on ('click', function (e) {
        e.preventDefault ();
        
        let btn = jQuery (this);
        let container = btn.closest ('.fuse-tabs-container');
        let tabs = container.find ('.fuse-tab');
        let el_id = btn.attr ('href');
        
        tabs.hide ();
        jQuery (el_id).show ();
        
        container.find ('.fuse-tabs-nav li a, .fuse-tab-nav-mobile').each (function () {
            let link = jQuery (this);
            
            if (link.attr ('href') == el_id) {
                link.addClass ('active');
            } // if ()
            else {
                link.removeClass ('active');
            } // else
        });
    });

});