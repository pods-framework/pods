<div class="pod-queries-tools"><?php

	$defaults = array(
		'pod' => '',
	);
	if ( empty( $atts ) ) {
		$atts = $defaults;
	} else {
		$atts = wp_parse_args( $atts, $defaults );
	}

	$api   = pods_api();
	$_pods = $api->load_pods();
	echo '<select name="pod_reference[pod]" id="pod-reference" class="pod-switch" data-template="#podref-tmpl" data-target="#pods-magic-tag-list" data-action="pq_loadpod" data-event="change" />';
	echo '<option value="">' . esc_html__( 'Select Pod to use as reference', 'pods' ) . '</option>';
	foreach ( $_pods as $pod ) {
		echo '<option value="' . esc_attr( $pod['name'] ) . '" ' . ( $atts['pod'] == $pod['name'] ? 'selected="selected"' : '' ) . '>' . esc_html( $pod['label'] ) . '</option>';
	}
	echo '</select>';
	?></div>
<div id="pod-reference-wrapper" class="pod-reference-wrapper">
	<?php include_once __DIR__ . '/element-pod_reference-content.php'; ?>
</div>
<script id="podref-tmpl" type="text/html">
	{{#if this.length}}
	<dl>
		<dt><?php esc_html_e( 'Available magic tags', 'pods' ); ?></dt>
		<dd><em><?php esc_html_e( 'You can click to copy any tag', 'pods' ); ?></em></dd>
		{{#each this}}
		<dd class="pods-magic-tag-option" data-tag="{{this}}" title="<?php esc_attr_e( 'Field', 'pods' ); ?>: {{this}}">
			<span>{{this}}</span>
		</dd>
		{{/each}}
	</dl>
	{{/if}}
</script>
