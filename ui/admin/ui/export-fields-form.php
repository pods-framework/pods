<?php
/**
 * @var $ui PodsUI
 */
?>
<div class="wrap pods-admin pods-ui">
	<h2><?php echo __( 'Choose Export Fields', 'pods' ); ?></h2>
	<form method="post" id="pods_admin_ui_export_form">
		<?php
		// Avoid a bunch of inputs if there's a lot selected
		if ( ! empty( $_REQUEST['action_bulk_ids'] ) ) {
			$_GET['action_bulk_ids'] = implode( ',', (array) $_REQUEST['action_bulk_ids'] );
		}

		$ui->hidden_vars();
		?>

		<ul>
			<?php foreach ( $ui->pod->fields() as $field_name => $field ) { ?>
				<li>
					<label for="bulk_export_fields_<?php echo esc_attr( $field['name'] ); ?>">
						<input type="checkbox" name="bulk_export_fields[]"
							   id="bulk_export_fields_<?php echo esc_attr( $field['name'] ); ?>"
							   value="<?php echo esc_attr( $field['name'] ); ?>"/>
						<?php esc_html_e( $field['label'] ); ?>
					</label>
				</li>
			<?php } ?>
		</ul>

		<p class="submit">
			<?php _e( 'Export as:', 'pods' ); ?>&nbsp;&nbsp;
			<?php foreach ( $ui->export['formats'] as $format => $separator ) { ?>
				<input type="submit" id="export_type_<?php esc_attr_e( strtoupper( $format ) ); ?>"
					   value=" <?php esc_attr_e( strtoupper( $format ) ); ?> " name="bulk_export_type"
					   class="button-primary"/>
			<?php } ?>
		</p>
	</form>
</div>
