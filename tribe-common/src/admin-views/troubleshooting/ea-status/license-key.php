<?php
/**
 * View: Troubleshooting - EA Status Table License Key Section
 *
 * @since 4.14.2
 *
 */

$message   = '&nbsp;';
$ea_active = false;
$notes     = '&nbsp;';

if ( ! tribe()->offsetExists( 'events-aggregator.main' ) ) {
	return;
};

if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
	$icon      = 'success';
	$message   = __( 'Your license is valid', 'tribe-common' );
	$ea_active = true;
} else {
	$service_status = tribe( 'events-aggregator.service' )->api()->get_error_code();

	$icon = 'error';
	if ( 'core:aggregator:invalid-service-key' == $service_status ) {
		$message = __( 'You do not have a license', 'tribe-common' );
		$notes   = '<a href="https://theeventscalendar.com/wordpress-event-aggregator/?utm_source=importsettings&utm_medium=plugin-tec&utm_campaign=in-app" target="_blank" rel="noopener noreferrer">';
		$notes  .= esc_html__( 'Buy Event Aggregator to access more event sources and automatic imports!', 'tribe-common' );
		$notes  .= '</a>';
	} else {
		$message = __( 'Your license is invalid', 'tribe-common' );
		$notes   = '<a href="' . esc_url( Tribe__Settings::instance()->get_url( [ 'tab' => 'licenses' ] ) ) . '">' . esc_html__( 'Check your license key', 'tribe-common' ) . '</a>';
	}
}
?>
<tr>
	<th>
		<?php esc_html_e( 'License & Usage', 'tribe-common' ); ?>
	</th>
</tr>

<tr class="tribe-events-admin__ea-status-table-dark">
	<td>
		<?php esc_html_e( 'License Key', 'tribe-common' ); ?>
	</td>
	<td>
		<img
			src="<?php echo esc_url( tribe_resource_url( $status_icons[ $icon ], false, null, $main ) ); ?>"
			alt=""
		/>
		<?php echo esc_html( $message ); ?>
	</td>
	<td><?php echo $notes; // Escaping handled above. ?></td>
</tr>