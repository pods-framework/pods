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

	foreach($fields as $name=>$field){
		$out[] = $prefix . $name;
		if( !empty( $field['table_info'] ) ){
			if( !empty( $field['table_info']['pod'] ) ){
				if( false === strpos( $prefix, $name . '.' ) ){

					$pod = pods( $field['table_info']['pod']['name'] );
					//$out = array_merge( $out, pq_tunnel_pod_field( $pod->pod_data['object_fields'], $prefix . $name . '.' ) );
					$out = array_merge( $out, pq_tunnel_pod_field( $field['table_info']['object_fields'], $prefix . $name . '.' ) );
					$pod_fields = $pod->fields();
					//dump($pod_fields);
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

function pg_guild_query($id){
	$struct = get_post_meta($id, 'query_builder', true);
	dump($struct);
}
?>