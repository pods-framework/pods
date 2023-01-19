<?php
/**
 * View: Summary Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/summary.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var array<string> $classes Additional classes to add to the svg icon.
 *
 * @version 4.13.3
 */

$svg_classes = [
	'tribe-common-c-svgicon',
	'tribe-common-c-svgicon--summary',
	'tribe-common-c-svgicon__svg-stroke',
];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M.716.643A.302.302 0 01.729.625h19.542a.656.656 0 01.104.375v2a.656.656 0 01-.104.375H.729A.657.657 0 01.625 3V1c0-.168.047-.292.09-.357zM20.254.608l.003.002a.014.014 0 01-.003-.002zm0 2.784l.003-.002-.003.002zm-19.508 0L.743 3.39a.013.013 0 01.003.002zM.743.61L.746.608.743.61zM.716 17.643a.312.312 0 01.013-.018h19.542l.013.018c.044.065.091.19.091.357v2a.656.656 0 01-.104.375H.729A.657.657 0 01.625 20v-2c0-.168.047-.292.09-.357zm19.538-.035l.003.002a.014.014 0 01-.003-.002zm0 2.784l.003-.002-.003.002zm-19.508 0l-.003-.002a.014.014 0 01.003.002zM.743 17.61a.013.013 0 01.003-.002l-.003.002zm19.58-2.735H.677c-.002 0-.005 0-.009-.002a.053.053 0 01-.016-.012.11.11 0 01-.027-.075V6.214a.11.11 0 01.027-.075.052.052 0 01.016-.012.022.022 0 01.01-.002h19.645c.002 0 .005 0 .009.002.004.002.01.005.016.012a.11.11 0 01.027.075v8.572a.11.11 0 01-.027.075.052.052 0 01-.016.012.023.023 0 01-.01.002z" stroke-width="1.25"/></svg>
