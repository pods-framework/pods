<?php
/**
 * Frontier Template code editor metabox
 */
?>
<div class="pods-compat-container">
	<textarea id="content" name="content">
		<?php if ( isset( $content ) ) {
			echo esc_textarea( $content );
		} ?>
	</textarea>
</div>
