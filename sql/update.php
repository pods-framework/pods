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
	$pod_types = pods_query("SELECT * FROM `@wp_pod_types`");

	foreach ($pod_types as $pod_type) {
		$field_rows = pods_query("SELECT * FROM `@wp_pod_fields` WHERE `datatype` = {$pod_type['id']}");
		$fields = array();
	}
}
