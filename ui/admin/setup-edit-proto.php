<?php
wp_enqueue_style( 'wp-edit-post' );

// Formatted data
$data = [
	'fieldType' => 'edit-pod',
];

$data = wp_json_encode( $data, JSON_HEX_TAG );
?>
<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>
	<script type="application/json" class="pods-dfv-field-data"><?php echo $data; ?></script>
</div>
