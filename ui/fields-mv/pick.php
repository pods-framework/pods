<?php
/**
 * @var $form_field_type string
 * @var $options         array
 * @var $field_type      string
 * @var $value           array
 * @var $id              string
 */
wp_enqueue_style( 'pods-flex' );
wp_enqueue_script( 'pods-mv-fields' );

$data = (array) pods_v( 'data', $options, array(), null, true );
unset ( $options[ 'data' ] );
$options[ 'item_id' ] = (int) $id;

$options[ 'supports_thumbnails' ] = null;
$options[ 'pick_object' ]         = ( empty( $options[ 'pick_object' ] ) ) ? '' : $options[ 'pick_object' ];

// Todo: Should probably be set where the data is set
// optgroups
if ( is_array( $data ) && is_array( current( $data ) ) ) {
	$options[ 'optgroup' ] = true;

	foreach ( $data as $this_key => $this_value ) {
		$model_data[] = array(
			'label'      => $this_key,
			'collection' => PodsField_Pick::build_model_data( $this_value, $value, $options )
		);
	}
} else { // Ungrouped (no optgroups)
	$options[ 'optgroup' ] = false;
	$model_data            = PodsField_Pick::build_model_data( $data, $value, $options );
}

$attributes = PodsForm::merge_attributes( array(), $name, $form_field_type, $options );
$attributes = array_map( 'esc_attr', $attributes );
$field_meta = array(
	'htmlAttr' => array(
		'id'         => $attributes[ 'id' ],
		'class'      => $attributes[ 'class' ],
		'name'       => $attributes[ 'name' ],
		'name_clean' => $attributes[ 'data-name-clean' ]
	),
	'fieldConfig'      => $options
);

// Set the file name and args based on the content type of the relationship
switch ( $options[ 'pick_object' ] ) {
	case 'post_type':
		$file_name  = 'post-new.php';
		$query_args = array(
			'post_type' => $options[ 'pick_val' ],
		);
		break;

	case 'taxonomy':
		$file_name  = 'edit-tags.php';
		$query_args = array(
			'taxonomy' => $options[ 'pick_val' ],
		);
		break;

	case 'user':
		$file_name  = 'user-new.php';
		$query_args = array();
		break;

	case 'pod':
		$file_name  = 'admin.php';
		$query_args = array(
			'page'   => 'pods-manage-' . $options[ 'pick_val' ],
			'action' => 'add'
		);
		break;

	// Something unsupported
	default:
		$file_name  = '';
		$query_args = array();
		break;
}

// Add args we always need
$query_args = array_merge(
	$query_args,
	array(
		'pods_modal' => '1', // @todo: Replace string literal with defined constant
	)
);

$iframe_src = '';
if ( ! empty( $file_name ) ) {
	$iframe_src = add_query_arg( $query_args, admin_url( $file_name ) );
}
$field_meta[ 'fieldConfig' ][ 'iframe_src' ] = $iframe_src;

// Assemble the URL
$url = add_query_arg( $query_args, admin_url( $file_name ) );

include_once PODS_DIR . 'classes/PodsMVFieldData.php';
$mvdata     = array(
	'fieldData'   => $model_data,
	'fieldConfig' => $field_meta[ 'fieldConfig' ],
	'htmlAttr'    => $field_meta[ 'htmlAttr' ]
);
$field_data = new PodsMVFieldData( $field_type, $mvdata );
?>
<div class="pods-form-ui-field">
	<?php $field_data->emit_script(); ?>
</div>
