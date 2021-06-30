<?php
/**
 * View: Week Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/v2/components/icons/week.php
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
$svg_classes = [ 'tribe-common-c-svgicon', 'tribe-common-c-svgicon--week' ];

if ( ! empty( $classes ) ) {
	$svg_classes = array_merge( $svg_classes, $classes );
}
?>
<svg <?php tribe_classes( $svg_classes ); ?> viewBox="0 0 19 18" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M.363.431V17.57c0 .238.163.431.363.431H18c.201 0 .364-.193.364-.431V.43c0-.238-.163-.431-.364-.431H.726c-.2 0-.363.193-.363.431zm.99.808h15.995v2.314H1.354V1.24zm15.995 3.449H1.354v12.07h15.994V4.687zM6.71 10.29v.862c0 .239.193.431.431.431h.862a.431.431 0 00.431-.43v-.863a.431.431 0 00-.43-.43H7.14a.431.431 0 00-.43.43zm3.448.862v-.862c0-.238.193-.43.431-.43h.862c.238 0 .431.192.431.43v.862a.431.431 0 01-.43.431h-.863a.431.431 0 01-.43-.43zm3.449-.862v.862c0 .239.193.431.43.431h.863a.431.431 0 00.43-.43v-.863a.431.431 0 00-.43-.43h-.862a.431.431 0 00-.431.43zm-10.345.862v-.862c0-.238.193-.43.43-.43h.863c.238 0 .43.192.43.43v.862a.431.431 0 01-.43.431h-.862a.431.431 0 01-.431-.43z" class="tribe-common-c-svgicon__svg-fill"/></svg>