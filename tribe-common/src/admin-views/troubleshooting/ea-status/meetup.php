<?php
/**
 * View: Troubleshooting - EA Status Table Meetup Section
 *
 * @since 4.14.2
 *
 */

$icon    = 'success';
$notes   = '&nbsp;';
$message = 'Connected';

if ( tribe( 'events-aggregator.main' )->api( 'origins' )->is_oauth_enabled( 'meetup' ) ) {
	if ( ! tribe( 'events-aggregator.settings' )->has_meetup_security_key() ) {
		$icon            = 'warning';
		$message         = __( 'You have not connected Event Aggregator to Meetup', 'tribe-common' );
		$meetup_auth_url = Tribe__Events__Aggregator__Record__Meetup::get_auth_url( [ 'back' => 'settings' ] );
		$notes           = '<a href="' . esc_url( $meetup_auth_url ). '">' . esc_html_x( 'Connect to Meetup', 'link for connecting meetup', 'tribe-common' ) . '</a>';
	}
} else {
	$icon    = 'warning';
	$message = __( 'Limited connectivity with Meetup', 'tribe-common' );
	$notes   = esc_html__( 'The service has disabled oAuth. Some types of events may not import.', 'tribe-common' );
}
?>

<tr>
	<td>
		<?php esc_html_e( 'Meetup', 'tribe-common' ); ?>
	</td>
	<td>
		<img
			src="<?php echo esc_url( tribe_resource_url( $status_icons[ $icon ], false, null, $main ) ); ?>"
			alt=""
		/>
		<?php echo esc_html( $message ); ?>
	</td>
	<td><?php echo $notes;  // Escaping handled above. ?></td>
</tr>