jQuery (document).ready (function () {
    fuseBuilderSetSortable ();

    jQuery ('button.fuse_builder_new_metabox_add').click (function (e) {
        e.preventDefault ();
        
        let html = jQuery ('template#fuse_builder_meta_box').html ();
        let container = jQuery (this).siblings ('.fuse_posttype_builder_metaboxes');
        
        container.append (html);
        fuseBuilderSetMetaboxes ();
    });
    
    // Toggle fields
    jQuery ('.fuse_posttype_builder_metaboxes').on ('click', '.fuse_builder_metabox_title a.expand', function (e) {
        e.preventDefault ();
        
        let btn = jQuery (this);
        let container = btn.closest ('.fuse-builder-metabox').find ('.fuse_builder_metabox_fields');
        
        if (btn.hasClass ('open')) {
            btn.removeClass ('open');
            container.slideUp (200);
        } // if ()
        else {
            btn.addClass ('open');
            container.slideDown (200);
        } // else
    });
    
    // Delete metabox
    jQuery ('.fuse_posttype_builder_metaboxes').on ('click', '.fuse_builder_metabox_title a.delete', function (e) {
        e.preventDefault ();
        
        let do_delete = confirm ('Are you sure that you want to delete this metabox?');
        
        if (do_delete) {
            jQuery (this).closest ('.fuse-builder-metabox').remove ();
            fuseBuilderSetMetaboxes ();
        } // if ()
    });
    
    // Metabox name field
    jQuery ('.fuse_posttype_builder_metaboxes').on ('keyup', '.fuse_builder_metabox_fields .metabox-name', function () {
        let field = jQuery (this);
        
        field.closest ('.fuse-builder-metabox').find ('h4.title').html (field.val ());
        fuseBuilderSetMetaboxes ();
    });
    
    
    
    
    // Fields
    jQuery ('.fuse_posttype_builder_metaboxes').on ('click', '.fuse_builder_field_add', function (e) {
       e.preventDefault ();
       let html = jQuery ('template#fuse_builder_meta_box_field').html ();
       
       jQuery (this).closest ('.fuse-builder-metabox').find ('.fuse_builder_metabox_fields_list').append (html);
       fuseBuilderSetMetaboxes ();
    });
    
    // Toggle fields
    jQuery ('.fuse_posttype_builder_metaboxes').on ('click', '.fuse_builder_metabox_field_title a.expand', function (e) {
        e.preventDefault ();
        
        let btn = jQuery (this);
        let container = btn.closest ('.fuse-builder-metabox-field').find ('.fuse_builder_metabox_field_settings');
        
        if (btn.hasClass ('open')) {
            btn.removeClass ('open');
            container.slideUp (200);
        } // if ()
        else {
            btn.addClass ('open');
            container.slideDown (200);
        } // else
    });
    
    // Metabox field name field
    jQuery ('.fuse_posttype_builder_metaboxes').on ('keyup', '.fuse_builder_metabox_field_settings .metabox-field-name', function () {
        let field = jQuery (this);
        
        field.closest ('.fuse-builder-metabox-field').find ('h4.title').html (field.val ());
        fuseBuilderSetMetaboxes ();
    });
    
    // Metabox field type
    jQuery ('.fuse_posttype_builder_metaboxes').on ('change', '.fuse_builder_field_type', function () {
        let type = jQuery (this).val ();
        let table = jQuery (this).closest ('table.form-table');
        
        table.find ('tr.fuse_field_options').hide ();
        table.find ('tr.fuse_field_option_' + type).show ();
        
        fuseBuilderSetMetaboxes ();
    });
    
    // Field updates
    jQuery ('.fuse_posttype_builder_metaboxes').on ('keyup', '.fuse_builder_metabox_field_settings .metabox-data_key, .fuse_builder_metabox_field_settings_list input, .fuse_builder_metabox_field_settings_list textarea', function () {
        fuseBuilderSetMetaboxes ();
    });
    jQuery ('.fuse_posttype_builder_metaboxes').on ('change', '.fuse_builder_metabox_field_settings_list select', function () {
        fuseBuilderSetMetaboxes ();
    });
    
    // Delete field
    jQuery ('.fuse_posttype_builder_metaboxes').on ('click', '.fuse_builder_metabox_field_title a.delete', function (e) {
        e.preventDefault ();
        
        let do_delete = confirm ('Are you sure that you want to delete this field?');
        
        if (do_delete) {
            jQuery (this).closest ('.fuse-builder-metabox-field').remove ();
            fuseBuilderSetMetaboxes ();
        } // if ()
    });
    
    // Sort fields
    jQuery ('.fuse_posttype_builder_metaboxes').on ('click', 'a.move', function (e) {
        e.preventDefault ();
    });
});




/**
 *  Set the sortable areas.
 */
function fuseBuilderSetSortable () {
    jQuery ('.fuse_posttype_builder_metaboxes').not ('.sortable').sortable ({
        items: '.fuse-builder-metabox',
        toleranceElement: '> div',
        update: function () {
            fuseBuilderSetMetaboxes ();
        }
    }).addClass ('sortable');
    
    jQuery ('.fuse_builder_metabox_fields_list').not ('.sortable').sortable ({
        items: '.fuse-builder-metabox-field',
        toleranceElement: '> div',
        update: function () {
            fuseBuilderSetMetaboxes ();
        }
    }).addClass ('sortable');
} // fuseBuilderSetSortable ()




/**
 *  Construct the JSON code to represent the metaboxes.
 */
function fuseBuilderSetMetaboxes () {
    let metaboxes = [];
    
    let sections = [
        'main',
        'side'
    ];
    
    for (let i in sections) {
        jQuery ('#fuse_posttype_builder_metaboxes_' + sections [i] + ' .fuse-builder-metabox').each (function () {
            let el = jQuery (this);
            let metabox_fields = [];
            
            jQuery (this).find ('.fuse_builder_metabox_fields_list .fuse-builder-metabox-field').each (function () {
                let field_el = jQuery (this);
                let field_type = field_el.find ('.fuse_builder_field_type').val ();
                let field_settings = {};
                
                field_el.find ('.fuse_field_options input, .fuse_field_options textarea, .fuse_field_options select').each (function () {
                    let setting = jQuery (this);
                    let setting_name = setting.attr ('name');
                    
                    field_settings [setting_name] = setting.val ();
                });
                
                let field = {
                    type: field_type,
                    name: field_el.find ('.metabox-field-name').val (),
                    key: field_el.find ('.metabox-data-key').val (),
                    settings: field_settings
                };
                
                metabox_fields.push (field);
            });
            
            let metabox = {
                section: sections [i],
                name: el.find ('.metabox-name').val (),
                fields: metabox_fields
            };
            
            metaboxes.push (metabox);
        });
    } // for ()
// console.log ("We have '" + metaboxes.length + "' metaboxes");
// console.log (JSON.stringify (metaboxes));
    
    jQuery ('#fuse_builder_metaboxes').val (JSON.stringify (metaboxes));
} // fuseBuildersetMetaBoxes ()