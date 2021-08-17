<?php

namespace Pods\Admin;

use Tribe__Main;

/**
 * Settings specific functionality.
 *
 * @since TBD
 */
class Settings {

	const OPTION_NAME = 'pods_settings';

	/**
	 * Add the class hooks.
	 *
	 * @since TBD
	 */
	public function hook() {
		add_filter( 'pods_admin_settings_tabs', [ $this, 'add_settings_tab' ] );
		add_action( 'pods_admin_settings_fields', [ $this, 'add_settings_fields' ], 9 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since TBD
	 */
	public function unhook() {
		remove_filter( 'pods_admin_settings_tabs', [ $this, 'add_settings_tab' ] );
		remove_action( 'pods_admin_settings_fields', [ $this, 'add_settings_fields' ] );
	}

	/**
	 * Get the value for a Pods setting.
	 *
	 * @since TBD
	 *
	 * @param string $setting_name The setting name.
	 * @param null   $default      The default value if the setting is not yet set.
	 *
	 * @return mixed The setting value.
	 */
	public function get_setting( $setting_name, $default = null ) {
		$defaults = $this->add_settings_fields( [] );
		$settings = get_option( self::OPTION_NAME, [] );

		if ( ! $settings ) {
			$settings = [];
		}

		if ( null === $default && isset( $defaults[ $setting_name ]['default'] ) ) {
			$default = $defaults[ $setting_name ]['default'];
		}

		return pods_v( $setting_name, $settings, $default );
	}

	/**
	 * Update the value for a Pods setting.
	 *
	 * @since TBD
	 *
	 * @param string $setting_name  The setting name.
	 * @param mixed  $setting_value The setting value.
	 */
	public function update_setting( $setting_name, $setting_value ) {
		$settings = get_option( self::OPTION_NAME, [] );

		if ( ! $settings ) {
			$settings = [];
		}

		if ( null !== $setting_value ) {
			$settings[ $setting_name ] = $setting_value;
		} elseif ( isset( $settings[ $setting_name ] ) ) {
			unset( $settings[ $setting_name ] );
		}

		update_option( self::OPTION_NAME, $settings, 'yes' );
	}

	/**
	 * Get the list of Pods settings fields.
	 *
	 * @since TBD
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

		return $fields;
	}

	/**
	 * Add custom settings fields.
	 *
	 * @since TBD
	 *
	 * @param array $fields List of fields to filter.
	 *
	 * @return array List of filtered fields.
	 */
	public function add_settings_fields( $fields ) {
		$setting_fields = $this->get_setting_fields();

		return array_merge( $fields, $setting_fields );
	}

	/**
	 * Add the official Settings tab.
	 *
	 * @since TBD
	 *
	 * @param array $tabs List of tabs to filter.
	 *
	 * @return array List of filtered tabs.
	 */
	public function add_settings_tab( $tabs ) {
		return Tribe__Main::array_insert_after_key( 'tools', $tabs, [
			'settings' => __( 'Settings', 'pods' ),
		] );
	}

}