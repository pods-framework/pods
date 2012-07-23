<?php

if (defined('PODS_DEVELOPER')) {
function pods_translate_field_type($name) {
	switch ($name) {
		case 'txt':
			$answer = "text";
			break;
		case 'desc':
			$answer = "paragraph";
			break;
		default:
			$answer = $name;
	}
	return $answer;
}

function pods_migrate_pods() {
	// Grab old pods and fields, and create new ones via the API
	$api = new PodsAPI;
	$pod_types = pods_query("SELECT * FROM `@wp_pod_types`");
	$pod_ids = array();

	foreach ($pod_types as $pod_type) {
		$field_rows = pods_query("SELECT * FROM `@wp_pod_fields` WHERE `datatype` = {$pod_type->id}");
		$fields = array();

		foreach ($field_rows as $row) {
			$field_params = array(
				'name' => $row->name,
				'label' => $row->label,
				'type' => pods_translate_field_type($row->coltype),
				'weight' => $row->weight,
				'options' => array(
					'required' => $row->required,
				),
			);

			if ($row->coltype == 'pick') {
				$field_params['pick_val'] = $row->pickval;
				$field_params['sister_field_id'] = $row->sister_field_id;
			}

			$fields[] = $field_params;
		}

		$pod_params = array(
			'name' => $pod_type->name,
			'type' => 'pod',
			'storage' => 'table',
			'fields' => $fields,
			'options' => array(
				'pre_save_helpers' => $pod_type->pre_save_helpers,
				'post_save_helpers' => $pod_type->post_save_helpers,
				'pre_delete_helpers' => $pod_type->pre_drop_helpers,
				'post_delete_helpers' => $pod_type->post_drop_helpers,
				'show_in_menu' => $pod_type->is_toplevel,
				'detail_url' => $pod_type->detail_page,
			),
		);
		$pod_id = $api->save_pod($pod_params);
		$pod_ids[] = $pod_id;
	}
	return $pod_ids;
}

function pods_migrate_templates() {
	$api = new PodsAPI;
	$templates = pods_query("SELECT * FROM `@wp_pod_templates`");
	$results = array();

	if (0 == count($templates))
		return true;

	foreach ($templates as $tpl) {
		$params = array(
			'name' => $tpl->name,
			'code' => $tpl->code,
		);

		$results[] = $api->save_template($params);
	}

	return $results;
}

function pods_migrate_helpers() {
	$api = new PodsAPI;
	$results = array();
	$helpers = pods_query("SELECT * FROM `@wp_pod_helpers`");

	foreach ($helpers as $hlpr) {
		$params = array(
			'name' => $hlpr->name,
			'helper_type' => $hlpr->helper_type,
			'phpcode' => $hlpr->phpcode,
		);

		$results[] = $api->save_helper($params);
	}

	return $results;
}

function pods_migrate_pages() {
	$api = new PodsAPI;
	$results = array();
	$pages = pods_query("SELECT * FROM `@wp_pod_pages`");

	foreach ($pages as $page) {
		$results[] = $api->save_page($page);
	}

	return $results;
}

function pods_alpha_table_exists($tbl) {
	try {
		$tbl = mysql_real_escape_string($tbl);
		$rows = pods_query("SELECT * FROM `{$tbl}` LIMIT 1");
	} catch (Exception $e) {
		$rows = false;
	}

	return $rows;
}

function pods_alpha_migrate_pods() {
	$api = new PodsAPI;
	$old_pods = pods_query( "SELECT * FROM `@wp_pods`" );
	$pod_ids = array();

	foreach ($old_pods as $pod) {
		$pod_opts = json_decode( $pod->options );
		$field_rows = pods_query( "SELECT * FROM `@wp_pods_fields` where `pod_id` = {$pod->id}" );
		$fields = array();

		foreach ($field_rows as $row) {
			$field_opts = json_decode( $row->options );
			$field_params = array(
				'name' => $row->name,
				'label' => $row->label,
				'type' => $row->type,
				'pick_object' => $row->pick_object,
				'pick_val' => $row->pick_val,
				'sister_field_id' => $row->sister_field_id,
				'weight' => $row->weight,
				'options' => $field_opts,
			);

			$fields[] = $field_params;
		}

		$pod_params = array(
			'name' => $pod->name,
			'type' => $pod->type,
			'storage' => $pod->storage,
			'fields' => $fields,
			'options' => $pod_opts,
		);

		$pod_id = $api->save_pod($pod_params);
		$pod_ids[] = $pod_id;
	}
	return $pod_ids;
}

function pods_alpha_migrate_helpers() {
	$api = new PodsAPI;
	$helper_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'helper'" );
	$helper_ids = array();

	foreach ( $helper_rows as $row ) {
		$opts = json_decode( $row->options );
		$helper_params = array(
			'name' => $row->name,
			'helper_type' => $opts[ 'helper_type' ],
			'phpcode' => $opts[ 'phpcode' ],
		);

		$helper_ids[] = $api->save_helper( $helper_params );
	}
	return $helper_ids;
}

function pods_alpha_migrate_pages() {
	$api = new PodsAPI;
	$page_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page'" );
	$page_ids = array();

	foreach ( $page_rows as $row ) {
		$opts = json_decode( $row->options );
		$page_params = array(
			'uri' => $row->name,
			'phpcode' => $opts[ 'phpcode' ],
		);

		$page_ids[] = $api->save_page( $page_params );
	}
	return $page_ids;
}

function pods_alpha_migrate_templates() {
	$api = new PodsAPI;
	$tpl_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'template'" );
	$tpl_ids = array();

	foreach ( $tpl_rows as $row ) {
		$opts = json_decode( $row->options );
		$tpl_params = array(
			'name' => $row->name,
			'code' => $opts[ 'code' ],
		);

		$tpl_ids[] = $api->save_template( $tpl_params );
	}
	return $tpl_ids;
}

if (version_compare($pods_version, '2.0.0', '<')) {
	//handle primary changes (don't process larger tables automatically)

	if ( $_GET[ 'pods_upgrade_test' ] == 1 ) {
		$pages = migrate_pages();
		$helpers = migrate_helpers();
		$templates = migrate_templates();
		$pod_ids = migrate_pods();
	}

} elseif (version_compare($pods_version, '2.0.0', '>') && pods_alpha_table_exists("@wp_pods")) {

	if ( $_GET[ 'pods_upgrade_test' ] == 1 ) {
		$pages = pods_alpha_migrate_pages();
		$helpers = pods_alpha_migrate_helpers();
		$templates = pods_alpha_migrate_templates();
		$pod_ids = pods_alpha_migrate_pods();
	}

}
}
