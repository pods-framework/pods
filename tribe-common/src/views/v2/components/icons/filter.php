<?php
/**
 * View: Filter Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/filter.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var array $classes Additional classes to add to the svg icon.
 *
 * @version 4.12.14
 *
 */
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--filter' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.44 1a.775.775 0 10-1.55 0v1.89H1a.775.775 0 000 1.55h1.89v1.893a.775.775 0 001.55 0V4.44H17a.775.775 0 000-1.55H4.44V1zM.224 14.332c0-.428.347-.775.775-.775h12.56v-1.893a.775.775 0 011.55 0v1.893h1.89a.775.775 0 010 1.55h-1.89v1.89a.775.775 0 01-1.55 0v-1.89H.998a.775.775 0 01-.775-.775z"/></svg>
