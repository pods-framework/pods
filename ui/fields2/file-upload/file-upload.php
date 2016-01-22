<?php
/**
 * @var $form_field_type string
 * @var $options         array
 * @var $field_type      string
 */
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );

wp_enqueue_script( 'backbone' );
wp_enqueue_script( 'marionette', PODS_URL . 'ui/js/marionette/backbone.marionette.min.js', array( 'backbone' ), '2.4.4', true );

wp_enqueue_script( 'backbone.babysitter', PODS_URL . 'ui/js/marionette/backbone.babysitter.min.js', array( 'backbone' ), '0.1.10', true );
wp_enqueue_script( 'backbone.wreqr', PODS_URL . 'ui/js/marionette/backbone.wreqr.min.js', array( 'backbone' ), '1.0.2', true );
//wp_enqueue_script( 'backbone.radio', PODS_URL . 'ui/js/marionette/backbone.radio.min.js', array( 'backbone' ), '1.0.2', true );
//wp_enqueue_script( 'marionette.radio.shim', PODS_URL . 'ui/js/marionette/marionette.radio.shim.js', array( 'marionette', 'backbone.radio' ), '1.0.2', true );

wp_enqueue_script( 'pods-ui', PODS_URL . 'ui/js/pods-ui.js', array(
	'backbone.wreqr',
	'marionette'
), PODS_VERSION, true );
wp_enqueue_script( 'ui/js/pods-ui-ready', PODS_URL . 'ui/js/pods-ui-ready.js', array( 'pods-ui' ), PODS_VERSION, true );

wp_enqueue_script( 'file-upload-model', PODS_URL . 'ui/fields2/file-upload/models/file-upload-model.js', array( 'pods-ui' ), PODS_VERSION, true );
wp_enqueue_script( 'file-upload-list', PODS_URL . 'ui/fields2/file-upload/views/file-upload-list.js', array( 'pods-ui' ), PODS_VERSION, true );
wp_enqueue_script( 'file-upload-form', PODS_URL . 'ui/fields2/file-upload/views/file-upload-form.js', array( 'pods-ui' ), PODS_VERSION, true );
wp_enqueue_script( 'file-upload-layout', PODS_URL . 'ui/fields2/file-upload/views/file-upload-layout.js', array(
	'file-upload-model',
	'file-upload-form',
	'file-upload-list'
), PODS_VERSION, true );

$file_limit = 1;
if ( 'multi' == pods_v( $form_field_type . '_format_type', $options, 'single' ) ) {
	$file_limit = (int) pods_v( $form_field_type . '_limit', $options, 0 );
}

$button_text = pods_v( $form_field_type . '_add_button', $options, __( 'Add File', 'pods' ) );

if ( empty( $value ) ) {
	$value = array();
} else {
	$value = (array) $value;
}

$attributes = PodsForm::merge_attributes( array(), $name, $form_field_type, $options );
$attributes = array_map( 'esc_attr', $attributes );

$model_data = array();
foreach ( $value as $id ) {
	$attachment = get_post( $id );
	if ( empty( $attachment ) ) {
		continue;
	}

	$thumb = wp_get_attachment_image_src( $id, 'thumbnail', true );
	$title = $attachment->post_title;
	if ( 0 == $title_editable ) {
		$title = basename( $attachment->guid );
	}

	$link = wp_get_attachment_url( $attachment->ID );

	$model_data[] = array(
		'id'   => $id,
		'name' => $title,
		'icon' => $thumb[ 0 ],
		'link' => $link
	);
}

$field_meta = array(
	'attributes' => array(
		'id'         => $attributes[ 'id' ],
		'class'      => $attributes[ 'class' ],
		'name'       => $attributes[ 'name' ],
		'name-clean' => $attributes[ 'data-name-clean' ]
	)
);
include_once PODS_DIR . 'ui/fields2/file-upload/templates/file-upload-tpl.php';
include_once PODS_DIR . 'ui/fields2/file-upload/PodsFieldData.php';

$field_data = new PodsUIFieldData( $field_type, array( 'model_data' => $model_data, 'field_meta' => $field_meta ) );
?>
<div class="pods-ui-field"><?php $field_data->emit_script(); ?></div>
