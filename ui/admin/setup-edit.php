<?php
wp_enqueue_media();
wp_enqueue_editor();

if (
	(
		function_exists( 'did_filter' )
		&& ! did_filter( 'tiny_mce_before_init' )
	)
	|| ! did_action( 'enqueue_block_editor_assets' )
) {
	wp_tinymce_inline_scripts();
}

wp_enqueue_style( 'wp-edit-post' );

// Formatted data
$data = [
	'fieldType'  => 'edit-pod',
	'fieldEmbed' => false,
];

$data = wp_json_encode( $data, JSON_HEX_TAG );
?>
<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>
	<script type="application/json" class="pods-dfv-field-data"><?php echo $data; ?></script>
</div>
