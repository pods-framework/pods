<?php
/**
 * @var string $title The header title.
 */

$box_id = 'pods-ui-postbox-header-' . sanitize_title( $title );
?>

<?php if ( version_compare( $GLOBALS['wp_version'], '5.5', '>=' ) ) : ?>
	<div class="postbox-header">
		<h2 class="hndle">
			<span><?php echo esc_html( $title ); ?></span>
		</h2>
		<div class="handle-actions hide-if-no-js">
			<?php
			/* We need to build support for this later.
			<button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="<?php echo esc_attr( $box_id ); ?>-handle-order-higher-description">
				<span class="screen-reader-text"><?php esc_html_e( 'Move up', 'pods' ); ?></span>
				<span class="order-higher-indicator" aria-hidden="true"></span>
			</button>
			<span class="hidden" id="<?php echo esc_attr( $box_id ); ?>-handle-order-higher-description"><?php esc_html_e( 'Move box up', 'pods' ); ?></span>
			<button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="<?php echo esc_attr( $box_id ); ?>-handle-order-lower-description">
				<span class="screen-reader-text"><?php esc_html_e( 'Move down', 'pods' ); ?></span>
				<span class="order-lower-indicator" aria-hidden="true"></span>
			</button>
			*/
			?>
			<span class="hidden" id="<?php echo esc_attr( $box_id ); ?>-handle-order-lower-description"><?php esc_html_e( 'Move box down', 'pods' ); ?></span>
			<button type="button" class="handlediv" aria-expanded="true">
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel', 'pods' ); ?></span>
				<span class="toggle-indicator" aria-hidden="true"></span>
			</button>
		</div>
	</div>
<?php else : ?>
	<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'pods' ); ?>"><br></div>
	<h3 class="hndle">
		<span><?php echo esc_html( $title ); ?></span>
	</h3>
<?php endif; ?>

