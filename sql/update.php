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

if (version_compare($pods_version, '2.0.0', '<')) {
    // handle primary changes (don't process larger tables automatically)

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
		}

		
	}
}
