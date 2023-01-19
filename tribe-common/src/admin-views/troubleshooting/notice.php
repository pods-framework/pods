<?php
/**
 * View: Troubleshooting - Admin Notice
 *
 * @since 4.14.2
 *
 */
$url = class_exists( 'Tribe__Events__Main' )
? admin_url( 'edit.php?post_type=tribe_events&page=tec-events-help' )
: admin_url( 'admin.php?page=tec-tickets-help' );

$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Help page?', 'tribe-common' ) . '</a>';
?>
<div class="tribe-events-admin__troubleshooting-notice">
	<div class="tribe-events-admin__troubleshooting-notice_title">
		<?php
			echo sprintf( esc_html__( 'Hey there... did you check out the %s', 'tribe-common' ), $link );
		?>
	</div>
</div>