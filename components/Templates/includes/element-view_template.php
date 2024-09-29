<?php
/**
 * Frontier Template code editor metabox
 *
 * @package Pods_templates
 */

$has_php = false;

$pods_output = '';

if ( isset( $content ) ) {
	$has_php = false !== strpos( $content, '<?' );

	// WordPress will already call esc_textarea() if richedit is off, don't escape twice (see #3462)
	if ( ! user_can_richedit() ) {
		// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		$pods_output = $content;
	} else {
		$pods_output = esc_textarea( $content );
	}
}
?>
<?php if ( $has_php ) : ?>
	<?php
	pods_deprecated( 'Pod Template PHP code has been deprecated, please use WP Templates instead of embedding PHP.', '2.3' );

	printf(
		'<div class="pods-ui-notice-admin pods-ui-notice-warning"><p>⚠️&nbsp;&nbsp;%1s - <a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a></p></div>',
		__( 'PHP detected, this feature is deprecated', 'pods' ),
		'https://docs.pods.io/displaying-pods/pod-page-template-hierarchy-for-themes/',
		__( 'Switch to file-based Pod Pages', 'pods' )
	);
	?>

	<?php if ( PODS_DISABLE_EVAL ) : ?>
		<?php
		pods_message(
			sprintf(
				'<p><strong>%1$s:</strong> %2$s</p><p><a href="%3$s" target="_blank" rel="noopener noreferrer">%4$s</a></p>',
				__( 'Pod Template Error', 'pods' ),
				__( 'This template contains PHP code that will not run due to security restrictions in Pods. To enable PHP code, you must configure your website to allow PHP by setting the constant PODS_DISABLE_EVAL to false.', 'pods' ),
				'https://docs.pods.io/displaying-pods/pod-template-hierarchy-for-themes/',
				__( 'Switch to file-based Pod Templates', 'pods' )
			),
			'error',
			false,
			false
		);
		?>
	<?php else : ?>
		<?php
		pods_message(
			sprintf(
				'<p><strong>%1$s:</strong> %2$s</p><p><a href="%3$s" target="_blank" rel="noopener noreferrer">%4$s</a></p>',
				__( 'Pod Template Warning', 'pods' ),
				__( 'This template contains PHP code that will no longer run in Pods 3.3+.', 'pods' ),
				'https://docs.pods.io/displaying-pods/pod-template-hierarchy-for-themes/',
				__( 'Switch to file-based Pod Templates', 'pods' )
			),
			'warning'
		);
		?>
	<?php endif; ?>
<?php endif; ?>

<div class="pods-compat-container">
	<textarea id="content" name="content"><?php echo $pods_output; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?></textarea>
</div>
