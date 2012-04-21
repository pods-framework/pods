<h3><?php echo $title; ?></h3>
<table class="form-table">
<?php foreach($fields as $field): ?>
<tr>
	<th><label for="<?php echo $field->name; ?>"><?php echo $field->label; ?></label></th>
	<td><?php echo $field['output']; ?></td>
</tr>
<?php endforeach; ?>
</table>