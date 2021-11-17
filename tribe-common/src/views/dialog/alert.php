<?php
/**
 * Dialog Alert View Template
 * The alert template for tribe-dialogs.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe/dialogs/alert.php
 *
 * @since 4.10.0
 *
 * @package Tribe
 * @version 4.10.0
 */

/** @var \Tribe\Dialog\View $dialog_view */
$dialog_view = tribe( 'dialog.view' );
// grab allthevars!
$vars        = get_defined_vars();
?>
<?php $dialog_view->template( 'button', $vars, true ); ?>
<script data-js="<?php echo esc_attr( 'dialog-content-' . $id ); ?>" type="text/template">
	<div <?php tribe_classes( $content_classes ) ?>>
		<?php if ( ! empty( $title ) ) : ?>
			<h2 <?php tribe_classes( $title_classes ) ?>><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<?php echo $content; ?>
		<div class="tribe-dialog__button_wrap">
			<button class="tribe-button tribe-alert__continue"><?php echo esc_html( $alert_button_text ); ?></button>
		</div>
	</div>
</script>
