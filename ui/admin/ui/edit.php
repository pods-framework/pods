<?php
/**
 * @var $ui PodsUI
 */
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
		echo wp_kses_post( $ui->do_template( $duplicate ? $ui->header['duplicate'] : $ui->header['edit'] ) );

		if ( ! in_array( 'add', $ui->actions_disabled ) && ! in_array( 'add', $ui->actions_hidden ) && ! $ui->restricted( 'add' ) ) {
			$link = pods_query_arg(
					array(
							$ui->num_prefix . 'action' . $ui->num => 'add',
							$ui->num_prefix . 'id' . $ui->num     => '',
							$ui->num_prefix . 'do' . $ui->num     => '',
					), $ui::$allowed, $ui->exclusion()
			);

			if ( ! empty( $ui->action_links['add'] ) ) {
				$link = $ui->action_links['add'];
			}
			?>
			<a href="<?php echo esc_url( $link ); ?>"
			   class="add-new-h2"><?php echo wp_kses_post( $ui->heading['add'] ); ?></a>
			<?php
		} elseif ( ! in_array( 'manage', $ui->actions_disabled ) && ! in_array( 'manage', $ui->actions_hidden ) && ! $ui->restricted( 'manage' ) ) {
			$link = pods_query_arg(
					array(
							$ui->num_prefix . 'action' . $ui->num => 'manage',
							$ui->num_prefix . 'id' . $ui->num     => '',
					), self::$allowed, $ui->exclusion()
			);

			if ( ! empty( $ui->action_links['manage'] ) ) {
				$link = $ui->action_links['manage'];
			}
			?>
			<a href="<?php echo esc_url( $link ); ?>"
			   class="add-new-h2">&laquo; <?php echo sprintf( __( 'Back to %s', 'pods' ), $ui->heading['manage'] ); ?></a>
			<?php
		}//end if
		?>
	</h2>

	<?php $ui->form( false, $duplicate ); ?>
</div>
