<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Settings_Tab' ) ) {
	/**
	 * helper class that creates a settings tab
	 * this is a public API, use it to create tabs
	 * simply by instantiating this class
	 *
	 */
	class Tribe__Settings_Tab {

		/**
		 * Tab ID, used in query string and elsewhere
		 * @var string
		 */
		public $id;

		/**
		 * Tab's name
		 * @var string
		 */
		public $name;

		/**
		 * Tab's arguments
		 * @var array
		 */
		public $args;

		/**
		 * Defaults for tabs
		 * @var array
		 */
		public $defaults;

		/**
		 * class constructor
		 *
		 * @param string $id   the tab's id (no spaces or special characters)
		 * @param string $name the tab's visible name
		 * @param array  $args additional arguments for the tab
		 */
		public function __construct( $id, $name, $args = [] ) {

			// setup the defaults
			$this->defaults = [
				'fields'           => [],
				'priority'         => 50,
				'show_save'        => true,
				'display_callback' => false,
				'network_admin'    => false,
			];

			// parse args with defaults
			$this->args = wp_parse_args( $args, $this->defaults );

			// set each instance variable and filter
			$this->id   = apply_filters( 'tribe_settings_tab_id', $id );
			$this->name = apply_filters( 'tribe_settings_tab_name', $name );
			foreach ( $this->defaults as $key => $value ) {
				$this->{$key} = apply_filters( 'tribe_settings_tab_' . $key, $this->args[ $key ], $id );
			}

			// run actions & filters
			if ( ! $this->network_admin ) {
				add_filter( 'tribe_settings_all_tabs', [ $this, 'addAllTabs' ] );
			}
			add_filter( 'tribe_settings_tabs', [ $this, 'addTab' ], $this->priority );
		}

		/**
		 * filters the tabs array from Tribe__Settings
		 * and adds the current tab to it
		 * does not add a tab if it's empty
		 *
		 * @param array $tabs the $tabs from Tribe__Settings
		 *
		 * @return array $tabs the filtered tabs
		 */
		public function addTab( $tabs ) {
			$hideSettingsTabs = Tribe__Settings_Manager::get_network_option( 'hideSettingsTabs', [] );
			if ( ( isset( $this->fields ) || has_action( 'tribe_settings_content_tab_' . $this->id ) ) && ( empty( $hideSettingsTabs ) || ! in_array( $this->id, $hideSettingsTabs ) ) ) {
				if ( ( is_network_admin() && $this->args['network_admin'] ) || ( ! is_network_admin() && ! $this->args['network_admin'] ) ) {
					$tabs[ $this->id ] = $this->name;
					add_filter( 'tribe_settings_fields', [ $this, 'addFields' ] );
					add_filter( 'tribe_settings_no_save_tabs', [ $this, 'showSaveTab' ] );
					add_filter( 'tribe_settings_content_tab_' . $this->id, [ $this, 'doContent' ] );
				}
			}

			return $tabs;
		}

		/**
		 * Adds this tab to the list of total tabs, even if it is not displayed.
		 *
		 * @param array $allTabs All the tabs from Tribe__Settings.
		 *
		 * @return array $allTabs All the tabs.
		 */
		public function addAllTabs( $allTabs ) {
			$allTabs[ $this->id ] = $this->name;

			return $allTabs;
		}


		/**
		 * filters the fields array from Tribe__Settings
		 * and adds the current tab's fields to it
		 *
		 * @param array $field the $fields from Tribe__Settings
		 *
		 * @return array $fields the filtered fields
		 */
		public function addFields( $fields ) {
			if ( ! empty ( $this->fields ) ) {
				$fields[ $this->id ] = $this->fields;
			} elseif ( has_action( 'tribe_settings_content_tab_' . $this->id ) ) {
				$fields[ $this->id ] = $this->fields = [ 0 => null ]; // just to trick it
			}

			return $fields;
		}

		/**
		 * sets whether the current tab should show the save
		 * button or not
		 *
		 * @param array $noSaveTabs the $noSaveTabs from Tribe__Settings
		 *
		 * @return array $noSaveTabs the filtered non saving tabs
		 */
		public function showSaveTab( $noSaveTabs ) {
			if ( ! $this->show_save || empty( $this->fields ) ) {
				$noSaveTabs[ $this->id ] = $this->id;
			}

			return $noSaveTabs;
		}

		/**
		 * displays the content for the tab
		 *
		 * @return void
		 */
		public function doContent() {
			if ( $this->display_callback && is_callable( $this->display_callback ) ) {
				call_user_func( $this->display_callback );

				return;
			}

			$sent_data = get_option( 'tribe_settings_sent_data', [] );

			if ( is_array( $this->fields ) && ! empty( $this->fields ) ) {
				foreach ( $this->fields as $key => $field ) {
					if ( isset( $sent_data[ $key ] ) ) {
						// If we just saved [or attempted to], get the value that was input.
						$value = $sent_data[ $key ];
					} else {
						// Some options should always be stored at network level
						$network_option = isset( $field['network_option'] ) ? (bool) $field['network_option'] : false;

						if ( is_network_admin() ) {
							$parent_option = ( isset( $field['parent_option'] ) ) ? $field['parent_option'] : Tribe__Main::OPTIONNAMENETWORK;
						}
						if ( ! is_network_admin() ) {
							$parent_option = ( isset( $field['parent_option'] ) ) ? $field['parent_option'] : Tribe__Main::OPTIONNAME;
						}
						// get the field's parent_option in order to later get the field's value
						$parent_option = apply_filters( 'tribe_settings_do_content_parent_option', $parent_option, $key );
						$default       = ( isset( $field['default'] ) ) ? $field['default'] : null;
						$default       = apply_filters( 'tribe_settings_field_default', $default, $field );

						if ( ! $parent_option ) {
							// no parent option, get the straight up value
							if ( $network_option || is_network_admin() ) {
								$value = get_site_option( $key, $default );
							} else {
								$value = get_option( $key, $default );
							}
						} else {
							// there's a parent option
							if ( $parent_option == Tribe__Main::OPTIONNAME ) {
								// get the options from Tribe__Settings_Manager if we're getting the main array
								$value = Tribe__Settings_Manager::get_option( $key, $default );
							} elseif ( $parent_option == Tribe__Main::OPTIONNAMENETWORK ) {
								$value = Tribe__Settings_Manager::get_network_option( $key, $default );
							} else {
								// else, get the parent option normally
								if ( is_network_admin() ) {
									$options = (array) get_site_option( $parent_option );
								} else {
									$options = (array) get_option( $parent_option );
								}
								$value = ( isset( $options[ $key ] ) ) ? $options[ $key ] : $default;
							}
						}
					}

					// escape the value for display
					if ( ! empty( $field['esc_display'] ) && function_exists( $field['esc_display'] ) ) {
						$value = $field['esc_display']( $value );
					} elseif ( is_string( $value ) ) {
						$value = esc_attr( stripslashes( $value ) );
					}

					// filter the value
					$value = apply_filters( 'tribe_settings_get_option_value_pre_display', $value, $key, $field );

					// create the field
					new Tribe__Field( $key, $field, $value );
				}
			} else {
				// no fields setup for this tab yet
				echo '<p>' . esc_html__( 'There are no fields set up for this tab yet.', 'tribe-common' ) . '</p>';
			}
		}

	} // end class
} // endif class_exists
