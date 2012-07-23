<?php
/**
 * Pods 2.x Migration Script
 *
 */

function migrate_pods() {
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

function migrate_helpers() {
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

function migrate_pages() {
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

function migrate_templates() {
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
