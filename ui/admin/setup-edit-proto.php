<?php
wp_enqueue_style( 'wp-edit-post' );
$api = pods_api();
$pod = $api->load_pod( array( 'id' => $obj->id ) );

$pod_fields = array();
foreach ( $pod[ 'fields' ] as $field_name => $field_data ) {
	$field_options = array_merge( $field_data[ 'options' ], $field_data);
	unset( $field_options[ 'options' ] );
	array_push( $pod_fields,  $field_options );
}

$data = array(
	'podInfo' => array(
		'name'   => $pod[ 'name' ],
		'id'     => $pod[ 'id' ],
		'type'   => $pod[ 'type' ],
		'fields' => $pod_fields

	),
	'fieldType'   => 'edit-pod',
	'fieldConfig' => array(
		'nonce'   => wp_create_nonce( 'pods-save_pod' )
	)
);
$data = wp_json_encode( $data, JSON_HEX_TAG );
?>
<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>
	<script type="application/json" class="pods-dfv-field-data"><?php echo $data; ?></script>
</div>
