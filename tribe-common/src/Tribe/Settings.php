<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Tribe\Admin\Pages as AdminPages;

if ( ! class_exists( 'Tribe__Settings' ) ) {
	/**
	 * helper class that allows registration of settings
	 * this is a static class & uses the singleton design method
	 * instantiation takes place in Tribe__Main
	 *
	 */
	class Tribe__Settings {
		/**
		 * Slug of the parent menu slug.
		 *
		 * @var string
		 */
		public static $parent_slug = 'tribe-common';

		/**
		 * Page of the parent menu.
		 *
		 * @var string
		 */
		public static $parent_page = 'edit.php';

		/**
		 * @var Tribe__Admin__Live_Date_Preview
		 */
		public $live_date_preview;

		/**
		 * The tabs that will appear in the settings page
		 * filtered on class construct.
		 *
		 * @var array
		 */
		public $tabs;

		/**
		 * All the tabs registered, not just the ones that will appear.
		 *
		 * @var array
		 */
		public $allTabs;

		/**
		 * Multidimensional array of the fields that will be generated
		 * for the entire settings panel, tabs are represented in the array keys.
		 *
		 * @var array
		 */
		public $fields;

		/**
		 * The default tab for the settings panel
		 * this should be a tab ID.
		 *
		 * @var string
		 */
		public $defaultTab;

		/**
		 * The current tab being displayed.
		 *
		 * @var string
		 */
		public $currentTab;

		/**
		 * Tabs that shouldn't show the save button.
		 *
		 * @var array
		 */
		public $noSaveTabs;

		/**
		 * The slug used in the admin to generate the settings page.
		 *
		 * @var string
		 */
		public $adminSlug;

		/**
		 * The slug used in the admin to generate the help page.
		 *
		 * @var string
		 */
		protected $help_slug;

		/**
		 * The menu name used for the settings page.
		 *
		 * @var string
		 */
		public $menuName;

		/**
		 * The required capability for the settings page.
		 *
		 * @var string
		 */
		public $requiredCap;

		/**
		 * Errors that occur after a save operation.
		 *
		 * @var mixed
		 */
		public $errors;

		/**
		 * POST data before/after save.
		 *
		 * @var mixed
		 */
		public $sent_data;

		/**
		 * The $current_screen name corresponding to the admin page.
		 *
		 * @var string
		 */
		public $admin_page;

		/**
		 * True if a major error that prevents saving occurred.
		 *
		 * @var bool
		 */
		public $major_error;

		/**
		 * Holds validated fields.
		 *
		 * @var array
		 */
		public $validated;

		/**
		 * Static Singleton Holder.
		 *
		 * @var Tribe__Settings|null
		 */
		private static $instance;

		/**
		 * The settings page URL.
		 *
		 * @var string
		 */
		protected $url;

		/**
		 * An array defining the suite root plugins.
		 *
		 * @var array
		 */
		protected $root_plugins = [
			'the-events-calendar/the-events-calendar.php',
			'event-tickets/event-tickets.php',
		];

		/**
		 * An associative array in the form [ <tab-slug> => array(...<fields>) ]
		 *
		 * @var array
		 */
		protected $fields_for_save = [];

		/**
		 * An array that contains the fields that are currently being validated.
		 *
		 * @var array
		 */
		protected $current_fields = [];

		/**
		 * Static Singleton Factory Method.
		 *
		 * @return Tribe__Settings
		 */
		public static function instance() {
			return tribe( 'settings' );
		}

		/**
		 * Class constructor.
		 *
		 * @return void
		 */
		public function __construct() {

			// Set instance variables.
			$this->menuName    = apply_filters( 'tribe_settings_menu_name', esc_html__( 'Events', 'tribe-common' ) );
			$this->requiredCap = apply_filters( 'tribe_settings_req_cap', 'manage_options' );
			$this->adminSlug   = apply_filters( 'tribe_settings_admin_slug', 'tribe-common' );
			$this->help_slug   = apply_filters( 'tribe_settings_help_slug', 'tribe-common-help' );
			$this->errors      = get_option( 'tribe_settings_errors', [] );
			$this->major_error = get_option( 'tribe_settings_major_error', false );
			$this->sent_data   = get_option( 'tribe_settings_sent_data', [] );
			$this->validated   = [];
			$this->defaultTab  = null;
			$this->currentTab  = null;

			$this->hook();
		}

		/**
		 * Hooks the actions and filters required for the class to work.
		 */
		public function hook() {
			// Run actions & filters.
			add_action( 'admin_init', [ $this, 'initTabs' ] );
			add_action( 'tribe_settings_below_tabs', [ $this, 'displayErrors' ] );
			add_action( 'tribe_settings_below_tabs', [ $this, 'displaySuccess' ] );
		}

		/**
		 * Determines whether or not the full admin pages should be initialized.
		 *
		 * @return boolean
		 */
		public function should_setup_pages() {
			// @todo: Deprecate this and update where needed.
			return true;
		}

		/**
		 * create the main option page
		 *
		 * @return void
		 */
		public function addPage() {
			_deprecated_function( __METHOD__, '4.15.0' );
		}

		/**
		 * create the network options page
		 *
		 * @return void
		 */
		public function addNetworkPage() {
			_deprecated_function( __METHOD__, '4.15.0' );
		}

		/**
		 * Init all the tabs.
		 *
		 * @return void
		 */
		public function initTabs() {
			$admin_pages = tribe( 'admin.pages' );
			$admin_page  = $admin_pages->get_current_page();

			if ( empty( $admin_pages->has_tabs( $admin_page ) ) ) {
				return;
			}

			// Load settings tab-specific helpers and enhancements.
			Tribe__Admin__Live_Date_Preview::instance();

			do_action( 'tribe_settings_do_tabs', $admin_page ); // This is the hook to use to add new tabs.

			$this->tabs       = (array) apply_filters( 'tribe_settings_tabs', [], $admin_page );
			$this->allTabs    = (array) apply_filters( 'tribe_settings_all_tabs', [], $admin_page );
			$this->noSaveTabs = (array) apply_filters( 'tribe_settings_no_save_tabs', [], $admin_page );

			if ( is_network_admin() ) {
				$this->defaultTab = apply_filters( 'tribe_settings_default_tab_network', 'network', $admin_page );
				$current_tab      = ( isset( $_GET['tab'] ) && $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : $this->defaultTab;
				$this->currentTab = apply_filters( 'tribe_settings_current_tab', $current_tab, $admin_page );
				$this->url        = $this->get_tab_url( $this->currentTab );
			} else {
				$tabs_keys        = array_keys( $this->tabs );
				$default_tab      = apply_filters( 'tribe_settings_default_tab', 'general', $admin_page );
				$this->defaultTab = in_array( $default_tab, $tabs_keys ) ? $default_tab : $tabs_keys[0];
				$this->currentTab = apply_filters( 'tribe_settings_current_tab', ( isset( $_GET['tab'] ) && $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : $this->defaultTab );
				$this->url        = $this->get_tab_url( $this->currentTab );
			}

			$this->fields_for_save = (array) apply_filters( 'tribe_settings_fields', [], $admin_page );
			do_action( 'tribe_settings_after_do_tabs', $admin_page );
			$this->fields = (array) apply_filters( 'tribe_settings_fields', [], $admin_page );
			$this->validate();
		}

		/**
		 * Get the current settings page URL
		 *
		 * @since 4.15.0
		 *
		 * @return string The current settings page URL.
		 */
		public function get_settings_page_url( array $args = [] ) {
			$admin_pages = tribe( 'admin.pages' );
			$page        = $admin_pages->get_current_page();
			$tab         = tribe_get_request_var( 'tab', $this->defaultTab );
			$defaults    = [
				'page' => $page,
				'tab'  => $tab,
			];

			// Allow the link to be "changed" on the fly.
			$args = wp_parse_args( $args, $defaults );

			$url = add_query_arg(
				$args,
				is_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'admin.php' )
			);

			return apply_filters( 'tribe_settings_page_url', $url, $page, $tab );
		}

		/**
		 * Get the settings page title.
		 *
		 * @since 4.15.0
		 *
		 * @param string $admin_page The admin page ID.
		 * @return string The settings page title.
		 */
		public function get_page_title( $admin_page ) {
			$page_title = sprintf(
				// Translators: %s is the name of the menu item.
				__( '%s Settings', 'tribe-common' ),
				$this->menuName
			);

			/**
			 * Filter the tribe settings page title.
			 *
			 * @since 4.15.0
			 *
			 * @param string $page_title The settings page title.
			 * @param string $admin_page The admin page ID.
			 */
			return apply_filters( 'tribe_settings_page_title', $page_title, $admin_page );
		}

		/**
		 * Generate the main option page.
		 * includes the view file.
		 *
		 * @since 4.15.0 Add the current page as parameter for the actions.
		 *
		 * @return void
		 */
		public function generatePage() {
			$admin_pages = tribe( 'admin.pages' );
			$admin_page  = $admin_pages->get_current_page();

			do_action( 'tribe_settings_top', $admin_page );
			echo '<div class="tribe_settings wrap">';
			echo '<h1>';
			echo esc_html( $this->get_page_title( $admin_page ) );
			echo '</h1>';
			do_action( 'tribe_settings_above_tabs' );
			$this->generateTabs( $this->currentTab, $admin_page );
			do_action( 'tribe_settings_below_tabs' );
			do_action( 'tribe_settings_below_tabs_tab_' . $this->currentTab, $admin_page );
			echo '<div class="tribe-settings-form form">';
			do_action( 'tribe_settings_above_form_element' );
			do_action( 'tribe_settings_above_form_element_tab_' . $this->currentTab, $admin_page );
			echo apply_filters( 'tribe_settings_form_element_tab_' . $this->currentTab, '<form method="post">' );
			do_action( 'tribe_settings_before_content' );
			do_action( 'tribe_settings_before_content_tab_' . $this->currentTab );
			do_action( 'tribe_settings_content_tab_' . $this->currentTab );
			if ( ! has_action( 'tribe_settings_content_tab_' . $this->currentTab ) ) {
				echo '<p>' . esc_html__( "You've requested a non-existent tab.", 'tribe-common' ) . '</p>';
			}
			do_action( 'tribe_settings_after_content_tab_' . $this->currentTab );
			do_action( 'tribe_settings_after_content', $this->currentTab );
			if ( has_action( 'tribe_settings_content_tab_' . $this->currentTab ) && ! in_array( $this->currentTab, $this->noSaveTabs ) ) {
				wp_nonce_field( 'saving', 'tribe-save-settings' );
				echo '<div class="clear"></div>';
				echo '<input type="hidden" name="current-settings-tab" id="current-settings-tab" value="' . esc_attr( $this->currentTab ) . '" />';
				echo '<input id="tribeSaveSettings" class="button-primary" type="submit" name="tribeSaveSettings" value="' . esc_attr__( 'Save Changes', 'tribe-common' ) . '" />';
			}
			echo apply_filters( 'tribe_settings_closing_form_element', '</form>' );
			do_action( 'tribe_settings_after_form_element' );
			do_action( 'tribe_settings_after_form_element_tab_' . $this->currentTab, $admin_page );
			echo '</div>';
			do_action( 'tribe_settings_after_form_div' );
			echo '</div>';
			do_action( 'tribe_settings_bottom' );
		}

		/**
		 * Generate the tabs in the settings screen.
		 *
		 * @return void
		 */
		public function generateTabs() {
			if ( is_array( $this->tabs ) && ! empty( $this->tabs ) ) {
				echo '<h2 id="tribe-settings-tabs" class="nav-tab-wrapper">';
				foreach ( $this->tabs as $tab => $name ) {
					$url   = $this->get_tab_url( $tab );
					$class = ( $tab == $this->currentTab ) ? ' nav-tab-active' : '';
					echo '<a id="' . esc_attr( $tab ) . '" class="nav-tab' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
				}
				do_action( 'tribe_settings_after_tabs' );
				echo '</h2>';
			}
		}

		/**
		 * Generate the URL for a tab.
		 *
		 * @since 4.15.0
		 *
		 * @param string $tab The tab slug.
		 *
		 * @return string $url The URL.
		 */
		public function get_tab_url( $tab ) {
			$admin_pages  = tribe( 'admin.pages' );
			$admin_page   = $admin_pages->get_current_page();
			$wp_page      = is_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'admin.php' );
			$url          = add_query_arg(
				[
					'page'      => $admin_page,
					'tab'       => $tab,
				],
				$wp_page
			);

			$url = apply_filters( 'tec_settings_tab_url', $url, $admin_page, $tab );

			return $url;
		}

		/**
		 * validate the settings
		 *
		 * @return void
		 */
		public function validate() {
			$admin_pages = tribe( 'admin.pages' );
			$admin_page  = $admin_pages->get_current_page();

			do_action( 'tribe_settings_validate_before_checks', $admin_page );

			// Check that the right POST && variables are set.
			if ( isset( $_POST['tribeSaveSettings'] ) && isset( $_POST['current-settings-tab'] ) ) {
				// check permissions
				if ( ! current_user_can( AdminPages::get_capability() ) ) {
					$this->errors[]    = esc_html__( "You don't have permission to do that.", 'tribe-common' );
					$this->major_error = true;
				}

				// Check the nonce.
				if ( ! wp_verify_nonce( $_POST['tribe-save-settings'], 'saving' ) ) {
					$this->errors[]    = esc_html__( 'The request was sent insecurely.', 'tribe-common' );
					$this->major_error = true;
				}

				// check that the request originated from the current tab.
				if ( $_POST['current-settings-tab'] != $this->currentTab ) {
					$this->errors[]    = esc_html__( "The request wasn't sent from this tab.", 'tribe-common' );
					$this->major_error = true;
				}

				// Bail if we have errors.
				if ( count( $this->errors ) ) {
					remove_action( 'shutdown', [ $this, 'deleteOptions' ] );
					add_option( 'tribe_settings_errors', $this->errors );
					add_option( 'tribe_settings_major_error', $this->major_error );
					wp_redirect( $this->get_settings_page_url() );
					exit;
				}

				// Some hooks.
				do_action( 'tribe_settings_validate', $admin_page );
				do_action( 'tribe_settings_validate_tab_' . $this->currentTab, $admin_page );

				// Set the current tab and current fields.
				$tab    = $this->currentTab;
				$fields = $this->current_fields = $this->fields_for_save[ $tab ];

				if ( is_array( $fields ) ) {
					// Loop through the fields and validate them.
					foreach ( $fields as $field_id => $field ) {
						// Get the value.
						$value = ( isset( $_POST[ $field_id ] ) ) ? $_POST[ $field_id ] : null;
						$value = apply_filters( 'tribe_settings_validate_field_value', $value, $field_id, $field );

						// Make sure it has validation set up for it, else do nothing.
						if (
							( ! isset( $field['conditional'] ) || $field['conditional'] )
							&& ( ! empty( $field['validation_type'] ) || ! empty( $field['validation_callback'] ) )
						) {
							// Some hooks.
							do_action( 'tribe_settings_validate_field', $field_id, $value, $field );
							do_action( 'tribe_settings_validate_field_' . $field_id, $value, $field );

							// Validate this field.
							$validate = new Tribe__Validate( $field_id, $field, $value );

							if ( isset( $validate->result->error ) ) {
								// Uh oh; validation failed.
								$this->errors[ $field_id ] = $validate->result->error;
							} elseif ( $validate->result->valid ) {
								// Validation passed.
								$this->validated[ $field_id ]        = new stdClass;
								$this->validated[ $field_id ]->field = $validate->field;
								$this->validated[ $field_id ]->value = $validate->value;
							}
						}
					}

					// Do not generate errors for dependent fields that should not show.
					if ( ! empty( $this->errors ) ) {
						$keep = array_filter( array_keys( $this->errors ), [ $this, 'dependency_checks' ] );
						$compare = empty( $keep ) ? [] : array_combine( $keep, $keep );
						$this->errors = array_intersect_key( $this->errors, $compare );
					}

					// Run the saving method.
					$this->save();
				}
			}
		}

		/**
		 * Save the settings.
		 *
		 * @since 4.15.0 Add the current page as parameter for the actions.
		 *
		 * @return void
		 */
		public function save() {
			$admin_pages = tribe( 'admin.pages' );
			$admin_page  = $admin_pages->get_current_page();

			// Some hooks.
			do_action( 'tribe_settings_save', $admin_page );
			do_action( 'tribe_settings_save_tab_' . $this->currentTab, $admin_page );

			// We'll need this later.
			$parent_options = [];

			/**
			 * loop through each validated option and either
			 * save it as is or figure out its parent option ID
			 * (in that case, it's a serialized option array and
			 * will be saved in the next loop)
			 */
			if ( ! empty( $this->validated ) ) {
				foreach ( $this->validated as $field_id => $validated_field ) {
					// Get the value and filter it.
					$value = $validated_field->value;
					$value = apply_filters( 'tribe_settings_save_field_value', $value, $field_id, $validated_field );

					// Figure out the parent option [could be set to false] and filter it.
					if ( is_network_admin() ) {
						$parent_option = ( isset( $validated_field->field['parent_option'] ) ) ? $validated_field->field['parent_option'] : Tribe__Main::OPTIONNAMENETWORK;
					}
					if ( ! is_network_admin() ) {
						$parent_option = ( isset( $validated_field->field['parent_option'] ) ) ? $validated_field->field['parent_option'] : Tribe__Main::OPTIONNAME;
					}

					$parent_option  = apply_filters( 'tribe_settings_save_field_parent_option', $parent_option, $field_id );
					$network_option = isset( $validated_field->field['network_option'] ) ? (bool) $validated_field->field['network_option'] : false;

					// Some hooks.
					do_action( 'tribe_settings_save_field', $field_id, $value, $validated_field );
					do_action( 'tribe_settings_save_field_' . $field_id, $value, $validated_field );

					if ( ! $parent_option ) {
						if ( $network_option || is_network_admin() ) {
							update_site_option( $field_id, $value );
						} else {
							update_option( $field_id, $value );
						}
					} else {
						// Set the parent option.
						$parent_options[ $parent_option ][ $field_id ] = $value;
					}
				}
			}

			/**
			 * loop through parent option arrays
			 * and save them
			 * NOTE: in the case of the main option Tribe Options,
			 * this will save using the Tribe__Settings_Manager::set_options method.
			 */
			foreach ( $parent_options as $option_id => $new_options ) {
				// Get the old options.
				if ( is_network_admin() ) {
					$old_options = (array) get_site_option( $option_id );
				} else {
					$old_options = (array) get_option( $option_id );
				}

				// Set the options by parsing old + new and filter that.
				$options = apply_filters( 'tribe_settings_save_option_array', wp_parse_args( $new_options, $old_options ), $option_id );

				if ( $option_id == Tribe__Main::OPTIONNAME ) {
					// Save using the Tribe__Settings_Manager method.
					Tribe__Settings_Manager::set_options( $options );
				} elseif ( $option_id == Tribe__Main::OPTIONNAMENETWORK ) {
					Tribe__Settings_Manager::set_network_options( $options );
				} else {
					// Save using regular WP method.
					if ( is_network_admin() ) {
						update_site_option( $option_id, $options );
					} else {
						update_option( $option_id, $options );
					}
				}
			}

			do_action( 'tribe_settings_after_save', $admin_page );
			do_action( 'tribe_settings_after_save_' . $this->currentTab, $admin_page );
			remove_action( 'shutdown', [ $this, 'deleteOptions' ] );
			add_option( 'tribe_settings_sent_data', $_POST );
			add_option( 'tribe_settings_errors', $this->errors );
			add_option( 'tribe_settings_major_error', $this->major_error );
			wp_redirect( esc_url_raw( add_query_arg( [ 'saved' => true ], $this->get_settings_page_url() ) ) );
			exit;
		}

		/**
		 * Display errors, if any, after saving.
		 *
		 * @return void
		 */
		public function displayErrors() {
			// Fetch the errors and filter them.
			$errors = (array) apply_filters( 'tribe_settings_display_errors', $this->errors );
			$count  = apply_filters( 'tribe_settings_count_errors', count( $errors ) );

			// Bail if we don't have errors.
			if ( ! apply_filters( 'tribe_settings_display_errors_or_not', ( $count > 0 ) ) ) {
				return;
			}

			$output = '<div id="message" class="error"><p><strong>';
			$output .= esc_html__( 'Your form had the following errors:', 'tribe-common' );
			$output .= '</strong></p><ul class="tribe-errors-list">';

			// Loop through each error.
			foreach ( $errors as $error ) {
				$output .= '<li>' . (string) $error . '</li>';
			}

			if ( count( $errors ) ) {
				$message = ( isset( $this->major_error ) && $this->major_error )
					? esc_html__( 'None of your settings were saved. Please try again.' )
					: esc_html( _n( 'The above setting was not saved. Other settings were successfully saved.', 'The above settings were not saved. Other settings were successfully saved.', $count, 'tribe-common' ) );
			}

			$output .= '</ul><p>' . $message . '</p></div>';

			// Final output, filtered of course.
			echo apply_filters( 'tribe_settings_error_message', $output );
		}

		/**
		 * Display success message after saving.
		 *
		 * @return void
		 */
		public function displaySuccess() {
			$errors = (array) apply_filters( 'tribe_settings_display_errors', $this->errors );
			$count  = apply_filters( 'tribe_settings_count_errors', count( $errors ) );

			// Are we coming from the saving place?
			if ( isset( $_GET['saved'] ) && ! apply_filters( 'tribe_settings_display_errors_or_not', ( $count > 0 ) ) ) {
				// output the filtered message
				$message = esc_html__( 'Settings saved.', 'tribe-common' );
				$output  = '<div id="message" class="updated"><p><strong>' . $message . '</strong></p></div>';
				echo apply_filters( 'tribe_settings_success_message', $output, $this->currentTab );
			}

			// Delete Temporary Options After Display Errors and Success.
			$this->deleteOptions();
		}

		/**
		 * Delete temporary options.
		 *
		 * @return void
		 */
		public function deleteOptions() {
			delete_option( 'tribe_settings_errors' );
			delete_option( 'tribe_settings_major_error' );
			delete_option( 'tribe_settings_sent_data' );
		}

		/**
		 * Returns the main admin settings URL.
		 *
		 * @return string
		 */
		public function get_url( array $args = [] ) {
			$defaults = [
				'page'   => $this->adminSlug,
				'parent' => self::$parent_page,
			];

			// Allow the link to be "changed" on the fly
			$args = wp_parse_args( $args, $defaults );

			$url = admin_url( $args['parent'] );

			// keep the resulting URL args clean
			unset( $args['parent'] );

			return apply_filters( 'tribe_settings_url', add_query_arg( $args, $url ), $args, $url );
		}

		/**
		 * The "slug" used for adding submenu pages
		 *
		 * @return string
		 */
		public function get_parent_slug() {
			$slug = self::$parent_page;

			// If we don't have an event post type, then we can just use the tribe-common slug.
			if ( 'edit.php' === $slug || 'admin.php?page=tribe-common' === $slug ) {
				$slug = self::$parent_slug;
			}

			return $slug;
		}

		/**
		 * @return string
		 */
		public function get_help_slug() {
			return $this->help_slug;
		}

		/**
		 * Determines whether or not the network admin pages should be initialized.
		 *
		 * When running in parallel with TEC 3.12.4, TEC should be relied on to handle the admin screens
		 * that version of TEC (and lower) is tribe-common ignorant. Therefore, tribe-common has to be
		 * the smarter, more lenient codebase.
		 * Beyond this at least one of the two "root" plugins (The Events Calendar and Event Tickets)
		 * should be network activated to add the page.
		 *
		 * @return boolean
		 */
		public function should_setup_network_pages() {
			$root_plugin_is_mu_activated = array_sum( array_map( 'is_plugin_active_for_network', $this->root_plugins ) ) >= 1;

			if ( ! $root_plugin_is_mu_activated ) {
				return false;
			}

			if ( ! class_exists( 'Tribe__Events__Main' ) ) {
				return true;
			}

			if ( version_compare( Tribe__Events__Main::VERSION, '4.0beta', '>=' ) ) {
				return true;
			}

			return false;

		}

		/**
		 * Sets what `common` should consider root plugins.
		 *
		 * @param array $root_plugins An array of plugins in the `<folder>/<file.php>` format.
		 */
		public function set_root_plugins( array $root_plugins ) {
			$this->root_plugins = $root_plugins;
		}

		/**
		 * Whether the specified field dependency condition is valid or not depending on
		 * its parent field value.
		 *
		 * @since 4.7.7
		 *
		 * @param string $field_id The id of the field that might be removed.
		 *
		 * @return bool `true` if the field dependency condition is valid, `false` if the field
		 *              dependency condition is not valid.
		 */
		protected function dependency_checks( $field_id ) {
			$does_not_exist = ! array_key_exists( $field_id, $this->current_fields );

			if ( $does_not_exist ) {
				return false;
			}

			$has_no_dependency = ! isset( $this->current_fields[ $field_id ]['validate_if'] );

			if ( $has_no_dependency ) {
				return true;
			}

			$condition = $this->current_fields[ $field_id ]['validate_if'];

			if ( $condition instanceof Tribe__Field_Conditional ) {
				$parent_field = Tribe__Utils__Array::get( $this->validated, $condition->depends_on(), null );

				return $condition->check( $parent_field->value, $this->current_fields );
			}

			return is_callable( $condition )
				? call_user_func( $condition, $this->current_fields )
				: true == $condition;
		}
	} // end class
} // endif class_exists
