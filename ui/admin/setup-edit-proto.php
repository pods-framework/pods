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

$tab_content = PodsInit::$admin->admin_setup_edit_options( $pod );
echo
	"<script>content = "
     . wp_json_encode( $tab_content )
     . "</script>"
;


$tab_data = PodsInit::$admin->admin_setup_edit_tabs( $pod );
$tabs = array();
foreach ( $tab_data as $name => $title_text ) {
	$content = isset( $tab_content[ $name ] ) ? $tab_content[ $name ] : array();
	array_push( $tabs, array(
		'name' => $name,
		'titleText' => $title_text,
		'content' => $content
	) );
}

// Labels
$labels = array();
if ( isset( $tab_content[ 'labels' ] ) ) {
	foreach ( $tab_content[ 'labels' ] as $field_name => $option ) {
		$option[ 'name' ] = $field_name;

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
}

// Formatted data
$data = array(
	'fieldType' => 'edit-pod',
	'podType'   => $pod[ 'type' ],
	'nonce'     => wp_create_nonce( 'pods-save_pod' ),
	'podMeta'   => array(
		'name' => $pod[ 'name' ],
		'id'   => $pod[ 'id' ]
	),
	'ui'        => array(
		'tabs' => $tabs,
	),
	'fields'    => $pod_fields,
	'labels'    => $labels,
);
$data = wp_json_encode( $data, JSON_HEX_TAG );
?>
<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>
	<script type="application/json" class="pods-dfv-field-data"><?php echo $data; ?></script>
</div>
