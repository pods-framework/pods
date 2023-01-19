<?php
/**
 * View: Troubleshooting - EA Status Table Scheduler Status Section
 *
 * @since 4.14.2
 *
 */

$icon  = 'success';
$notes = '&nbsp;';

if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) {
	$icon    = 'warning';
	$message = __( 'WP Cron not enabled', 'tribe-common' );
	$notes   = esc_html__( 'Scheduled imports may not run reliably', 'tribe-common' );
} else {
	$message = __( 'WP Cron enabled', 'tribe-common' );
}
?>

<tr>
	<td>
		<?php esc_html_e( 'Scheduler Status', 'tribe-common' ); ?>
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