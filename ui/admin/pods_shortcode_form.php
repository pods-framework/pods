<style type="text/css">
h3.popup-header {
	font-family: Georgia, "Times New Roman", Times, serif;
	color: #5a5a5a;
	font-size: 1.8em;
}

div.section, div.select, div.header {
	padding: 15px 15px 0 15px;
}

div.section.hide {
	display: none;
}

.section label {
	display: inline-block;
	width: 120px;
	font-weight: bold;
}
</style>

<script type="text/javascript">
jQuery(function($) {
	var $useCaseSelector = $('#use-case-selector'),
		$form = $('#pods_shortcode_form');

	$useCaseSelector.change(function(evt) {
		var val = $(this).val();

		$('.section').addClass('hide');

		switch (val) {
			case 'single':
				$('#pod_select, #pod_slug, #pod_template, #pod_helper, #pods_insert_shortcode').each(function() {
					$(this).closest('.section').removeClass('hide');
				})
				break;
			case 'list':
				$('#pod_select, #pod_orderby, #pod_sort_direction, #pod_limit, #pod_template, #pod_helper, #pods_insert_shortcode').each(function() {
					$(this).closest('.section').removeClass('hide');
				})
				break;
			case 'column':
				$('#pod_select, #pod_slug, #pod_helper, #pod_column, #pods_insert_shortcode').each(function() {
					$(this).closest('.section').removeClass('hide');
				})
				break;
		}
	});
});
</script>


<div id="pods_shortcode_form" style="display: none;">
	<div class="wrap">
		<div>
			<div class="header">
				<h3 class="popup-header">Add a Pod</h3>
			</div>
			
			<form id="pods_shortcode_form">
				<div class="select">
					<label for="use-case-selector">What would you like to do?</label>
					<select id="use-case-selector">
						<option value="">---</option>
						<option value="single">Display a single Pod item</option>
						<option value="list">List multiple Pod items</option>
						<option value="column">Display a column from a single Pod item</option>
					</select>
				</div>
				<div class="section hide">
					<?php
					$api = new PodsAPI();
					$all_pods = $api->load_pods(array(
						'orderby' => 'name ASC',
					));
					?>
					<label for="pod_select">Choose a Pod</label>
					<select id="pod_select" name="pod_select">
						<?php foreach($all_pods as $pod => $data) { ?>
							<option value="<?php echo $pod; ?>">
								<?php echo $pod; ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="section hide">
					<label for="pod_slug">Slug</label>
					<input type="text" id="pod_slug" name="pod_slug" />
				</div>
				<div class="section hide">
					<label for="pod_orderby">Order By</label>
					<input type="text" id="pod_orderby" name="pod_orderby" />
				</div>
				<div class="section hide">
					<label for="pod_sort_direction">Direction</label>
					<select id="pod_sort_direction" name="pod_sort_direction">
						<option value=""></option>
						<option value="ASC">
							Ascending
						</option>
						<option value="DESC">
							Descending
						</option>
					</select>
				</div>
				<div class="section hide">
					<?php
					$templates = $api->load_templates(array(
						'orderby' => 'name ASC',
					));
					?>
					<label for="pod_template">Template</label>
					<select id="pod_template" name="pod_template">
						<option value=""></option>
						<?php foreach ($templates as $tmpl => $data){ ?>
							<option value="<?php echo $tmpl; ?>">
								<?php echo $tmpl; ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="section hide">
					<label for="pod_limit">Limit</label>
					<input type="text" id="pod_limit" name="pod_limit" />
				</div>
				<div class="section hide">
					<label for="pod_column">Column</label>
					<input type="text" id="pod_column" name="pod_column" />
				</div>
				<div class="section hide">
					<?php
					$helpers = $api->load_helpers(array(
						"orderby" => "name ASC",
					));
					?>
				    <label for="pod_helper">Helper</label>
					<select id="pod_helper" name="pod_helper">
						<option value=""></option>
						<?php foreach ($helpers as $helper => $data) { ?>
							<option value="<?php echo $helper; ?>">
								<?php echo $helper; ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="section hide">
					<a class="button" id="pods_insert_shortcode" href="#">Insert</a>
				</div>
			</form>
		</div>
	</div>
</div>
