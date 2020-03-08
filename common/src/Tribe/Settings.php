<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Settings' ) ) {
	/**
	 * helper class that allows registration of settings
	 * this is a static class & uses the singleton design method
	 * instantiation takes place in Tribe__Main
	 *
	 */
	class Tribe__Settings {
		/**
		 * Slug of the parent menu slug
		 * @var string
		 */
		public static $parent_slug = 'tribe-common';

		/**
		 * Page of the parent menu
		 * @var string
		 */
		public static $parent_page = 'edit.php';

		/**
		 * @var Tribe__Admin__Live_Date_Preview
		 */
		public $live_date_preview;

		/**
		 * the tabs that will appear in the settings page
		 * filtered on class construct
		 * @var array
		 */
		public $tabs;

		/**
		 * All the tabs registered, not just the ones that will appear
		 * @var array
		 */
		public $allTabs;

		/**
		 * multidimensional array of the fields that will be generated
		 * for the entire settings panel, tabs are represented in the array keys
		 * @var array
		 */
		public $fields;

		/**
		 * the default tab for the settings panel
		 * this should be a tab ID
		 * @var string
		 */
		public $defaultTab;

		/**
		 * the current tab being displayed
		 * @var string
		 */
		public $currentTab;

		/**
		 * tabs that shouldn't show the save button
		 * @var array
		 */
		public $noSaveTabs;

		/**
		 * The slug used in the admin to generate the settings page
		 * @var string
		 */
		public $adminSlug;

		/**
		 * The slug used in the admin to generate the help page
		 * @var string
		 */
		protected $help_slug;


		/**
		 * the menu name used for the settings page
		 * @var string
		 */
		public $menuName;

		/**
		 * the required capability for the settings page
		 * @var string
		 */
		public $requiredCap;

		/**
		 * errors that occur after a save operation
		 * @var mixed
		 */
		public $errors;

		/**
		 * POST data before/after save
		 * @var mixed
		 */
		public $sent_data;

		/**
		 * the $current_screen name corresponding to the admin page
		 * @var string
		 */
		public $admin_page;

		/**
		 * true if a major error that prevents saving occurred
		 * @var bool
		 */
		public $major_error;

		/**
		 * holds validated fields
		 * @var array
		 */
		public $validated;

		/**
		 * Static Singleton Holder
		 * @var Tribe__Settings|null
		 */
		private static $instance;

		/**
		 * The settings page URL.
		 * @var string
		 */
		protected $url;

		/**
		 * An array defining the suite root plugins.
		 * @var array
		 */
		protected $root_plugins = array(
			'the-events-calendar/the-events-calendar.php',
			'event-tickets/event-ticket.php',
		);

		/**
		 * An associative array in the form [ <tab-slug> => array(...<fields>) ]
		 * @var array
		 */
		protected $fields_for_save = array();

		/**
		 * An array that contains the fields that are currently being validated.
		 * @var array
		 */
		protected $current_fields = array();

		/**
		 * Static Singleton Factory Method
		 *
		 * @return Tribe__Settings
		 */
		public static function instance() {
			return tribe( 'settings' );
		}

		/**
		 * Class constructor
		 *
		 * @return void
		 */
		public function __construct() {

			// set instance variables
			$this->menuName    = apply_filters( 'tribe_settings_menu_name', esc_html__( 'Events', 'tribe-common' ) );
			$this->requiredCap = apply_filters( 'tribe_settings_req_cap', 'manage_options' );
			$this->adminSlug   = apply_filters( 'tribe_settings_admin_slug', 'tribe-common' );
			$this->help_slug   = apply_filters( 'tribe_settings_help_slug', 'tribe-common-help' );
			$this->errors      = get_option( 'tribe_settings_errors', array() );
			$this->major_error = get_option( 'tribe_settings_major_error', false );
			$this->sent_data   = get_option( 'tribe_settings_sent_data', array() );
			$this->validated   = array();
			$this->defaultTab  = null;
			$this->currentTab  = null;

			$this->hook();
		}

		/**
		 * Hooks the actions and filters required for the class to work.
		 */
		public function hook() {
			// run actions & filters
			add_action( 'admin_menu', array( $this, 'addPage' ) );
			add_action( 'network_admin_menu', array( $this, 'addNetworkPage' ) );
			add_action( 'admin_init', array( $this, 'initTabs' ) );
			add_action( 'tribe_settings_below_tabs', array( $this, 'displayErrors' ) );
			add_action( 'tribe_settings_below_tabs', array( $this, 'displaySuccess' ) );
		}

		/**
		 * Determines whether or not the full admin pages should be initialized.
		 *
		 * When running in parallel with TEC 3.12.4, TEC should be relied on to handle the admin screens
		 * that version of TEC (and lower) is tribe-common ignorant. Therefore, tribe-common has to be
		 * the smarter, more lenient codebase.
		 *
		 * @return boolean
		 */
		public function should_setup_pages() {
			if ( ! class_exists( 'Tribe__Events__Main' ) ) {
				return true;
			}

			if ( version_compare( Tribe__Events__Main::VERSION, '4.0beta', '>=' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * create the main option page
		 *
		 * @return void
		 */
		public function addPage() {
			if ( ! $this->should_setup_pages() ) {
				return;
			}

			if ( ! is_multisite() || ( is_multisite() && '0' == Tribe__Settings_Manager::get_network_option( 'allSettingsTabsHidden', '0' ) ) ) {
				if ( post_type_exists( 'tribe_events' ) ) {
					self::$parent_page = 'edit.php?post_type=tribe_events';
				} else {
					self::$parent_page = 'admin.php?page=tribe-common';

					add_menu_page(
						esc_html__( 'Events', 'tribe-common' ),
						esc_html__( 'Events', 'tribe-common' ),
						apply_filters( 'tribe_common_event_page_capability', 'manage_options' ),
						self::$parent_slug,
						null,
						'dashicons-calendar',
						6
					);
				}

				$this->admin_page = add_submenu_page(
					$this->get_parent_slug(),
					esc_html__( 'Events Settings', 'tribe-common' ),
					esc_html__( 'Settings', 'tribe-common' ),
					$this->requiredCap,
					self::$parent_slug,
					array( $this, 'generatePage' )
				);
			}
		}

		/**
		 * create the network options page
		 *
		 * @return void
		 */
		public function addNetworkPage() {
			if ( ! $this->should_setup_network_pages() ) {
				return;
			}

			$this->admin_page = add_submenu_page(
				'settings.php', esc_html__( 'Events Settings', 'tribe-common' ), esc_html__( 'Events Settings', 'tribe-common' ), $this->requiredCap, $this->adminSlug, array(
					$this,
					'generatePage',
				)
			);

			$this->admin_page = add_submenu_page(
				'settings.php',
				esc_html__( 'Events Help', 'tribe-common' ),
				esc_html__( 'Events Help', 'tribe-common' ),
				$this->requiredCap,
				$this->help_slug,
				array(
					tribe( 'settings.manager' ),
					'do_help_tab',
				)
			);
		}

		/**
		 * init all the tabs
		 *
		 * @return void
		 */
		public function initTabs() {
			if (
				empty( $_GET['page'] )
				|| $_GET['page'] != $this->adminSlug
			) {
				return;
			}

			// Load settings tab-specific helpers and enhancements
			Tribe__Admin__Live_Date_Preview::instance();

			do_action( 'tribe_settings_do_tabs' ); // this is the hook to use to add new tabs
			$this->tabs       = (array) apply_filters( 'tribe_settings_tabs', [] );
			$this->allTabs    = (array) apply_filters( 'tribe_settings_all_tabs', [] );
			$this->noSaveTabs = (array) apply_filters( 'tribe_settings_no_save_tabs', [] );

			if ( is_network_admin() ) {
				$this->defaultTab = apply_filters( 'tribe_settings_default_tab_network', 'network' );
				$this->currentTab = apply_filters( 'tribe_settings_current_tab', ( isset( $_GET['tab'] ) && $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : $this->defaultTab );
				$this->url        = apply_filters(
					'tribe_settings_url', add_query_arg(
						[
							'page' => $this->adminSlug,
							'tab'  => $this->currentTab,
						], network_admin_url( 'settings.php' )
					)
				);
			} else {
				$tabs_keys        = array_keys( $this->tabs );
				$this->defaultTab = in_array( apply_filters( 'tribe_settings_default_tab', 'general' ), $tabs_keys ) ? apply_filters( 'tribe_settings_default_tab', 'general' ) : $tabs_keys[0];
				$this->currentTab = apply_filters( 'tribe_settings_current_tab', ( isset( $_GET['tab'] ) && $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : $this->defaultTab );
				$this->url        = apply_filters(
					'tribe_settings_url', add_query_arg(
						[
							'page' => $this->adminSlug,
							'tab'  => $this->currentTab,
						],
						admin_url( self::$parent_page )
					)
				);
			}

			$this->fields_for_save = (array) apply_filters( 'tribe_settings_fields', [] );
			do_action( 'tribe_settings_after_do_tabs' );
			$this->fields = (array) apply_filters( 'tribe_settings_fields', [] );
			$this->validate();
		}

		/**
		 * generate the main option page
		 * includes the view file
		 *
		 * @return void
		 */
		public function generatePage() {
			do_action( 'tribe_settings_top' );
			echo '<div class="tribe_settings wrap">';
			echo '<h1>';
			printf( esc_html__( '%s Settings', 'tribe-common' ), $this->menuName );
			echo '</h1>';
			do_action( 'tribe_settings_above_tabs' );
			$this->generateTabs( $this->currentTab );
			do_action( 'tribe_settings_below_tabs' );
			do_action( 'tribe_settings_below_tabs_tab_' . $this->currentTab );
			echo '<div class="tribe-settings-form form">';
			do_action( 'tribe_settings_above_form_element' );
			do_action( 'tribe_settings_above_form_element_tab_' . $this->currentTab );
			echo apply_filters( 'tribe_settings_form_element_tab_' . $this->currentTab, '<form method="post">' );
			do_action( 'tribe_settings_before_content' );
			do_action( 'tribe_settings_before_content_tab_' . $this->currentTab );
			do_action( 'tribe_settings_content_tab_' . $this->currentTab );
			if ( ! has_action( 'tribe_settings_content_tab_' . $this->currentTab ) ) {
				echo '<p>' . esc_html__( "You've requested a non-existent tab.", 'tribe-common' ) . '</p>';
			}
			do_action( 'tribe_settings_after_content_tab_' . $this->currentTab );
			do_action( 'tribe_settings_after_content' );
			if ( has_action( 'tribe_settings_content_tab_' . $this->currentTab ) && ! in_array( $this->currentTab, $this->noSaveTabs ) ) {
				wp_nonce_field( 'saving', 'tribe-save-settings' );
				echo '<div class="clear"></div>';
				echo '<input type="hidden" name="current-settings-tab" id="current-settings-tab" value="' . esc_attr( $this->currentTab ) . '" />';
				echo '<input id="tribeSaveSettings" class="button-primary" type="submit" name="tribeSaveSettings" value="' . esc_attr__( 'Save Changes', 'tribe-common' ) . '" />';
			}
			echo apply_filters( 'tribe_settings_closing_form_element', '</form>' );
			do_action( 'tribe_settings_after_form_element' );
			do_action( 'tribe_settings_after_form_element_tab_' . $this->currentTab );
			echo '</div>';
			do_action( 'tribe_settings_after_form_div' );
			echo '</div>';
			do_action( 'tribe_settings_bottom' );
		}

		/**
		 * generate the tabs in the settings screen
		 *
		 * @return void
		 */
		public function generateTabs() {
			if ( is_array( $this->tabs ) && ! empty( $this->tabs ) ) {
				echo '<h2 id="tribe-settings-tabs" class="nav-tab-wrapper">';
				foreach ( $this->tabs as $tab => $name ) {
					if ( ! is_network_admin() ) {
						$url = '?page=' . $this->adminSlug . '&tab=' . urlencode( $tab );
						$url = apply_filters( 'tribe_settings_url', $url );
					}
					if ( is_network_admin() ) {
						$url = '?page=' . $this->adminSlug . '&tab=' . urlencode( $tab );
					}
					$class = ( $tab == $this->currentTab ) ? ' nav-tab-active' : '';
					echo '<a id="' . esc_attr( $tab ) . '" class="nav-tab' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
				}
				do_action( 'tribe_settings_after_tabs' );
				echo '</h2>';
			}
		}

		/**
		 * validate the settings
		 *
		 * @return void
		 */
		public function validate() {

			do_action( 'tribe_settings_validate_before_checks' );

			// check that the right POST && variables are set
			if ( isset( $_POST['tribeSaveSettings'] ) && isset( $_POST['current-settings-tab'] ) ) {
				// check permissions
				if ( ! current_user_can( 'manage_options' ) ) {
					$this->errors[]    = esc_html__( "You don't have permission to do that.", 'tribe-common' );
					$this->major_error = true;
				}

				// check the nonce
				if ( ! wp_verify_nonce( $_POST['tribe-save-settings'], 'saving' ) ) {
					$this->errors[]    = esc_html__( 'The request was sent insecurely.', 'tribe-common' );
					$this->major_error = true;
				}

				// check that the request originated from the current tab
				if ( $_POST['current-settings-tab'] != $this->currentTab ) {
					$this->errors[]    = esc_html__( "The request wasn't sent from this tab.", 'tribe-common' );
					$this->major_error = true;
				}

				// bail if we have errors
				if ( count( $this->errors ) ) {
					remove_action( 'shutdown', array( $this, 'deleteOptions' ) );
					add_option( 'tribe_settings_errors', $this->errors );
					add_option( 'tribe_settings_major_error', $this->major_error );
					wp_redirect( $this->url );
					exit;
				}

				// some hooks
				do_action( 'tribe_settings_validate' );
				do_action( 'tribe_settings_validate_tab_' . $this->currentTab );

				// set the current tab and current fields
				$tab    = $this->currentTab;
				$fields = $this->current_fields = $this->fields_for_save[ $tab ];

				if ( is_array( $fields ) ) {
					// loop through the fields and validate them
					foreach ( $fields as $field_id => $field ) {
						// get the value
						$value = ( isset( $_POST[ $field_id ] ) ) ? $_POST[ $field_id ] : null;
						$value = apply_filters( 'tribe_settings_validate_field_value', $value, $field_id, $field );

						// make sure it has validation set up for it, else do nothing
						if (
							( ! isset( $field['conditional'] ) || $field['conditional'] )
							&& ( ! empty( $field['validation_type'] ) || ! empty( $field['validation_callback'] ) )
						) {
							// some hooks
							do_action( 'tribe_settings_validate_field', $field_id, $value, $field );
							do_action( 'tribe_settings_validate_field_' . $field_id, $value, $field );

							// validate this field
							$validate = new Tribe__Validate( $field_id, $field, $value );

							if ( isset( $validate->result->error ) ) {
								// uh oh; validation failed
								$this->errors[ $field_id ] = $validate->result->error;
							} elseif ( $validate->result->valid ) {
								// validation passed
								$this->validated[ $field_id ]        = new stdClass;
								$this->validated[ $field_id ]->field = $validate->field;
								$this->validated[ $field_id ]->value = $validate->value;
							}
						}
					}

					// do not generate errors for dependent fields that should not show
					if ( ! empty( $this->errors ) ) {
						$keep         = array_filter( array_keys( $this->errors ), array( $this, 'dependency_checks' ) );
						$compare = empty( $keep ) ? array() : array_combine( $keep, $keep );
						$this->errors = array_intersect_key( $this->errors, $compare );
					}

					// run the saving method
					$this->save();
				}
			}

		}

		/**
		 * save the settings
		 *
		 * @return void
		 */
		public function save() {

			// some hooks
			do_action( 'tribe_settings_save' );
			do_action( 'tribe_settings_save_tab_' . $this->currentTab );

			// we'll need this later
			$parent_options = array();

			/**
			 * loop through each validated option and either
			 * save it as is or figure out its parent option ID
			 * (in that case, it's a serialized option array and
			 * will be saved in the next loop)
			 */
			if ( ! empty( $this->validated ) ) {
				foreach ( $this->validated as $field_id => $validated_field ) {
					// get the value and filter it
					$value = $validated_field->value;
					$value = apply_filters( 'tribe_settings_save_field_value', $value, $field_id, $validated_field );

					// figure out the parent option [could be set to false] and filter it
					if ( is_network_admin() ) {
						$parent_option = ( isset( $validated_field->field['parent_option'] ) ) ? $validated_field->field['parent_option'] : Tribe__Main::OPTIONNAMENETWORK;
					}
					if ( ! is_network_admin() ) {
						$parent_option = ( isset( $validated_field->field['parent_option'] ) ) ? $validated_field->field['parent_option'] : Tribe__Main::OPTIONNAME;
					}

					$parent_option  = apply_filters( 'tribe_settings_save_field_parent_option', $parent_option, $field_id );
					$network_option = isset( $validated_field->field['network_option'] ) ? (bool) $validated_field->field['network_option'] : false;

					// some hooks
					do_action( 'tribe_settings_save_field', $field_id, $value, $validated_field );
					do_action( 'tribe_settings_save_field_' . $field_id, $value, $validated_field );

					if ( ! $parent_option ) {
						if ( $network_option || is_network_admin() ) {
							update_site_option( $field_id, $value );
						} else {
							update_option( $field_id, $value );
						}
					} else {
						// set the parent option
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
				// get the old options
				if ( is_network_admin() ) {
					$old_options = (array) get_site_option( $option_id );
				} else {
					$old_options = (array) get_option( $option_id );
				}

				// set the options by parsing old + new and filter that
				$options = apply_filters( 'tribe_settings_save_option_array', wp_parse_args( $new_options, $old_options ), $option_id );

				if ( $option_id == Tribe__Main::OPTIONNAME ) {
					// save using the Tribe__Settings_Manager method
					Tribe__Settings_Manager::set_options( $options );
				} elseif ( $option_id == Tribe__Main::OPTIONNAMENETWORK ) {
					Tribe__Settings_Manager::set_network_options( $options );
				} else {
					// save using regular WP method
					if ( is_network_admin() ) {
						update_site_option( $option_id, $options );
					} else {
						update_option( $option_id, $options );
					}
				}
			}

			do_action( 'tribe_settings_after_save' );
			do_action( 'tribe_settings_after_save_' . $this->currentTab );
			remove_action( 'shutdown', array( $this, 'deleteOptions' ) );
			add_option( 'tribe_settings_sent_data', $_POST );
			add_option( 'tribe_settings_errors', $this->errors );
			add_option( 'tribe_settings_major_error', $this->major_error );
			wp_redirect( esc_url_raw( add_query_arg( array( 'saved' => true ), $this->url ) ) );
			exit;
		}

		/**
		 * display errors, if any, after saving
		 *
		 * @return void
		 */
		public function displayErrors() {

			// fetch the errors and filter them
			$errors = (array) apply_filters( 'tribe_settings_display_errors', $this->errors );
			$count  = apply_filters( 'tribe_settings_count_errors', count( $errors ) );

			if ( apply_filters( 'tribe_settings_display_errors_or_not', ( $count > 0 ) ) ) {
				// output a message if we have errors

				$output = '<div id="message" class="error"><p><strong>';
				$output .= esc_html__( 'Your form had the following errors:', 'tribe-common' );
				$output .= '</strong></p><ul class="tribe-errors-list">';

				// loop through each error
				foreach ( $errors as $error ) {
					$output .= '<li>' . (string) $error . '</li>';
				}

				if ( count( $errors ) ) {
					$message = ( isset( $this->major_error ) && $this->major_error )
						? esc_html__( 'None of your settings were saved. Please try again.' )
						: esc_html( _n( 'The above setting was not saved. Other settings were successfully saved.', 'The above settings were not saved. Other settings were successfully saved.', $count, 'tribe-common' ) );
				}

				$output .= '</ul><p>' . $message . '</p></div>';

				// final output, filtered of course
				echo apply_filters( 'tribe_settings_error_message', $output );
			}
		}

		/**
		 * display success message after saving
		 *
		 * @return void
		 */
		public function displaySuccess() {
			$errors = (array) apply_filters( 'tribe_settings_display_errors', $this->errors );
			$count  = apply_filters( 'tribe_settings_count_errors', count( $errors ) );

			// are we coming from the saving place?
			if ( isset( $_GET['saved'] ) && ! apply_filters( 'tribe_settings_display_errors_or_not', ( $count > 0 ) ) ) {
				// output the filtered message
				$message = esc_html__( 'Settings saved.', 'tribe-common' );
				$output  = '<div id="message" class="updated"><p><strong>' . $message . '</strong></p></div>';
				echo apply_filters( 'tribe_settings_success_message', $output, $this->currentTab );
			}

			//Delete Temporary Options After Display Errors and Success
			$this->deleteOptions();
		}

		/**
		 * delete temporary options
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
		public function get_url( array $args = array() ) {
			$defaults = array(
				'page' => $this->adminSlug,
				'parent' => self::$parent_page,
			);

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

			// if we don't have an event post type, then we can just use the tribe-common slug
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
