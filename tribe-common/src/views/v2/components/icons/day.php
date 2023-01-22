<?php
/**
 * View: Day Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/day.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--day' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 19 18" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M.363 17.569V.43C.363.193.526 0 .726 0H18c.201 0 .364.193.364.431V17.57c0 .238-.163.431-.364.431H.726c-.2 0-.363-.193-.363-.431zm16.985-16.33H1.354v2.314h15.994V1.24zM1.354 4.688h15.994v12.07H1.354V4.687zm11.164 9.265v-1.498c0-.413.335-.748.748-.748h1.498c.413 0 .748.335.748.748v1.498a.749.749 0 01-.748.748h-1.498a.749.749 0 01-.748-.748z" class="tribe-common-c-svgicon__svg-fill"/></svg>