<?php
/**
 * View: No Map Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/no-map.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--no-map' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 67 66" xmlns="http://www.w3.org/2000/svg"><path d="M24.432 65S1 43.672 1 24.477C.992 12.344 10.225 2.207 22.293 1.1 34.361-.008 45.281 8.28 47.476 20.212M24.806 65s2.152-1.959 5.151-5.223" stroke-width="2" stroke-linecap="round" class="tribe-common-c-svgicon__svg-stroke"/><path d="M25 31.476a6.476 6.476 0 100-12.952 6.476 6.476 0 000 12.952zM48.365 61.063c9.468 0 17.143-7.675 17.143-17.142 0-9.468-7.675-17.143-17.142-17.143-9.468 0-17.143 7.675-17.143 17.143 0 9.467 7.675 17.142 17.142 17.142z" stroke-width="2" class="tribe-common-c-svgicon__svg-stroke"/><path d="M48.78 55.095a2.065 2.065 0 100-4.13 2.065 2.065 0 000 4.13z" class="tribe-common-c-svgicon__svg-fill"/><path d="M48.636 33.762v13.763" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tribe-common-c-svgicon__svg-stroke"/></svg>
