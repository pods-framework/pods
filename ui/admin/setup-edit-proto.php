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

$setup_edit_options = PodsInit::$admin->admin_setup_edit_options( $pod );
$setup_edit_tabs    = PodsInit::$admin->admin_setup_edit_tabs( $pod );

$tab_list = array();
$tabs     = array();
$options  = array();
foreach ( $setup_edit_tabs as $tab_name => $tab_title_text ) {
	$tab_options = array();
	array_push( $tab_list, $tab_name );

	if ( isset( $setup_edit_options[ $tab_name ] ) ) {
		foreach ( $setup_edit_options[ $tab_name ] as $option_name => $this_option ) {

			$value = isset( $this_option[ 'default' ] ) ? $this_option[ 'default' ] : "";
			if ( isset( $this_option[ 'value' ] ) && 0 < strlen( $this_option[ 'value' ] ) ) {
				$value = $this_option[ 'value' ];
			} else {
				//--!! 'label' is on the Pod itself but the rest are under 'options'?
				$value = pods_v( $option_name, $pod, $value );
				$value = pods_v( $option_name, $pod[ 'options' ], $value );
			}
			$this_option[ 'value' ] = $value;
			$this_option[ 'name' ]  = $option_name;

			$options[ $option_name ] = $this_option;

			array_push( $tab_options, $option_name );
		}
	}

	$tabs[ $tab_name ] = array(
		'name'      => $tab_name,
		'titleText' => $tab_title_text,
		'options'   => $tab_options
	);
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
		'tabs'    => array(
			'byName'  => $tabs,
			'orderedList' => $tab_list,
		),
		'options' => $options,
	),
	'fields'    => $pod_fields,
);
$data = wp_json_encode( $data, JSON_HEX_TAG );
?>
<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>
	<script type="application/json" class="pods-dfv-field-data"><?php echo $data; ?></script>
</div>
