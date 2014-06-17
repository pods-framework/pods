<div class="pod-queries-tools"><?php

$api = pods_api();
$_pods = $api->load_pods();
echo '<select name="pod_reference[pod]" id="pod-reference" class="pod-switch" data-template="#podref-tmpl" data-target="#pod-reference-wrapper" data-action="pq_loadpod" data-event="change" />';
echo '<option value="">' . __( 'Select Pod to use as reference', 'pods' ) . '</option>';
foreach($_pods as $pod){
	echo '<option value="'.$pod['name'].'" ' . ( $atts['pod'] == $pod['name'] ? 'selected="selected"' : '' ) . '>'.$pod['label'].'</option>';
}
echo '</select>';
?></div>
<div id="pod-reference-wrapper" class="pod-reference-wrapper">
<?php 

	if(!empty($atts['pod'])){
		$fields = pq_loadpod($atts['pod']);
		if(!empty($fields)){
			foreach($fields as $field){
				echo '<div class="pod-field-row">';
				echo '<div class="pod-field-label pod-field-name" data-tag="'.$field.'">'.$field.'</div>';
				echo '</div>';
			}
		}
	}else{

	}

?>
</div>
<script id="podref-tmpl" type="text/html">
	{{#each this}}
	<div class="pod-field-row">
		<div class="pod-field-label pod-field-name" data-tag="{{this}}">{{this}}</div>
	</div>
	{{/each}}
</script>