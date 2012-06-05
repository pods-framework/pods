<style type="text/css">
h3.popup-header {
    font-family: Georgia, "Times New Roman", Times, serif;
    color: #5a5a5a;
    font-size: 1.8em;
    background: url(<?php echo PODS_URL; ?>/ui/images/icon32.png) top left no-repeat;
    padding: 8px 0 5px 36px;
    margin-top: 0;
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

a#pods_insert_shortcode {
    color: white !important;
}

strong.red {
    color: red;
}
</style>

<script type="text/javascript">
jQuery(function($) {
    var $useCaseSelector = $('#use-case-selector'),
        $form = $('#pods_shortcode_form'),
        $podSelector = $('#pod_select'),
		ajaxurl = "<?php echo admin_url('admin-ajax.php?pods_ajax=1'); ?>",
		nonce = "<?php echo wp_create_nonce('pods-load_pod'); ?>";
	console.log(ajaxurl);
	console.log(nonce);

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
                $('#pod_select, #pod_orderby, #pod_sort_direction, #pod_limit, #pod_template, #pod_helper, #pod_where, #pods_insert_shortcode').each(function() {
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

	$('#pod_select').change(function() {
		var pod = $(this).val();
		var jax = $.ajax(ajaxurl, {
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'pods_admin',
				method: 'load_pod',
				name: pod,
				_wpnonce: nonce
			}
		});
		jax.success(function(json) {
			console.log(json.fields);
			var $orderby = $('#pod_orderby'),
				$column  = $('#pod_column');

			$orderby.find('option').remove();
			$orderby.append('<option value=""></option>');

			$column.find('option').remove();
			$column.append('<option value=""></option>');

			$.each(json.fields, function(key, val) {
				$orderby.append('<option value="' + val.name + '">' + val.label + '</option>');
				$column.append('<option value="' + val.name + '">' + val.label + '</option>');
			});
		});
	});
});
</script>


<div id="pods_shortcode_form" style="display: none;">
    <div class="wrap">
        <div>
            <div class="header">
                <h3 class="popup-header">Pods &raquo; Embed</h3>
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
                    $pod_count = count($all_pods);
                    ?>
                    <label for="pod_select">Choose a Pod</label>
                    <?php if ($pod_count > 0) { ?>
                        <select id="pod_select" name="pod_select">
                            <?php foreach($all_pods as $pod => $data) { ?>
                                <option value="<?php echo $pod; ?>">
                                    <?php echo $pod; ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <strong class="red" id="pod_select">None Found</strong>
                    <?php } ?>
                </div>
                <div class="section hide">
                    <?php
                    $templates = $api->load_templates(array(
                        'orderby' => 'name ASC',
                    ));
                    $template_count = count($templates);
                    ?>
                    <label for="pod_template">Template</label>
                    <?php if ($template_count > 0) { ?>
                        <select id="pod_template" name="pod_template">
                            <option value=""></option>
                            <?php foreach ($templates as $tmpl => $data){ ?>
                                <option value="<?php echo $tmpl; ?>">
                                    <?php echo $tmpl; ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <strong class="red" id="pod_template">None Found</strong>
                    <?php } ?>
                    <script type="text/javascript">
                    window.pods_template_count = <?php echo $template_count; ?>;
                    </script>
                </div>
                <div class="section hide">
                    <label for="pod_slug">ID or Slug</label>
                    <input type="text" id="pod_slug" name="pod_slug" />
                </div>
                <div class="section hide">
                    <label for="pod_limit">Limit</label>
                    <input type="text" id="pod_limit" name="pod_limit" />
                </div>
                <div class="section hide">
                    <label for="pod_orderby">Order By</label>
					<select name="pod_orderby" id="pod_orderby">
					</select>
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
                    <label for="pod_column">Column</label>
					<select id="pod_column" name="pod_column">
					</select>
                </div>
                <div class="section hide">
                    <label for="pod_where">Filter</label>
                    <input type="text" name="pod_where" id="pod_where" />
                </div>
                <div class="section hide">
                    <?php
                    $helpers = $api->load_helpers(array(
                        "orderby" => "name ASC",
                    ));
                    $helper_count = count($helpers);
                    ?>
                    <label for="pod_helper">Helper</label>
                    <?php if ($helper_count > 0) { ?>
                        <select id="pod_helper" name="pod_helper">
                            <option value=""></option>
                            <?php foreach ($helpers as $helper => $data) { ?>
                                <option value="<?php echo $helper; ?>">
                                    <?php echo $helper; ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                </div>
                <div class="section hide" style="text-align: right;">
                    <a class="button-primary" id="pods_insert_shortcode" href="#">Insert</a>
                </div>
            </form>
        </div>
    </div>
</div>
