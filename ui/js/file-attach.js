var pods_file_info = {
    asset: null,
    hijacked: false,
    context: null
};

// delete
jQuery('span.pods-file-remove img').live('click',function(){
    pods_file = jQuery(this).parent().parent();
    pods_file.height(pods_file.height()).css('minHeight','0').slideUp(function(){
        pods_file.remove();
    });
    return false;
});

// add
jQuery('p.pods-add-file a').live('click',function(event){

    // track which file column we're dealing with here
    jQuery('div.pods-file-context').removeClass('pods-file-context');
    jQuery(this).parent().parent().addClass('pods-file-context').addClass('current');

    var pods_href = jQuery(this).attr('href'), pods_width = jQuery(window).width(), pods_H = jQuery(window).height(), pods_W = ( 720 < pods_width ) ? 720 : pods_width;

    if ( ! pods_href ) return;

    pods_href = pods_href.replace(/&width=[0-9]+/g, '');
    pods_href = pods_href.replace(/&height=[0-9]+/g, '');

    jQuery(this).attr('href', pods_href + '&width=' + ( pods_W - 80 ) + '&height=' + ( pods_H - 85 ));

    pods_file_info.hijacked = true;
    pods_file_thickbox = setInterval(pods_thickbox_handler, 500);

    tb_show('Add a file', event.target.href, false);

    return false;
});


function pods_thickbox_handler(){
    if(pods_file_info.hijacked){
        // need to add our own Attach (single) button
        if(jQuery('#TB_iframeContent').contents().find('td.savesend').length){
            jQuery('#TB_iframeContent').contents().find('td.savesend').each(function(){
                if(jQuery(this).find('input.pods-add-button').length==0){
                    jQuery(this).find('input').hide();
                    jQuery(this).prepend('<input type="submit" name="pods_add_trigger" class="pods-add-button button" value="Add" />');
                }
            });
        }
        // need to handle the click event
        jQuery('#TB_iframeContent').contents().find('td.savesend input.pods-add-button').unbind('click').click(function(e){
            pods_media_parent = jQuery(this).parent().parent().parent();
            jQuery(this).after(' <span class="pods-attached">Added! Choose another or close this box.</span>');
            pods_media_id = pods_media_parent.find('td.imgedit-response').attr('id').replace('imgedit-response-','');
            pods_media_name = pods_media_parent.parent().prev().find('span.title').text();
            pods_media_thumb = pods_media_parent.parent().parent().find('img.pinkynail').attr('src');
            // add the file on the edit screen
            pods_add_file(pods_media_id,pods_media_name,pods_media_thumb);
            // get rid of the message
            pods_media_parent.find('span.pods-attached').delay(3000).fadeOut('fast');
            return false;
        });
        // now we can handle the button
        if(jQuery('#TB_iframeContent').contents().find('.media-item .savesend input[type=submit], #insertonlybutton').length){
            jQuery('#TB_iframeContent').contents().find('.media-item .savesend input[type=submit], #insertonlybutton').val('Add');
        }
        if(jQuery('#TB_iframeContent').contents().find('#tab-type_url').length){
            jQuery('#TB_iframeContent').contents().find('#tab-type_url').hide();
        }
        if(jQuery('#TB_iframeContent').contents().find('tr.post_title').length){
            // we need to ALWAYS get the fullsize since we're retrieving the guid
            // if the user inserts an image somewhere else and chooses another size, everything breaks
            jQuery('#TB_iframeContent').contents().find('tr.image-size input[value="full"]').prop('checked', true);
            jQuery('#TB_iframeContent').contents().find('tr.post_title,tr.image_alt,tr.post_excerpt,tr.image-size,tr.post_content,tr.url,tr.align,tr.submit>td>a.del-link').hide();
        }
    }
    if(jQuery('#TB_iframeContent').contents().length==0&&pods_file_info.hijacked){
        // the thickbox was closed
		pods_file_list = jQuery('.pods-file-context.current ul')
        clearInterval(pods_file_thickbox);
        pods_file_info.hijacked = false;
		pods_file_list.parent().removeClass('current');
    }
}


function pods_add_file(pods_media_id,pods_media_name,pods_media_thumb){
    pods_file_list = jQuery('.pods-file-context.current ul');
    pods_file_field_name = pods_file_list.parent().attr('id').replace('field-pods-field-','');

    pods_file_markup = '<li class="media-item">';
    pods_file_markup += '<span class="pods-file-reorder"><img src="'+PODS_URL+'ui/images/handle.gif" alt="Drag to reorder" /></span>';
    pods_file_markup += '<span class="pods-file-thumb"><span>';
    pods_file_markup += '<img class="pinkynail" src="'+pods_media_thumb+'" alt="Thumbnail" />';
    pods_file_markup += '<input name="pods-field-'+pods_file_field_name+'[]" type="hidden" value="' + pods_media_id + '" />';
    pods_file_markup += '</span></span>';
    pods_file_markup += '<span class="pods-file-name">'+pods_media_name+'</span>';
    pods_file_markup += '<span class="pods-file-remove"><img class="pods-icon-minus" src="'+PODS_URL+'ui/images/del.png" alt="Remove" /></span>';
    pods_file_markup += '</li>';

    // append it
    pods_file_list.append(pods_file_markup);
}


// sortable
function pods_init_file_sortability() {
    jQuery('.pods-files').sortable({
            containment: 'parent'
        });
}
jQuery(document).ready(function(){
    pods_init_file_sortability();
});
