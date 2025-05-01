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
<?php if ( $has_php && pods_eval_show_errors() ) : ?>
	<?php
	pods_deprecated( 'Pod Template PHP code has been deprecated, please use WP Templates instead of embedding PHP.', '2.3' );

	pods_message(
		sprintf(
			'
				<p><strong>%1$s:</strong> %2$s</p>
				<p><a href="%3$s" target="_blank" rel="noopener noreferrer">%4$s</a> | <a href="%5$s" target="_blank" rel="noopener noreferrer">%6$s</a></p>
			',
			esc_html__( 'Pod Template Error', 'pods' ),
			esc_html__( 'This template contains PHP code that will not run due to security restrictions in Pods.', 'pods' ),
			'https://docs.pods.io/displaying-pods/pod-template-hierarchy-for-themes/',
			esc_html__( 'Read more about file-based templates', 'pods' ),
			admin_url( 'admin.php?page=pods-components' ),
			esc_html__( 'Switch to file-based Pod Templates using our Migrate PHP into File-based templates component', 'pods' )
		),
		'error',
		false,
		false
	);
	?>
<?php endif; ?>

<div class="pods-compat-container">
	<textarea id="content" name="content"><?php echo $pods_output; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?></textarea>
</div>
