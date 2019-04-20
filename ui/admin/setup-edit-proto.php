<?php
wp_enqueue_style( 'wp-edit-post' );
$api = pods_api();

/** @noinspection PhpUndefinedVariableInspection */
$pod = $api->load_pod( array( 'id' => $obj->id ) );

// Fields
$pod_fields = array();
foreach ( $pod[ 'fields' ] as $field_name => $field_data ) {
	$field_options = array_merge( $field_data[ 'options' ], $field_data );
	unset( $field_options[ 'options' ] );
	array_push( $pod_fields, $field_options );
}

// Labels
$tab_options = PodsInit::$admin->admin_setup_edit_options( $pod );
$labels = array();
foreach ( $tab_options[ 'labels' ] as $field_name => $option ) {
	$option['name'] = $field_name;

	$value = $option[ 'default' ];
	if ( isset( $option[ 'value' ] ) && 0 < strlen( $option[ 'value' ] ) ) {
		$value = $option[ 'value' ];
	} else {
		//--!! 'label' is on the Pod itself but the rest are under 'options'?
		$value = pods_v( $field_name, $pod, $value );
		$value = pods_v( $field_name, $pod[ 'options' ], $value );
	}
	$option[ 'value' ] = $value;

	array_push( $labels, $option );
}

// Formatted data
$data = array(
	'fieldType'   => 'edit-pod',
	'podInfo'     => array(
		'name' => $pod[ 'name' ],
		'id'   => $pod[ 'id' ],
		'type' => $pod[ 'type' ],
	),
	'fields'      => $pod_fields,
	'tabOptions'  => $tab_options,
	'labels'      => $labels,
	'fieldConfig' => array(
		'nonce' => wp_create_nonce( 'pods-save_pod' )
	)
);
$data = wp_json_encode( $data, JSON_HEX_TAG );
?>
<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>
	<script type="application/json" class="pods-dfv-field-data"><?php echo $data; ?></script>
</div>
