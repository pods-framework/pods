<?php
/**
 * @var $form_field_type string
 * @var $options         array
 * @var $field_type      string
 * @var $value
 */
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );

wp_enqueue_script( 'backbone' );
wp_enqueue_script( 'marionette', PODS_URL . 'ui/js/marionette/backbone.marionette.js', array( 'backbone' ), '2.4.4', true );

wp_enqueue_script( 'backbone.babysitter', PODS_URL . 'ui/js/marionette/backbone.babysitter.min.js', array( 'backbone' ), '0.1.10', true );
wp_enqueue_script( 'backbone.radio', PODS_URL . 'ui/js/marionette/backbone.radio.min.js', array( 'backbone' ), '1.0.2', true );
wp_enqueue_script( 'marionette.radio.shim', PODS_URL . 'ui/js/marionette/marionette.radio.shim.js', array(
	'marionette',
	'backbone.radio'
), '1.0.2', true );

wp_enqueue_script( 'pods-ui-ready', PODS_URL . 'ui/js/pods-ui-ready.min.js', array(), PODS_VERSION, true );

$data = (array) pods_v( 'data', $options, array(), null, true );
unset ( $options[ 'data' ] );
$model_data = array();
foreach ( $data as $this_id => $this_title ) {
	$model_data[] = array(
		'id'       => $this_id,
		'name'     => $this_title,
		'selected' => ( null !== $value[ $this_id ] )
	);
}

$attributes = PodsForm::merge_attributes( array(), $name, $form_field_type, $options );
$attributes = array_map( 'esc_attr', $attributes );
$field_meta = array(
	'field_attributes' => array(
		'id'         => $attributes[ 'id' ],
		'class'      => $attributes[ 'class' ],
		'name'       => $attributes[ 'name' ],
		'name_clean' => $attributes[ 'data-name-clean' ]
	),
	'field_options'    => $options
);

include_once PODS_DIR . 'classes/PodsFieldData.php';
$field_data = new PodsUIFieldData( $field_type, array( 'model_data' => $model_data, 'field_meta' => $field_meta ) );
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes[ 'class' ], 'id' => $attributes[ 'id' ] ), $name, $form_field_type, $options ); ?>>
	<?php $field_data->emit_script(); ?>
</div>
