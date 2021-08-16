<?php
/**
 * @var array  $log_choices
 * @var array  $log_engines
 * @var array  $log_levels
 * @var array  $log_entries
 * @var string $download_url
 */
?>
<div id="tribe-log-controls">

	<?php
	/**
	 * Fires within the #tribe-log-controls div, before any of the default
	 * controls are generated.
	 */
	do_action( 'tribe_common_log_controls_top' );
	?>

	<div>
		<label for="log-levels"><?php esc_html_e( 'Logging level', 'tribe-common' ) ?></label>
		<select
			class="tribe-dropdown"
			name="log-level"
			id="log-level"
		>
			<?php foreach ( $log_levels as $code => $name ): ?>
				<option name="<?php echo esc_attr( $code ) ?>" <?php selected( $code, tribe_get_option( 'logging_level') ); ?>>
					<?php echo esc_html( $name ) ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<?php
	/**
	 * Fires within the #tribe-log-controls div, after the #log-level control.
	 */
	do_action( 'tribe_common_log_controls_after_log_level' );
	?>

	<div>
		<label for="log-engine"><?php esc_html_e( 'Method', 'tribe-common' ) ?></label>
		<select
			class="tribe-dropdown"
			name="log-engine"
			id="log-engine"
		>
			<?php foreach ( $log_engines as $code => $name ): ?>
				<option name="<?php echo esc_attr( $code ) ?>" <?php selected( $code, tribe_get_option( 'logging_engine') ); ?>>
					<?php echo esc_html( $name ) ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<?php
	/**
	 * Fires within the #tribe-log-controls div, after the #log-engine control.
	 */
	do_action( 'tribe_common_log_controls_after_log_engine' );
	?>

	<div>
		<label for="log-selector"><?php esc_html_e( 'View', 'tribe-common' ) ?></label>
		<select
			class="tribe-dropdown"
			name="log-selector"
			id="log-selector"
		>
			<?php foreach ( $log_choices as $name ): ?>
				<option name="<?php echo esc_attr( $name ) ?>"><?php echo esc_html( $name ) ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<?php
	/**
	 * Fires within the #tribe-log-controls div, after the #log-selector control.
	 */
	do_action( 'tribe_common_log_controls_after_log_selector' );
	?>

	<div class="working hidden">
		<img src="<?php echo esc_url( get_admin_url( null, '/images/spinner.gif' ) ); ?>" />
	</div>

	<?php
	/**
	 * Fires within the #tribe-log-controls div, after all of the default
	 * controls have been generated.
	 */
	do_action( 'tribe_common_log_controls_bottom' );
	?>

</div>

<div id="tribe-log-viewer">
	<?php if ( empty( $log_entries ) ): ?>
		<p><?php esc_html_e( 'The selected log file is empty or has not been generated yet.', 'tribe-common' ); ?></p>
	<?php else: ?>

	<table>
		<?php foreach ( $log_entries as $data ): ?>
			<tr>
				<?php foreach ( $data as $single_cell ): ?>
					<td><?php echo esc_html( $single_cell ); ?></td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</table>

	<?php endif; ?>

</div>

<p> <a href="<?php echo esc_url( $download_url ) ?>" class="download_log" target="_blank"><?php esc_html_e( 'Download log', 'tribe-common' ); ?> </a> </p>