<div id="plupload-upload-ui" class="hide-if-no-js">
     <div id="drag-drop-area">
       <div class="drag-drop-inside">
        <p class="drag-drop-info"><?php _e('Drop files here'); ?></p>
        <p><?php _ex('or', 'Uploader: Drop files here - or - Select Files'); ?></p>
        <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button" /></p>
      </div>
     </div>
  </div>

  <?php

  $plupload_init = array(
    'runtimes'            => 'html5,silverlight,flash,html4',
    'browse_button'       => 'plupload-browse-button',
    'container'           => 'plupload-upload-ui',
    'drop_element'        => 'drag-drop-area',
    'file_data_name'      => 'async-upload',
    'multiple_queues'     => true,
    'max_file_size'       => wp_max_upload_size().'b',
    'url'                 => admin_url('admin-ajax.php'),
    'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
    'filters'             => array(array('title' => __('Allowed Files', 'pods'), 'extensions' => '*')),
    'multipart'           => true,
    'urlstream_upload'    => true,
    'multipart_params'    => array(
      '_ajax_nonce' => wp_create_nonce('photo-upload'),
      'action'      => 'media_upload_test',
    ),
  );

  $plupload_init = apply_filters('plupload_init', $plupload_init); ?>

  <script type="text/javascript">

    jQuery(document).ready(function($){

      var uploader = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

      uploader.bind('Init', function(up){
        var uploaddiv = $('#plupload-upload-ui');

        if(up.features.dragdrop){
          uploaddiv.addClass('drag-drop');
            $('#drag-drop-area')
              .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
              .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

        }else{
          uploaddiv.removeClass('drag-drop');
          $('#drag-drop-area').unbind('.wp-uploader');
        }
      });

      uploader.init();

      uploader.bind('FilesAdded', function(up, files){
        var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

        plupload.each(files, function(file){
          if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){

          }else{
//              file - id, name, percent, status, loaded, size
              $('#drag-drop-area').append(file.name);
          }
        });

        up.refresh();
        up.start();
      });

      uploader.bind('FileUploaded', function(up, file, response) {

//        console.log(response)
      });

    });

  </script>
  <?php


// handle uploaded file here
add_action('wp_ajax_media_upload_test', 'pods_media_upload_test');

function pods_media_upload_test() {

  #check_ajax_referer('photo-upload');

  // you can use WP's wp_handle_upload() function:
  $status = wp_handle_upload($_FILES['async-upload']);

  // and output the results or something...
  echo 'Uploaded to: '.$status['url'];

  die();
}