<?php
/**
 * View: Troubleshooting - EA Status Table Server Connection Section
 *
 * @since 4.14.2
 *
 */
?>
<tr>
	<th>
		<?php esc_html_e( 'Import Services', 'tribe-common' ); ?>
	</th>
</tr>

<?php
	$icon      = 'success';
	$notes     = '&nbsp;';
	$ea_server = tribe( 'events-aggregator.service' )->api()->domain;
	$up        = tribe( 'events-aggregator.service' )->get( 'status/up' );

	if ( ! $up || is_wp_error( $up ) ) {
		$icon = 'error';
		/* translators: %s: Event Aggregator Server URL */
		$message = sprintf( __( 'Not connected to %s', 'tribe-common' ), $ea_server );
		$notes   = esc_html__( 'The server is not currently responding', 'tribe-common' );
	} elseif ( is_object( $up ) && is_object( $up->data ) && isset( $up->data->status ) && 400 <= $up->data->status ) {
		// this is a rare condition that should never happen
		// An example case: the route is not defined on the EA server
		$icon    = 'warning';

		/* translators: %s: Event Aggregator Server URL */
		$message = sprintf( __( 'Not connected to %s', 'tribe-common' ), $ea_server );
		$notes   = __( 'The server is responding with an error:', 'tribe-common' );
		$notes  .= '<pre>';
		$notes  .= esc_html( $up->message );
		$notes  .= '</pre>';
	} else {
		/* translators: %s: Event Aggregator Server URL */
		$message = sprintf( __( 'Connected to %s', 'tribe-common' ), $ea_server );
	}
?>

<tr class="tribe-events-admin__ea-status-table-dark">
	<td>
		<?php esc_html_e( 'Server Connection', 'tribe-common' ); ?>
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