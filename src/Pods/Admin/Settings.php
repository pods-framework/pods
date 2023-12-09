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
		add_filter( 'pods_admin_settings_fields', [ $this, 'add_settings_fields' ], 9 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		remove_filter( 'pods_admin_settings_fields', [ $this, 'add_settings_fields' ], 9 );
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

		$setting = pods_v( $setting_name, $settings, $default );

		if ( null !== $default && ( null === $setting || '' === $setting ) ) {
			return $default;
		}

		return $setting;
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

		static $defaults;

		if ( null === $defaults ) {
			$defaults = $this->get_setting_fields();
		}

		$layout_field_types = PodsForm::layout_field_types();

		// Set up defaults as needed.
		foreach ( $defaults as $setting_name => $setting ) {
			// Skip layout field types.
			if ( isset( $setting['type'] ) && in_array( $setting['type'], $layout_field_types, true ) ) {
				continue;
			}

			// Skip if we do not have a default to set.
			if ( ! isset( $setting['default'] ) ) {
				continue;
			}

			// Skip if we do not
			if ( isset( $settings[ $setting_name ] ) && ! in_array( $settings[ $setting_name ], [ null, '' ], true ) ) {
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
		$disabled_text = __( 'This setting is disabled because it is forced through the constant/filter elsewhere.', 'pods' );
		$current_value = __( 'Current value', 'pods' );

		$fields['core'] = [
			'label' => __( 'Core', 'pods' ),
			'type'  => 'heading',
		];

		$is_types_only            = pods_is_types_only( true );
		$is_types_only_overridden = null !== $is_types_only;

		$is_types_only_disabled_text = sprintf(
			'%1$s<br /><strong>%2$s: %3$s</strong>',
			$disabled_text,
			$current_value,
			! $is_types_only ? __( 'Enabled', 'pods' ) : __( 'Disabled', 'pods' )
		);

		$fields['types_only'] = [
			'name'               => 'types_only',
			'label'              => __( 'Allow Pods to create and manage custom fields on any content type created/extended through Pods', 'pods' ),
			'help'               => __( 'By default, Pods allows you to create custom fields for any content type that you create/extend with Pods. If you only intend to use Pods for content types themselves and not to add custom fields, Disabling Custom Fields can improve performance on your site. When disabled, this is known as the types-only mode feature.', 'pods' ),
			'type'               => 'pick',
			'default'            => '0',
			'readonly'           => $is_types_only_overridden,
			'description'        => $is_types_only_overridden ? $is_types_only_disabled_text : '',
			'pick_format'        => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'0' => __( 'Enable creating custom fields with Pods', 'pods' ),
				'1' => __( 'Disable creating custom fields with Pods (for when using Pods only for content types)', 'pods' ),
			],
		];

		$fields['performance'] = [
			'label' => __( 'Performance', 'pods' ),
			'type'  => 'heading',
		];

		$first_pods_version = get_option( 'pods_framework_version_first' );
		$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

		$fields['watch_changed_fields'] = [
			'name'               => 'watch_changed_fields',
			'label'              => __( 'Watch changed fields for use in hooks', 'pods' ),
			'help'               => __( 'By default, Pods does not watch changed fields when a post, term, user, or other Pods items are saved. Enabling this will allow you to use PHP hooks to reference the previous values of those fields after the save has happened.', 'pods' ),
			'type'               => 'pick',
			'default'            => version_compare( $first_pods_version, '2.8.21', '<=' ) ? '1' : '0',
			'pick_format'        => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'1' => __( 'Enable watching changed fields (may reduce performance with large processes)', 'pods' ),
				'0' => __( 'Disable watching changed fields', 'pods' ),
			],
		];

		$fields['metadata_integration'] = [
			'name'               => 'metadata_integration',
			'label'              => __( 'Watch WP Metadata calls', 'pods' ),
			'help'               => __( 'By default, Pods will watch Metadata calls and send any values to table-based fields as well as index relationship IDs when they are saved. You can disable this if you do not use table-based Pods and you only want to query meta-based Pods or settings.', 'pods' ),
			'type'               => 'pick',
			'default'            => ! function_exists( 'wc_get_product' ) || version_compare( $first_pods_version, '2.9.14', '<' ) ? '1' : '0',
			'pick_format'        => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'1' => __( 'Enable watching WP Metadata calls (may reduce performance with large processes)', 'pods' ),
				'0' => __( 'Disable watching WP Metadata calls', 'pods' ),
			],
			'dependency'         => true,
		];

		$fields['metadata_override_get'] = [
			'name'               => 'metadata_override_get',
			'label'              => __( 'Override WP Metadata values', 'pods' ),
			'help'               => __( 'By default, Pods will override Metadata values when calling functions like get_post_meta() so that it can provide more Relationship / File field context.', 'pods' ),
			'type'               => 'pick',
			'default'            => version_compare( $first_pods_version, '2.8.21', '<=' ) ? '1' : '0',
			'pick_format'        => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'1' => __( 'Enable overriding WP Metadata values (may conflict with certain plugins and decrease performance with large processes)', 'pods' ),
				'0' => __( 'Disable overriding WP Metadata values', 'pods' ),
			],
			'depends-on' => [ 'metadata_integration' => '1' ],
		];

		$fields['media_modal_fields'] = [
			'name'               => 'media_modal_fields',
			'label'              => __( 'Show Pods fields in Media Library modals', 'pods' ),
			'help'               => __( 'This feature is only used when you have extended the WordPress Media object with Pods', 'pods' ),
			'type'               => 'pick',
			'default'            => version_compare( $first_pods_version, '2.9.16', '<=' ) ? '1' : '0',
			'pick_format'        => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'1' => __( 'Enable showing Pods fields in Media Library modals (may decrease performance with large numbers of items on admin screens with media grids)', 'pods' ),
				'0' => __( 'Disable showing Pods fields in Media Library modals and only show them when in the full edit screen for an attachment', 'pods' ),
			],
		];

		$session_auto_start            = pods_session_auto_start( true );
		$session_auto_start_overridden = null !== $session_auto_start;

		$fields['sessions'] = [
			'label' => __( 'Sessions', 'pods' ),
			'type'  => 'heading',
		];

		$session_auto_start_disabled_text = sprintf(
			'%1$s<br /><strong>%2$s: %3$s</strong>',
			$disabled_text,
			$current_value,
			$session_auto_start ? __( 'Enabled', 'pods' ) : __( 'Disabled', 'pods' )
		);

		$fields['session_auto_start'] = [
			'name'               => 'session_auto_start',
			'label'              => __( 'Secure anonymous public form submissions using PHP sessions (potential performance impacts)', 'pods' ),
			'help'               => __( 'Sessions will be used to secure submissions from public forms from logged out visitors to ensure they do not submit fields they are not allowed to access. Auto-detecting sessions will automatically turn this setting on the first anonymous submission so that future submissions will be secured going forward.', 'pods' ),
			'type'               => 'pick',
			'default'            => '0',
			'readonly'           => $session_auto_start_overridden,
			'description'        => $session_auto_start_overridden ? $session_auto_start_disabled_text : '',
			'pick_format'        => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'auto' => __( 'Auto-detect sessions (enable on first anonymous submission)', 'pods' ),
				'1'    => __( 'Enable sessions (may decrease performance)', 'pods' ),
				'0'    => __( 'Disable sessions', 'pods' ),
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
			'description'        => __( 'Thank you for installing our plugin. We\'d like your permission to track its usage on your site. We won\'t record any sensitive data, only information regarding the WordPress environment and your plugin settings. We will only use this information help us make improvements to the plugin and provide better support when you reach out. Tracking is completely optional.', 'pods' ),
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
