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
			$answer = $name
	}
	return $answer;
}

function migrate_pods() {
	// Grab old pods and fields, and create new ones via the API
	$pods_api = new PodsAPI;
	$pod_types = pods_query("SELECT * FROM `@wp_pod_types`");

	foreach ($pod_types as $pod_type) {
		$field_rows = pods_query("SELECT * FROM `@wp_pod_fields` WHERE `datatype` = {$pod_type['id']}");
		$fields = array();

		foreach ($field_rows as $row) {
			$field_params = array(
				'name' => $row['name'],
				'label' => $row['label'],
				'type' => translate_field_type($row['coltype']),
				'weight' => $row['weight'],
				'required' => $row['required'],
			);

			if ($row['coltype'] == 'pick') {
				$field_params['pick_val'] = $row['pickval'];
			}

			$fields[] = $field_params;
			// @todo Finish this function
		}
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
			'name' => $tpl['name'],
			'code' => $tpl['code'],
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
			'name' => $hlpr['name'],
			'helper_type' => $hlpr['helper_type'],
			'phpcode' => $hlpr['phpcode'],
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
	migrate_pods();
}
