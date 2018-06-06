<?php
/**
 * Frontier Template code editor metabox
 *
 * @package Pods_templates
 */

?>
<div class="pods-compat-container">
	<textarea id="content" name="content">
		<?php
		if ( isset( $content ) ) {
			// WordPress will already call esc_textarea() if richedit is off, don't escape twice (see #3462)
			if ( ! user_can_richedit() ) {
				// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				echo $content;
			} else {
				echo esc_textarea( $content );
			}
		}
		?>
	</textarea>
</div>
