jQuery(document).ready(function($){

    var pods_file_context = false;      // tracks whether or not we've got a thickbox displayed in our context
    var pods_file_thickbox_modder;      // stores our interval for making necessary changes to thickbox content

    // init sortable
    $('ul.pods-files').each(function(){
        if($(this).children().length){
            $(this).sortable({
                containment: 'parent'
            })
        }
    });

    // hook delete links
    $('.pods-metabox').on('click','li.pods-file-delete',function(){
        var podsfile = $(this).parent().parent();
        podsfile.slideUp(function(){

            // check to see if this was the only entry
            if(podsfile.parent().children().length==1){     // 1 because we haven't removed our target yet
                podsfile.parent().hide();
            }

            // remove the entry
            podsfile.remove();
        });
    });

    // hook the add link
    $('.pods-metabox').on('click', 'a.pods-file-add', function(e){
        e.preventDefault();
        var trigger = $(this);
        var href = trigger.attr('href'), width = $(window).width(), H = $(window).height(), W = ( 720 < width ) ? 720 : width;
        if(!href) return;
        href = href.replace(/&width=[0-9]+/g, '');
        href = href.replace(/&height=[0-9]+/g, '');
        trigger.attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );

        pods_file_context = trigger.prev();
        pods_file_thickbox_modder = setInterval(function(){
            if(pods_file_context) modify_thickbox();
        },500);

        tb_show('Attach a file', e.target.href, false);
        return false;
    });

    // handle our thickbox mods
    function modify_thickbox(){

        var pods_thickbox = $('#TB_iframeContent').contents();

        pods_thickbox.find('td.savesend input').unbind('click').click(function(e){
            // grab our meta as per the Media library
            var wp_media_meta       = $(this).parent().parent().parent();
            var wp_media_title      = wp_media_meta.find('tr.post_title td.field input').val();
            var wp_media_caption    = wp_media_meta.find('tr.post_excerpt td.field input').val();
            var wp_media_id         = wp_media_meta.find('td.imgedit-response').attr('id').replace('imgedit-response-','');
            var wp_media_thumb      = wp_media_meta.parent().find('img.thumbnail').attr('src');

            // use the data we found to form a new Pods file entry and append it to the DOM
            var source   = jQuery('#pods-file-template').html();
            var template = Handlebars.compile(source);
            pods_file_context.append(template({id:wp_media_id,name:wp_media_title,icon:wp_media_thumb}));

            return false;
        });

        // update button
        if(pods_thickbox.find('.media-item .savesend input[type=submit], #insertonlybutton').length){
            pods_thickbox.find('.media-item .savesend input[type=submit], #insertonlybutton').val('Select');
        }

        // hide the URL tab
        if(pods_thickbox.find('#tab-type_url').length){
            pods_thickbox.find('#tab-type_url').hide();
        }

        // we need to ALWAYS get the fullsize since we're retrieving the guid
        // if the user inserts an image somewhere else and chooses another size, everything breaks, so we'll force it
        if(pods_thickbox.find('tr.post_title').length){
            pods_thickbox.find('tr.image-size input[value="full"]').prop('checked', true);
            pods_thickbox.find('tr.image-size,tr.post_content,tr.url,tr.align,tr.submit>td>a.del-link').hide();
        }

        // was the thickbox closed?
        if(pods_thickbox.length==0 && pods_file_context){
            clearInterval(pods_file_thickbox_modder);
            pods_file_context = false;
        }
    }

});
