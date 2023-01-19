<?php
/**
 * View: Photo Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/icons/photo.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--photo' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 19 18" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.536 0H.539a.484.484 0 00-.495.483v17.034c0 .27.218.483.495.483h16.997a.484.484 0 00.495-.483V.483A.484.484 0 0017.536 0zm-.515.986V13.73l-1.907-2.938c-.555-.58-1.584-.58-2.139 0l-1.327 1.41-3.841-4.31a1.548 1.548 0 00-1.169-.502c-.435 0-.871.193-1.148.522l-4.436 4.929V.986h15.967zM1.054 14.329v2.705h15.987v-1.835l-2.66-3.73c-.178-.175-.495-.175-.653 0l-1.703 1.816c-.1.097-.218.174-.377.155a.569.569 0 01-.376-.174L7.054 8.53a.577.577 0 00-.416-.174.504.504 0 00-.396.174l-5.188 5.798z" class="tribe-common-c-svgicon__svg-fill"/><path fill-rule="evenodd" clip-rule="evenodd" d="M14.682 5.875c0 1.043-.825 1.875-1.82 1.875-.993 0-1.818-.832-1.818-1.875C11.044 4.83 11.85 4 12.863 4c1.012 0 1.819.831 1.819 1.875zm-.957 0c0-.483-.393-.89-.862-.89s-.863.407-.863.89c0 .483.394.889.863.889s.862-.406.862-.89z" class="tribe-common-c-svgicon__svg-fill"/></svg>
