<?php
/**
 * View: Troubleshooting - EA Status Table Current Usage Section
 *
 * @since 4.14.2
 *
 */

$service          = tribe( 'events-aggregator.service' );
$import_limit     = $service->get_limit( 'import' );
$import_available = $service->get_limit_remaining();
$import_count     = $service->get_limit_usage();
$icon             = 'success';
$notes            = '&nbsp;';

if ( 0 === $import_limit || $import_count >= $import_limit ) {
	$icon  = 'error';
	$notes = esc_html__( 'You have reached your daily import limit. Scheduled imports will be paused until tomorrow.', 'tribe-common' );
} elseif ( $import_count / $import_limit >= 0.8 ) {
	$icon  = 'warning';
	$notes = esc_html__( 'You are approaching your daily import limit. You may want to adjust your Scheduled Import frequencies.', 'tribe-common' );
}

$message = sprintf( // import count and limit
	_n( '%1$d import used out of %2$d available today', '%1$d imports used out of %2$d available today', $import_count, 'tribe-common' ),
	intval( $import_count ),
	intval( $import_limit )
);
?>
<tr>
	<td>
		<?php esc_html_e( 'Current usage', 'tribe-common' ); ?>
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