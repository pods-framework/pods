<style type="text/css">
h3.popup-header {
	font-family: Georgia, "Times New Roman", Times, serif;
	color: #5a5a5a;
	font-size: 1.8em;
}

div.section {
	padding: 15px 15px 0 15px;
}
</style>
<script type="text/javascript">

</script>
<div id="pods_shortcode_form" style="display: none;">
	<div class="wrap">
		<div>
			<div class="section">
				<h3 class="popup-header">Add a Pod</h3>
			</div>
			
			<form id="pods_shortcode_form">
				<div class="section">
					<?php
					$api = new PodsAPI();
					$all_pods = $api->load_pods(array());
					?>
					<label for="pod_select">Choose a Pod</label>
					<select id="pod_select" name="pod_select">
						<?php foreach($all_pods as $pod => $data) { ?>
							<option name="<?php echo $pod; ?>">
								<?php echo $pod; ?>
							</option>
						<?php } ?>
					</select>
				</div>
			</form>
		</div>
	</div>
</div>
