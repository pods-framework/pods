<?php

function translate_field_type($name) {
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

function migrate_pods() {
	// Grab old pods and fields, and create new ones via the API
	$api = new PodsAPI;
	$pod_types = pods_query("SELECT * FROM `@wp_pod_types`");

	foreach ($pod_types as $pod_type) {
		$field_rows = pods_query("SELECT * FROM `@wp_pod_fields` WHERE `datatype` = {$pod_type->id}");
		$fields = array();

		foreach ($field_rows as $row) {
			$field_params = array(
				'name' => $row->name,
				'label' => $row->label,
				'type' => translate_field_type($row->coltype),
				'weight' => $row->weight,
				'required' => $row->required,
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
	}
}

function migrate_templates() {
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

function migrate_helpers() {
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

function migrate_pages() {
	$api = new PodsAPI;
	$results = array();
	$pages = pods_query("SELECT * FROM `@wp_pod_pages`");

	foreach ($pages as $page) {
		$results[] = $api->save_page($page);
	}

	return $results;
}

if (version_compare($pods_version, '2.0.0', '<')) {
    // handle primary changes (don't process larger tables automatically)
	// $pages = migrate_pages();
	// $helpers = migrate_helpers();
	// $templates = migrate_templates();
	// migrate_pods();
}
