<?php

namespace Pods\Admin;

use PodsForm;

/**
 * Settings specific functionality.
 *
 * @since 2.8.0
 */
class Settings {

	const OPTION_NAME = 'pods_settings';

	/**
	 * Add the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function hook() {
		add_action( 'pods_admin_settings_fields', [ $this, 'add_settings_fields' ], 9 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		remove_action( 'pods_admin_settings_fields', [ $this, 'add_settings_fields' ], 9 );
	}

	/**
	 * Get the value for a Pods setting.
	 *
	 * @since 2.8.0
	 *
	 * @param string $setting_name The setting name.
	 * @param null   $default      The default value if the setting is not yet set.
	 *
	 * @return mixed The setting value.
	 */
	public function get_setting( $setting_name, $default = null ) {
		$settings = $this->get_settings();

		return pods_v( $setting_name, $settings, $default );
	}

	/**
	 * Get the Pods settings.
	 *
	 * @since 2.8.0
	 *
	 * @return array The setting values.
	 */
	public function get_settings() {
		$settings = get_option( self::OPTION_NAME, [] );

		if ( ! $settings ) {
			$settings = [];
		}

		// Register settings with Wisdom Tracker.
		$settings['wisdom_registered_setting'] = 1;

		$defaults = $this->get_setting_fields();

		$layout_field_types = PodsForm::layout_field_types();

		// Set up defaults as needed.
		foreach ( $defaults as $setting_name => $setting ) {
			// Skip layout field types.
			if ( isset( $setting['type'] ) && in_array( $setting['type'], $layout_field_types, true ) ) {
				continue;
			}

			if ( isset( $settings[ $setting_name ] ) || ! isset( $setting['default'] ) ) {
				continue;
			}

			$settings[ $setting_name ] = $setting['default'];
		}

		return $settings;
	}

	/**
	 * Update the value for a Pods setting.
	 *
	 * @since 2.8.0
	 *
	 * @param string $setting_name  The setting name.
	 * @param mixed  $setting_value The setting value.
	 */
	public function update_setting( $setting_name, $setting_value ) {
		$settings = $this->get_settings();

		if ( null !== $setting_value ) {
			$settings[ $setting_name ] = $setting_value;
		} elseif ( isset( $settings[ $setting_name ] ) ) {
			unset( $settings[ $setting_name ] );
		}

		$this->update_option( $settings );
	}

	/**
	 * Update the settings for a Pods.
	 *
	 * @since 2.8.0
	 *
	 * @param array $setting_values The list of settings to update, pass null as a value to remove it.
	 */
	public function update_settings( array $setting_values ) {
		$settings = $this->get_settings();
		$settings = array_merge( $settings, $setting_values );

		foreach ( $settings as $setting_name => $setting_value ) {
			if ( null === $setting_value ) {
				unset( $settings[ $setting_name ] );
			}
		}

		$this->update_option( $settings );
	}

	/**
	 * Handle saving the Pods settings to the option.
	 *
	 * @param array $settings The Pods settings to be saved.
	 */
	private function update_option( array $settings ) {
		/**
		 * Allow filtering whether Pods settings are set to autoload.
		 *
		 * @param string $autoload Whether Pods settings should be saved as autoload, set to 'yes' to autoload (default) and 'no' to not autoload.
		 */
		$autoload = apply_filters( 'pods_admin_settings_autoload', 'yes' );

		update_option( self::OPTION_NAME, $settings, $autoload );
	}

	/**
	 * Get the list of Pods settings fields.
	 *
	 * @since 2.8.0
	 *
	 * @return array The list of Pods settings fields.
	 */
	public function get_setting_fields() {
		$session_auto_start            = pods_session_auto_start( true );
		$session_auto_start_overridden = null !== $session_auto_start;

		$fields['sessions'] = [
			'label' => __( 'Sessions', 'pods' ),
			'type'  => 'heading',
		];

		$disabled_text = sprintf(
			'%1$s<br /><strong>%2$s: %3$s</strong>',
			__( 'This setting is disabled because it is forced through the PODS_SESSION_AUTO_START constant elsewhere.', 'pods' ),
			__( 'Current value', 'pods' ),
			$session_auto_start ? __( 'Enabled', 'pods' ) : __( 'Disabled', 'pods' )
		);

		$fields['session_auto_start'] = [
			'name'               => 'session_auto_start',
			'label'              => __( 'Secure anonymous public form submissions using PHP sessions (potential performance impacts)', 'pods' ),
			'help'               => __( 'Sessions will be used to secure submissions from public forms from logged out visitors to ensure they do not submit fields they are not allowed to access. Auto-detecting sessions will automatically turn this setting on the first anonymous submission so that future submissions will be secured going forward.', 'pods' ),
			'type'               => 'pick',
			'default'            => '0',
			'readonly'           => $session_auto_start_overridden,
			'description'        => $session_auto_start_overridden ? $disabled_text : '',
			'pick_format'        => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'0'    => __( 'Disable sessions', 'pods' ),
				'1'    => __( 'Enable sessions', 'pods' ),
				'auto' => __( 'Auto-detect sessions (enable on first anonymous submission)', 'pods' ),
			],
		];

		$pods_init = pods_init();

		$is_wisdom_opted_out = ! $pods_init->stats_tracking || ! $pods_init->stats_tracking->get_is_tracking_allowed();

		$fields['wisdom-opt-in'] = [
			'label' => __( 'Stats Tracking', 'pods' ),
			'type'  => 'heading',
		];

		// Only register if they are already opted-in.
		$fields['wisdom_opt_out'] = [
			'name'               => 'wisdom_opt_out',
			'label'              => __( 'Would you like to opt-out of tracking?', 'pods' ),
			'description'        => __( 'Thank you for installing our plugin. We\'d like your permission to track its usage on your site. We won\'t record any sensitive data, only information regarding the WordPress environment, your site admin email address, and plugin settings. We will only use this information help us make improvements to the plugin and provide better support when you reach out. Tracking is completely optional.', 'pods' ),
			'type'               => 'pick',
			'default'            => $is_wisdom_opted_out ? '1' : '',
			'pick_format'        => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'' => __( 'Track usage on my site', 'pods' ),
				'1' => __( 'DO NOT track usage on my site', 'pods' ),
			],
		];

		return $fields;
	}

	/**
	 * Add custom settings fields.
	 *
	 * @since 2.8.0
	 *
	 * @param array $fields List of fields to filter.
	 *
	 * @return array List of filtered fields.
	 */
	public function add_settings_fields( $fields ) {
		$setting_fields = $this->get_setting_fields();

		return array_merge( $fields, $setting_fields );
	}

}
