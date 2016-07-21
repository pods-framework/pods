<?php
/**
 * @var $form_field_type string
 * @var $options         array
 * @var $field_type      string
 */
wp_enqueue_style( 'pods-flex' );
wp_enqueue_script( 'pods-mv-fields' );

$file_limit = 1;
if ( 'multi' == pods_v( $form_field_type . '_format_type', $options, 'single' ) ) {
	$file_limit = (int) pods_v( $form_field_type . '_limit', $options, 0 );
}

$limit_file_type = pods_v( $form_field_type . '_type', $options, 'images' );

$title_editable = pods_v( $form_field_type . '_edit_title', $options, 0 );
$linked         = pods_v( $form_field_type . '_linked', $options, 0 );

$button_text = pods_v( $form_field_type . '_add_button', $options, __( 'Add File', 'pods' ) );

// Handle default template setting
$file_field_template = pods_v( $form_field_type . '_field_template', $options, 'rows', true );

if ( 'images' != $limit_file_type ) {
	$file_field_template = 'rows';
}

$options[ $form_field_type . '_field_template' ] = $file_field_template;

if ( empty( $value ) ) {
	$value = array();
} else {
	$value = (array) $value;
}

$attributes = PodsForm::merge_attributes( array(), $name, $form_field_type, $options );

// Add template class
$attributes['class'] .= ' pods-field-template-' . $file_field_template;

$attributes = array_map( 'esc_attr', $attributes );
$css_id     = $attributes[ 'id' ];

$model_data = array();
foreach ( $value as $id ) {
	$attachment = get_post( $id );
	if ( empty( $attachment ) ) {
		continue;
	}

	$icon = '';
	$edit_link = get_edit_post_link( $attachment->ID, 'raw' );
	$link      = get_permalink( $attachment->ID );
	$download  = wp_get_attachment_url( $attachment->ID );

	$thumb = wp_get_attachment_image_src( $id, 'thumbnail', true );

	if ( ! empty( $thumb[0] ) ) {
		$icon = $thumb[0];
	}

	$title = $attachment->post_title;

	if ( 0 == $title_editable ) {
		$title = basename( $attachment->guid );
	}


	$model_data[] = array(
		'id'        => $id,
		'icon'      => $icon,
		'name'      => $title,
		'edit_link' => $edit_link,
		'link'      => $link,
		'download'  => $download,
	);
}

if ( 'images' == $limit_file_type ) {
	$limit_types      = 'image';
	$limit_extensions = 'jpg,jpeg,png,gif';
} elseif ( 'video' == $limit_file_type ) {
	$limit_types      = 'video';
	$limit_extensions = 'mpg,mov,flv,mp4';
} elseif ( 'audio' == $limit_file_type ) {
	$limit_types      = 'audio';
	$limit_extensions = 'mp3,m4a,wav,wma';
} elseif ( 'text' == $limit_file_type ) {
	$limit_types      = 'text';
	$limit_extensions = 'txt,rtx,csv,tsv';
} elseif ( 'any' == $limit_file_type ) {
	$limit_types      = '';
	$limit_extensions = '*';
} else {
	$limit_types = $limit_extensions = pods_v( $form_field_type . '_allowed_extensions', $options, '', true );
}
$limit_types      = trim( str_replace( array( ' ', '.', "\n", "\t", ';' ), array(
	'',
	',',
	',',
	','
), $limit_types ), ',' );
$limit_extensions = trim( str_replace( array( ' ', '.', "\n", "\t", ';' ), array(
	'',
	',',
	',',
	','
), $limit_extensions ), ',' );
$mime_types       = wp_get_mime_types();

if ( ! in_array( $limit_file_type, array( 'images', 'video', 'audio', 'text', 'any' ) ) ) {
	$new_limit_types = array();

	$limit_types = explode( ',', $limit_types );

	foreach ( $limit_types as $k => $limit_type ) {
		if ( isset( $mime_types[ $limit_type ] ) ) {
			$mime = explode( '/', $mime_types[ $limit_type ] );
			$mime = $mime[ 0 ];

			if ( ! in_array( $mime, $new_limit_types ) ) {
				$new_limit_types[] = $mime;
			}
		} else {
			$found = false;

			foreach ( $mime_types as $type => $mime ) {
				if ( false !== strpos( $type, $limit_type ) ) {
					$mime = explode( '/', $mime );
					$mime = $mime[ 0 ];

					if ( ! in_array( $mime, $new_limit_types ) ) {
						$new_limit_types[] = $mime;
					}
					$found = true;
				}
			}

			if ( ! $found ) {
				$new_limit_types[] = $limit_type;
			}
		}
	}

	if ( ! empty( $new_limit_types ) ) {
		$limit_types = implode( ',', $new_limit_types );
	}
}

$options[ 'file_limit' ]       = $file_limit; // @todo Why is the $options version of the story wrong?
$options[ 'limit_types' ]      = $limit_types;
$options[ 'limit_extensions' ] = $limit_extensions;

// @todo: plupload specific options need accommodation
if ( 'plupload' == $options[ 'file_uploader' ] ) {
	wp_enqueue_script( 'plupload-all' );

	$uid = @session_id();

	if ( is_user_logged_in() ) {
		$uid = 'user_' . get_current_user_id();
	}

	$uri_hash    = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );
	$field_nonce = wp_create_nonce( 'pods_upload_' . ( ! is_object( $pod ) ? '0' : $pod->pod_id ) . '_' . $uid . '_' . $uri_hash . '_' . $options[ 'id' ] );

	$options[ 'plupload_init' ] = array(
		'runtimes'            => 'html5,silverlight,flash,html4',
		'url'                 => admin_url( 'admin-ajax.php?pods_ajax=1', 'relative' ),
		'file_data_name'      => 'Filedata',
		'multiple_queues'     => false,
		'max_file_size'       => wp_max_upload_size() . 'b',
		'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
		'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
		'filters'             => array( array( 'title' => __( 'Allowed Files', 'pods' ), 'extensions' => '*' ) ),
		'multipart'           => true,
		'urlstream_upload'    => true,
		'multipart_params'    => array(
			'_wpnonce' => $field_nonce,
			'action'   => 'pods_upload',
			'method'   => 'upload',
			'pod'      => ( ! is_object( $pod ) ? '0' : $pod->pod_id ),
			'field'    => $options[ 'id' ],
			'uri'      => $uri_hash
		),
	);
}

$field_meta = array(
	'field_attributes' => array(
		'id'         => $attributes[ 'id' ],
		'class'      => $attributes[ 'class' ],
		'name'       => $attributes[ 'name' ],
		'name_clean' => $attributes[ 'data-name-clean' ]
	),
	'field_options'    => $options
);

include_once PODS_DIR . 'classes/PodsMVFieldData.php';

// @todo Need to normalize and finalize.  Is there a potential need for subclasses or does this basically cover it?
$field_data = new PodsMVFieldData( $field_type, array( 'model_data' => $model_data, 'field_meta' => $field_meta ) );

// @todo This is the demarcation point, everything above this exists to achieve the single line below.  Everything
// upstream from here needs clean up, simplification, and refactoring
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes[ 'class' ], 'id' => $attributes[ 'id' ] ), $name, $form_field_type, $options ); ?>>
	<?php $field_data->emit_script(); ?>
</div>
