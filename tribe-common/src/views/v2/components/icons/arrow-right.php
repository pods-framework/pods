<?php
/**
 * View: Arrow Right Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/arrow-right.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--arrow-right' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 22 19" xmlns="http://www.w3.org/2000/svg"><path d="M11.648 0L9.62 1.956l6.23 6.005H0v2.793h15.85L9.62 16.76l2.028 1.956 9.705-9.358z" class="tribe-common-c-svgicon__svg-fill"/></svg>
