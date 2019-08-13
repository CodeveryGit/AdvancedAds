function upload_new_img(obj) {
    var file_frame;
    var img_name  =   jQuery(obj).closest('p').find('.upload_image');

    if ( file_frame ) {
        file_frame.open();
        return;
    }

    file_frame = wp.media.frames.file_frame = wp.media(
        {
            title: 'Select File',
            button: {
                text: jQuery( this ).data( 'uploader_button_text' )
            },
            multiple: false
        }
    );

    file_frame.on('select', function() {
        attachment = file_frame.state().get('selection').first().toJSON();
        var newwurl = attachment.url.split('/wp-content');
        img_name[0].value = '/wp-content'+newwurl[1];
        file_frame.close();
        // jQuery('.upload_image').val(attachment.url);
    });

    file_frame.open();
}

function remove_image(obj) {
    var img_name;
    if (jQuery(obj).closest('p').find('.upload_image').length > 0) {
        img_name = jQuery(obj).closest('p').find('.upload_image');
    } else {
        img_name = jQuery(obj).closest('td').find('.upload_image');
    }
    if (typeof img_name != "undefined") {
        img_name.val('');
    }
}