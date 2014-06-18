<?php

add_action( 'wp_ajax_pq_loadpod', 'pq_loadpod' );

function pq_loadpod($podname = false) {
	if(!empty($_POST['pod_reference']['pod'])){
		$podname = $_POST['pod_reference']['pod'];
	}
	if(!empty($_POST['pod'])){
		$podname = $_POST['pod'];

	}
	$fields = array('No reference Pod selected');
	if(!empty($podname)){
		$pod = pods( $podname );
		$fields = array();
		foreach( $pod->pod_data['object_fields'] as $name=>$field ){
			$fields[] = $name;
		}
		$pod_fields = $pod->fields();
		if( post_type_supports( $podname, 'thumbnail' ) ){
			$fields[] = 'post_thumbnail';
			$fields[] = 'post_thumbnail_url';
			$sizes = get_intermediate_image_sizes();
			foreach( $sizes as &$size){
				$fields[] = 'post_thumbnail.'.$size;
				$fields[] = 'post_thumbnail_url.'.$size;
			}
		}
		$fields = array_merge( $fields, pq_tunnel_pod_field( $pod_fields ) );
	}
	if(!empty($_POST['pod_reference']['pod']) || !empty($_POST['pod'])){
		header("Content-Type:application/json");
		echo json_encode($fields);
		die;
	}
	return $fields;
}

function pq_tunnel_pod_field( $fields, $prefix = null ){

	$out = array();
	// return out if fields are empty
	if(empty($fields)){
		return $out;
	}
	foreach($fields as $name=>$field){
		$out[] = $prefix . $name;
		if($field['type'] === 'file' && $field['options']['file_uploader'] == 'attachment'){

		$out[] = $prefix . $name .'._src';
		$out[] = $prefix . $name .'._img';

			$sizes = get_intermediate_image_sizes();
			foreach( $sizes as &$size){
				$out[] = $prefix . $name . '._src.'.$size;
			}
			if( 'multi' != $field['options']['file_format_type']){
				foreach( $sizes as &$size){
					$out[] = $prefix . $name . '._src_relative.'.$size;
				}
				foreach( $sizes as &$size){
					$out[] = $prefix . $name . '._src_schemeless.'.$size;
				}
			}
			foreach( $sizes as &$size){
				$out[] = $prefix . $name . '._img.'.$size;
			}
		}
		if( !empty( $field['table_info'] ) ){
			if( !empty( $field['table_info']['pod'] ) ){
				if( false === strpos( $prefix, $name . '.' ) ){

					$pod = pods( $field['table_info']['pod']['name'] );
					// only tunnel in if there are object fields
					if(!empty($field['table_info']['object_fields'])){
						$out = array_merge( $out, pq_tunnel_pod_field( $field['table_info']['object_fields'], $prefix . $name . '.' ) );
					}
					if( post_type_supports( $field['table_info']['pod']['name'], 'thumbnail' ) ){
						$out[] = 'post_thumbnail';
						$out[] = 'post_thumbnail_url';
						$sizes = get_intermediate_image_sizes();
						foreach( $sizes as &$size){
							$out[] = 'post_thumbnail.'.$size;
							$out[] = 'post_thumbnail_url.'.$size;
						}
					}
					$pod_fields = $pod->fields();
					$out = array_merge( $out, pq_tunnel_pod_field( $pod_fields, $prefix . $name . '.') );
				}
			}else{
				
				if(!empty($field['table_info']['object_fields'])){
				
					$out = array_merge( $out, pq_tunnel_pod_field( $field['table_info']['object_fields'], $prefix . $name . '.') );
				
				}

			}
		}
	}

	return $out;
}

