jQuery (document).ready (function () {

    // Set up our form fields.
    fuseAdminFormFieldImage ();
    fuseAdminFormFieldFile ();
    fuseAdminFormFieldGallery ();
    fuseAdminFormFieldIconGroup ();

});




/**
 *  Set up our image fields.
 */
function fuseAdminFormFieldImage () {
    jQuery ('.fuse-image-field').each (function () {
        var container = jQuery (this);
        var img = container.find ('.fuse-image-image');
        var btn = container.find ('.select-image-container');
    
        // Uploading files
        var file_frame;
        var previous_post_id = null;
            
        container.find ('.choose-image-link').on ('click', function (e) {
            e.preventDefault ();
            
            // Attach anything uploaded or saved from the media window to this post.
            previous_post_id = _fuseAdminMediaSetPostId ();

            // If the media frame already exists, reopen it.
            if (file_frame) {
                // Open frame
                file_frame.open ();
                return;
            } // if ()
            
            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media ({
                title: 'Select image',
                button: {
                    text: 'Select',
                },
                library: {
                    type: [
                        'image'
                    ]
                },
                multiple: false
            });
            
            // When an image is selected, run a callback.
            file_frame.on ('select', function () {
                // We set multiple to false so only get one image from the uploader
                var attachment = file_frame.state ().get ('selection').first ().toJSON ();
                var id = attachment.id;
                var src = attachment.sizes.thumbnail.url;
                
                img.find ('img').attr ('src', src);
                
                btn.hide ();
                img.show ();
                
                container.find ('input').val (id);
            });
            
            // Put the media library back the way we found it.
            file_frame.on ('close', function () {
                _fuseAdminMediaRestorePostId (previous_post_id);
            });

            // Finally, open the modal
            file_frame.open ();
        });
    });
    
    jQuery ('.fuse-image-image .dashicons').click (function (e) {
        e.preventDefault ();
        
        var container = jQuery (this).closest ('.fuse-image-field');
        var img = container.find ('.fuse-image-image');
        var add_link = container.find ('.select-image-container');
        var input = container.find ('input');
        
        img.hide ();
        add_link.show ();
        input.val ('');
    });
} // fuseAdminFormFieldImage ()




/**
 *  Set up our file fields.
 */
function fuseAdminFormFieldFile () {
    jQuery ('.fuse-file-field').each (function () {
        var container = jQuery (this);
        var file = container.find ('.fuse-file-file');
        var btn = container.find ('.select-file-container');
    
        // Uploading files
        var file_frame;
        var previous_post_id = null;
            
        container.find ('.choose-file-link').on ('click', function (e) {
            e.preventDefault ();
            
            // Attach anything uploaded or saved from the media window to this post.
            previous_post_id = _fuseAdminMediaSetPostId ();

            // If the media frame already exists, reopen it.
            if (file_frame) {
                // Open frame
                file_frame.open ();
                return;
            } // if ()
            
            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media ({
                title: 'Select file',
                button: {
                    text: 'Select',
                },
                multiple: false
            });
            
            // When an image is selected, run a callback.
            file_frame.on ('select', function () {
                // We set multiple to false so only get one image from the uploader
                var attachment = file_frame.state ().get ('selection').first ().toJSON ();
                var id = attachment.id;
                var src = attachment.url;
                
                file.find ('a').attr ('href', src).html (src.split ('/').pop ());
                
                btn.hide ();
                file.show ();
                
                container.find ('input').val (id);
            });
            
            // Put the media library back the way we found it.
            file_frame.on ('close', function () {
                _fuseAdminMediaRestorePostId (previous_post_id);
            });

            // Finally, open the modal
            file_frame.open ();
        });
    });
    
    jQuery ('.fuse-file-file .dashicons').click (function (e) {
        e.preventDefault ();
        
        var container = jQuery (this).closest ('.fuse-file-field');
        var file = container.find ('.fuse-file-file');
        var add_link = container.find ('.select-file-container');
        var input = container.find ('input');
        
        file.hide ();
        add_link.show ();
        input.val ('');
    });
} // fuseAdminFormFieldFileImage ()




/**
 *  Set up our gallery fields.
 */
function fuseAdminFormFieldGallery () {
    jQuery ('.fuse-gallery-field').on ('click', '.fuse-gallery-image .dashicons', function (e) {
        e.preventDefault ();
        
        var img = jQuery (this);
        var container = img.closest ('.fuse-gallery-field');
        
        img.closest ('.fuse-gallery-image').remove ();
        _fuseAdminGallerySetIds (container);
    });
    
    jQuery ('.fuse-gallery-field .gallery-images').sortable ({
        update: function () {
            var container = jQuery (this).closest ('.fuse-gallery-field');
            _fuseAdminGallerySetIds (container);
        }
    });
    
    // Set up our media windows
    jQuery ('.fuse-gallery-field').each (function () {
        var container = jQuery (this);
    
        // Uploading files
        var file_frame;
        var previous_post_id = null;
                        
        var btn;
        var cell;
            
        container.find ('.choose-gallery-images-link').on ('click', function (e) {
            e.preventDefault ();
                            
            // Get our surrondings
            btn = jQuery (this);
            
            // Attach anything uploaded or saved from the media window to this post.
            previous_post_id = _fuseAdminMediaSetPostId ();

            // If the media frame already exists, reopen it.
            if (file_frame) {
                // Open frame
                file_frame.open ();
                return;
            } // if ()
            
            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media ({
                title: 'Add images to this gallery',
                button: {
                    text: 'Add gallery images',
                },
                library: {
                    type: [
                        'image'
                    ]
                },
                multiple: true
            });
            
            // When an image is selected, run a callback.
            file_frame.on ('select', function () {
                // We set multiple to true so get all of the images
                var attachments = file_frame.state ().get ('selection').map ( 
                    function( attachment ) {
                        attachment.toJSON();
                        return attachment;
                    }
                );
                
                for (var i in attachments) {
                    var att = attachments [i];
                    
                    var id = att.attributes.id;
                    var src = att.attributes.sizes.thumbnail.url;
                    var img_html = container.find ('template.fuse-gallery-image').clone ().html ();
                    
                    img_html = img_html.replace ('%%ID%%', id);
                    img_html = img_html.replace ('%%SRC%%', src);
                    
                    container.find ('.gallery-images').append (img_html);
                } // for ()
                
                _fuseAdminGallerySetIds (container);
            });
            
            // Put the media library back the way we found it.
            file_frame.on ('close', function () {
                _fuseAdminMediaRestorePostId (previous_post_id);
            });

            // Finally, open the modal
            file_frame.open ();
        });
    
    });
} // fuseAdminFormFieldGallery ()

/**
 *  Set the gallery IDs
 */
function _fuseAdminGallerySetIds (container) {
    var ids = [];
    
    container.find ('.fuse-gallery-image img').each (function () {
        ids.push (jQuery (this).data ('id'));
    });
        
    container.find ('input').val (ids);
} // _fuseAdminGallerySetIds ()




/**
 *  Set up the icon group form field.
 */
function fuseAdminFormFieldIconGroup () {
    jQuery ('a.fuse-form-field-icongroup-image').on ('click', function (e) {
        e.preventDefault ();
        
        let el = jQuery (this);
        let container = el.closest ('.fuse-form-field-icongroup');
        
        container.find ('input').val (el.data ('value'));
        container.find ('a.fuse-form-field-icongroup-image').removeClass ('selected');
        el.blur ().addClass ('selected');
    });
} // fuseAdminFormFieldIconGroup ()



/**
 *  Point the media window at the post being edited, so that anything uploaded or
 *  saved from it is attached to that post. Returns the ID that was set before, to
 *  hand back to _fuseAdminMediaRestorePostId (), or NULL when there is nothing to
 *  change.
 */
function _fuseAdminMediaSetPostId () {
    var post = _fuseAdminMediaPostSettings ();
    var post_id = _fuseAdminMediaPostId ();

    if (!post || post_id === 0) {
        return null;
    } // if ()

    var previous_post_id = post.id;

    post.id = post_id;

    return previous_post_id;
} // _fuseAdminMediaSetPostId ()




/**
 *  Put the media window's post ID back to what it was before we opened it, so we
 *  leave the rest of the page as we found it.
 */
function _fuseAdminMediaRestorePostId (previous_post_id) {
    var post = _fuseAdminMediaPostSettings ();

    if (!post || previous_post_id === null) {
        return;
    } // if ()

    post.id = previous_post_id;
} // _fuseAdminMediaRestorePostId ()




/**
 *  Get the ID of the post being edited, or 0 when we are not editing one (on a
 *  settings page, for example).
 */
function _fuseAdminMediaPostId () {
    var post_id = parseInt (jQuery ('#post_ID').val (), 10);

    if (isNaN (post_id) || post_id < 1) {
        return 0;
    } // if ()

    return post_id;
} // _fuseAdminMediaPostId ()




/**
 *  The media library's post settings. wp.media.model and wp.media.view share the
 *  one object, so the ID set here covers uploads, attachment saves and gallery
 *  ordering. Missing on screens that have not loaded the media library.
 */
function _fuseAdminMediaPostSettings () {
    if (typeof wp == 'undefined' || !wp.media || !wp.media.model || !wp.media.model.settings) {
        return null;
    } // if ()

    return wp.media.model.settings.post || null;
} // _fuseAdminMediaPostSettings ()
