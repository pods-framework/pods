<?php
/**
 * View: Troubleshooting - EA Status Table Current Status.
 *
 * @since 4.14.18
 *
 *
 * @param array<string|string> $status_icons An array of icons for the EA Status table.
 * @param \Tribe__Main         $main         An instance of the main class of Tribe Common.
 *
 */

$icon     = 'success';
$notes    = '&nbsp;';
$message  = esc_html_x( 'Imports Enabled in Settings', '', 'tribe-common' );
$disabled = tribe_get_option( 'tribe_aggregator_disable', false );

if ( $disabled ) {
	$icon         = 'error';
	$message      = _x( 'Imports disabled in Settings', '', 'tribe-common' );
	$settings_url = Tribe__Settings::instance()->get_url( array( 'tab' => 'imports' ) );
	$notes 		  = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( $settings_url ),
		_x( 'Edit Import Settings', '','tribe-common' )
	);
}
?>
<tr class="tribe-events-admin__ea-status-table-dark">
	<td>
		<?php esc_html_e( 'Enabled', 'tribe-common' ); ?>
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