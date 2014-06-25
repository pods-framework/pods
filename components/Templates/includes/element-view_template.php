<?php
/**
 * Frontier Template code editor metabox
 */
?><textarea id="content" name="content"><?php if ( isset( $content ) ) {
	echo esc_textarea( $content );
} ?></textarea>