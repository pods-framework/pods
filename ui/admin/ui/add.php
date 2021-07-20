<?php
$icon_style = '';
if ( false !== $ui->icon ) {
	$icon_style = ' style="background-position:0 0;background-size:100%;background-image:url(' . esc_url( $ui->icon ) . ');"';
}
?>
<div class="wrap pods-ui">
	<div id="icon-edit-pages"
	     class="icon32"<?php echo $icon_style; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>>
		<br/>
	</div>
	<h2>
		<?php
		echo wp_kses_post( $ui->header['add'] );

		if ( ! in_array( 'manage', $ui->actions_disabled ) && ! in_array( 'manage', $ui->actions_hidden ) && ! $ui->restricted( 'manage' ) ) {
			$link = pods_query_arg(
				array(
					$ui->num_prefix . 'action' . $ui->num => 'manage',
					$ui->num_prefix . 'id' . $ui->num     => '',
				), $ui::$allowed, $ui->exclusion()
			);

			if ( ! empty( $ui->action_links['manage'] ) ) {
				$link = $ui->action_links['manage'];
			}
			?>
			<a href="<?php echo esc_url( $link ); ?>"
			   class="add-new-h2">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $ui->heading['manage'] ); ?></a>
		<?php } ?>
	</h2>

	<?php $ui->form( true ); ?>
</div>
