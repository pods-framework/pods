<?php
/**
 * Frontier Template code editor metabox
 *
 * @package Pods_templates
 */

$pods_output = '';
if ( isset( $content ) ) {

	// WordPress will already call esc_textarea() if richedit is off, don't escape twice (see #3462)
	if ( ! user_can_richedit() ) {
		// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		$pods_output = $content;
	} else {
		$pods_output = esc_textarea( $content );
	}
}
?>
<div class="pods-compat-container">
	<textarea id="content" name="content"><?php echo $pods_output; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?></textarea>
</div>
