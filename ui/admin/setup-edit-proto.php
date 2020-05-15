<?php
wp_enqueue_style( 'wp-edit-post' );
$api = pods_api();

/** @noinspection PhpUndefinedVariableInspection */
$pod = $api->load_pod( [ 'id' => $obj->id ] );

//--! Todo: prototyping only
$all_field_names = [];

// Fields
$pod_fields = [];
foreach ( $pod['fields'] as $field_name => $field_data ) {
	array_push( $all_field_names, $field_name );
	$field_options = array_merge( $field_data['options'], $field_data->get_args() );
	unset( $field_options['options'] );
	$pod_fields[ $field_name ] = $field_options;
}

$setup_edit_options = PodsInit::$admin->admin_setup_edit_options( $pod );
$setup_edit_tabs    = PodsInit::$admin->admin_setup_edit_tabs( $pod );

// Iterate through the defined tabs
$ordered_tab_list = [];
$tabs_by_name     = [];
$tab_options_list = [];
$options          = [];
foreach ( $setup_edit_tabs as $tab_name => $tab_title_text ) {
	$tab_option_list = [];
	array_push( $ordered_tab_list, $tab_name ); // Ordered array of names only

	// Loop through the options for this tab
	if ( isset( $setup_edit_options[ $tab_name ] ) ) {
		foreach ( $setup_edit_options[ $tab_name ] as $tab_option_name => $tab_option_values ) {
			$tab_option_values = (array) $tab_option_values;
			array_push( $tab_option_list, $tab_option_name ); // Ordered array of names only

			$value = isset( $tab_option_values['default'] ) ? $tab_option_values['default'] : "";
			if ( isset( $tab_option_values['value'] ) && 0 < strlen( $tab_option_values['value'] ) ) {
				$value = $tab_option_values['value'];
			} else {
				//--!! 'label' is on the Pod itself but the rest are under 'options'?
				$value = pods_v( $tab_option_name, $pod, $value );
				$value = pods_v( $tab_option_name, $pod['options'], $value );
			}

			$tab_option_values['value'] = $value;
			$tab_option_values['name']  = $tab_option_name;

			$options[ $tab_option_name ] = $tab_option_values;
		}
	}

	$tabs_by_name[ $tab_name ]     = [
		'name'      => $tab_name,
		'titleText' => $tab_title_text,
	];
	$tab_options_list[ $tab_name ] = $tab_option_list;
}

$dummy_group_list       = [
	'Test Group 1',
	'Test Group 2',
	'Test Group 3',
	'Test Group 4',
	'Test Group 5',
	'Test Group 6',
	'Test Group 7',
	'Test Group 8',
];
$dummy_group_field_list = [
	'Test Group 1' => $all_field_names,
	'Test Group 2' => [],
	'Test Group 3' => [],
	'Test Group 4' => [],
	'Test Group 5' => [],
	'Test Group 6' => [],
	'Test Group 7' => [],
	'Test Group 8' => [],
];
$dummy_groups           = [
	'Test Group 1' => [
		'name' => 'Test Group 1',
	],
	'Test Group 2' => [
		'name' => 'Test Group 2',
	],
	'Test Group 3' => [
		'name' => 'Test Group 3',
	],
	'Test Group 4' => [
		'name' => 'Test Group 4',
	],
	'Test Group 5' => [
		'name' => 'Test Group 5',
	],
	'Test Group 6' => [
		'name' => 'Test Group 6',
	],
	'Test Group 7' => [
		'name' => 'Test Group 7',
	],
	'Test Group 8' => [
		'name' => 'Test Group 8',
	],
];

// Formatted data
$data = [
	'fieldType' => 'edit-pod',
	// @todo Remove this.
	'podType'   => $pod['type'],
	// @todo Remove this.
	'podMeta'   => [
		'name' => $pod['name'],
		'id'   => $pod['id'],
	],
	// @todo Remove this.
	'ui'        => [
		'tabs' => [
			'byName'         => $tabs_by_name,
			'tabList'        => $ordered_tab_list,
			'tabOptionsList' => $tab_options_list,
		],
	],
	// @todo Remove this.
	'options'   => $options,
	// @todo Remove this.
	'fields'    => $pod_fields,
	// @todo Remove this.
	'groups'    => [
		'byName'         => $dummy_groups,
		'groupList'      => $dummy_group_list,
		'groupFieldList' => $dummy_group_field_list,
		'currentPod'     => $config['currentPod'],
		'global'         => $config['global'],
	],
	'config'    => $config,
];

$data = wp_json_encode( $data, JSON_HEX_TAG );
?>
<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>
	<script type="application/json" class="pods-dfv-field-data"><?php echo $data; ?></script>
</div>
