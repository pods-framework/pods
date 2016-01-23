<?php /* @global $attributes, $button_text */ ?>
<script type="text/template" id="file-upload-layout-template">
	<div class="pods-ui-list"></div>
	<div class="pods-ui-form"></div>
</script>

<script type="text/template" id="file-upload-item-template">
	<input name="<%- attributes.name %>[<%- id %>][id]" data-name-clean="<%- attributes.name_clean %>-id" id="<%- attributes.id %>-<%- id %>-id" class="<%- attributes.class %>" type="hidden" value="<%- id %>" />
	<ul class="pods-file-meta media-item">
		<li class="pods-file-col pods-file-handle">
			Handle
		</li>
		<li class="pods-file-col pods-file-icon">
			<img class="pinkynail" src="<%- icon %>" alt="Icon">
		</li>
		<li class="pods-file-col pods-file-name">
			<input type="text" value="<%- name %>" tabindex="2" maxlength="255">
		</li>
		<li class="pods-file-col pods-file-remove pods-file-delete">
			<a href="#remove">Remove</a>
		</li>
	</ul>
</script>

<script type="text/template" id="file-upload-form-template">
	<a class="button pods-file-add pods-media-add" href="#" tabindex="2"><?php echo $button_text; ?></a>
</script>