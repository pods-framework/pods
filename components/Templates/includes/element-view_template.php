<?php
/**
 * Frontier Template code editor metabox
 *
 * @package    Pods
 * @category   Components
 * @subpackage Templates
 */
?>
<textarea id="content" name="content"><?php if ( isset( $content ) ) { echo esc_textarea( $content ); } ?></textarea>