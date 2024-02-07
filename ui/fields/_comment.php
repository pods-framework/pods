<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<p<?php PodsForm::attributes( $attributes, $name, $type, $options ); ?>>
	<?php echo wp_kses_post( $message ); ?>
</p>
