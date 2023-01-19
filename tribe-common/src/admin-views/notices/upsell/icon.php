<?php
/**
 * Upsell Icon Template
 * The icon template for TEC Upsell notices.
 *
 * @since 4.14.17
 * 
 * @version 4.14.17
 *
 * @var string        $icon_url  URL to icon.
 * 
 */

?>
<div class="tec-admin__upsell-icon">
	<img
		class="tec-admin__upsell-icon-image"
		src="<?php echo esc_url( $icon_url ); ?>"
		alt="<?php esc_attr_e( 'The Events Calendar important notice icon', 'tribe-common' ); ?>"
	/>
</div>