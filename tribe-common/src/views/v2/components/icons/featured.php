<?php
/**
 * View: Featured Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/featured.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var array<string> $classes Additional classes to add to the svg icon.
 *
 * @version 4.12.14
 *
 */
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--featured' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $icon_title ) ) {
	$icon_title = __( 'Featured', 'tribe-common' );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 8 10" xmlns="http://www.w3.org/2000/svg">
	<title><?php echo esc_html( $icon_title ) ?></title>
	<path fill-rule="evenodd" clip-rule="evenodd" d="M0 0h8v10L4.049 7.439 0 10V0z"/>
</svg>
