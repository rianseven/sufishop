jQuery(document).ready(function($) {
    var cl_marketplace;

    if( jQuery('.cl_marketplace_setting_plus').length > 0 ){
        cl_marketplace_html = jQuery('.cl_marketplace_parent_button').html();
        // jQuery('.color-field').wpColorPicker();
    }

    jQuery(document).on('click', '.cl_marketplace_setting_plus', function(e){
        e.preventDefault();
        // jQuery( ".cl_marketplace_parent_button" ).append(cl_marketplace_html);
        jQuery( ".cl_marketplace_item:last" ).clone().appendTo( ".cl_marketplace_parent_button" ).find("input[type='text']").val("");
        // jQuery('.color-field').wpColorPicker();
    });

    jQuery(document).on('click', '.cl_marketplace_setting_min', function(e){
        e.preventDefault();
        if( jQuery('.cl_marketplace_item').length > 1 )
          jQuery( this ).closest( ".cl_marketplace_item" ).remove();
      });

    jQuery(document).on('click', '.cl_marketplace_button', function(e) {
        cl_marketplace = $(this);
        // cl_marketplace_url = $(this).siblings('.cl_marketplace_url');
        // cl_marketplace_img = $(this).siblings('.cl_marketplace_image');
        renderMediaUploader();
        return false;
    });

    function renderMediaUploader() {
        'use strict';
     
        var file_frame, image_data;
     
        /**
         * If an instance of file_frame already exists, then we can open it
         * rather than creating a new instance.
         */
        
        if ( undefined !== file_frame ) {
     
            file_frame.open();
            return;
     
        }
        
        file_frame = wp.media.frames.file_frame = wp.media({
            frame:    'post',
            state:    'insert',
            multiple: false
        });
     
        file_frame.on( 'insert', function() {
            var att = file_frame.state().get('selection').first().toJSON();
            var imgurl = att.url;
            
            // $(cl_marketplace).siblings('.cl_marketplace_url').val( imgurl );
            $(cl_marketplace).siblings('.cl_marketplace_url').attr('value', imgurl);
            $(cl_marketplace).siblings('.cl_marketplace_image').attr('src', imgurl);
        });
     
        // Now display the actual file_frame
        file_frame.open();
     
    }
});