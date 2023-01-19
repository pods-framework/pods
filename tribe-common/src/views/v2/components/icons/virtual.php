<?php
/**
 * View: Virtual Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/virtual.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var array<string> $classes Additional classes to add to the svg icon.
 *
 * @version 4.12.14
 */

$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--virtual' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}

if ( empty( $icon_title ) ) {
	$icon_title = __( 'Virtual', 'tribe-common' );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 16">
	<title><?php echo esc_html( $icon_title ) ?></title>
	<defs/>
	<g fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" transform="translate(1 1)">
		<path d="M18 10.7333333c2.16-2.09999997 2.16-5.44444441 0-7.46666663M21.12 13.7666667c3.84-3.7333334 3.84-9.80000003 0-13.53333337M6 10.7333333C3.84 8.63333333 3.84 5.28888889 6 3.26666667M2.88 13.7666667C-.96 10.0333333-.96 3.96666667 2.88.23333333" class="tribe-common-c-svgicon__svg-stroke"/><ellipse cx="12" cy="7" rx="2.4" ry="2.33333333" class="tribe-common-c-svgicon__svg-stroke"/>
	</g>
</svg>
