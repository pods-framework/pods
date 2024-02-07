<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<p<?php PodsForm::attributes( $attributes, $name, $type, $options ); ?>>
	<?php echo pods_kses_exclude_p( $message ); ?>
</p>
