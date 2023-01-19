<?php
/**
 * View: Troubleshooting - EA Status Table Eventbrite Section
 *
 * @since 4.14.2
 *
 */
?>
<tr>
	<th>
		<?php esc_html_e( 'Third Party Accounts', 'tribe-common' ); ?>
	</th>
</tr>

<?php
	// Eventbrite status section
	$icon    = 'success';
	$notes   = '&nbsp;';
	$message = 'Connected';

	if ( tribe( 'events-aggregator.main' )->api( 'origins' )->is_oauth_enabled( 'eventbrite' ) ) {
		if ( ! tribe( 'events-aggregator.settings' )->has_eb_security_key() ) {
			$icon                = 'warning';
			$message             = __( 'You have not connected Event Aggregator to Eventbrite', 'tribe-common' );
			$eventbrite_auth_url = Tribe__Events__Aggregator__Record__Eventbrite::get_auth_url(
					[ 'back' => 'settings' ]
			);
			$notes               = '<a href="' . esc_url( $eventbrite_auth_url ). '">' . esc_html_x( 'Connect to Eventbrite', 'link for connecting eventbrite', 'tribe-common' ) . '</a>';
		}
	} else {
		$icon    = 'warning';
		$message = __( 'Limited connectivity with Eventbrite', 'tribe-common' );
		$notes   = esc_html__( 'The service has disabled oAuth. Some types of events may not import.', 'tribe-common' );
	}
?>
<tr class="tribe-events-admin__ea-status-table-dark">
	<td class="tribe-events-admin__ea-status-table-dark">
		<?php esc_html_e( 'Eventbrite', 'tribe-common' ); ?>
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