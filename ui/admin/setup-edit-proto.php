<?php
$api = pods_api();
$pod = $api->load_pod( array( 'id' => $obj->id ) );

$pod_fields = array();
foreach ( $pod[ 'fields' ] as $field_name => $field_data ) {
	$pod_fields[ $field_name ] = $field_data[ 'options' ];
	$pod_fields[ $field_name ][ 'name' ] = $field_name;
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
