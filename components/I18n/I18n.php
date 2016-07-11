<?php
/**
 * Name: Translate Pods Admin
 *
 * Menu Name: Translate Pods
 *
 * Description: Allow UI of Pods and fields to be translated
 *
 * Version: 0.1
 *
 * Category: I18n
 *
 * Class: Pods_Component_I18n
 * 
 * @package Pods\Components
 * @subpackage i18n
 */

! defined( 'ABSPATH' ) and die();

if ( class_exists( 'Pods_Component_I18n' ) )
	return;

class Pods_Component_I18n extends PodsComponent {

	public $settings = array();
	public $locale = null;
	public $languages = array();
	public $languages_available = array();
	public $languages_translated = array();
	public $cur_pod = null;
	public $option_key = 'pods_component_i18n_settings';
	public $admin_page = 'pods-component-translate-pods-admin';

	public $translatable_fields = array(
		'label',
		'description',
		'placeholder',
		'menu_name',
		'pick_select_text',

		//@todo: Not working due to lack of filters
		//'file_add_button',
		//'file_modal_title',
		//'file_modal_add_button',
	);

	/**
	 * Get mandatory data and add actions
	 *
	 * @since 0.1
	 */
	public function __construct () {
		//add_action( 'plugins_loaded', array( $this, 'init' ) );
		$this->init();
	}

	/**
	 * Init function to register data, hooks and resources
	 * @since 0.1
	 */
	public function init() {
		$this->settings = get_option( $this->option_key, array() );

		// Polylang
		if ( function_exists( 'PLL' ) && file_exists( plugin_dir_path( __FILE__ ) . '/I18n-polylang.php' ) ) {
			include_once( plugin_dir_path( __FILE__ ) . '/I18n-polylang.php' );
		}
		// WPML
		// Polylang has WPML compat functions with the same names so check constant ICL_SITEPRESS_VERSION for WPML
		if ( defined('ICL_SITEPRESS_VERSION') && file_exists( plugin_dir_path( __FILE__ ) . '/I18n-wpml.php' ) ) {
			include_once( plugin_dir_path( __FILE__ ) . '/I18n-wpml.php' );
		}

		if ( ! empty( $this->settings['enabled_languages'] ) ) {

			$this->languages = $this->settings['enabled_languages'];
			$this->locale = get_locale();

			if ( is_admin() ) {
				
				if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'pods', $this->admin_page ) ) ) {

					add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
					//$this->admin_assets();

					// Do save action here because otherwise the loading of post_types get done first and labels aren't translated
					if (   $_GET['page'] == $this->admin_page
						&& pods_is_admin( array( 'pods_i18n_activate_lanuages' ) ) 
						&& isset( $_POST['_nonce_i18n'] ) 
						&& wp_verify_nonce( $_POST['_nonce_i18n'], 'pods_i18n_activate_lanuages' ) 
					) {
						$this->admin_save();
					}

					if ( $_GET['page'] == 'pods' ) {

						$pod = null;
						// Get the pod if available
						if ( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
							$pod = pods_api()->load_pod( array( 'id' => $_GET['id'] ) );
							// Append options to root array for pods_v function to work
							foreach ( $pod[ 'options' ] as $_option => $_value ) {
								$pod[ $_option ] = $_value;
							}
						}
						$this->cur_pod = $pod;

						//Add option tab for post types
						//add_filter( 'pods_admin_setup_edit_tabs_post_type', array( $this, 'pod_tab' ), 11, 3 );

						//Add the same tab for taxonomies
						//add_filter( 'pods_admin_setup_edit_tabs_taxonomy', array( $this, 'pod_tab' ), 11, 3 );

						//Add options to the new tab
						//add_filter( 'pods_admin_setup_edit_options', array( $this, 'pod_options' ), 12, 2 );

						//Add options metabox to the pod edit screens
						add_action( 'pods_add_meta_boxes', array( $this, 'admin_meta_box' ), 10 );

						//Add the i18n input fields based on existing fields
						add_filter( 'pods_form_ui_field_text', array( $this, 'add_i18n_inputs' ), 10, 6 );

						//add_filter( 'pods_admin_setup_edit_field_tabs', array( $this, 'pod_field_tab' ), 10, 2 );
						//add_filter( 'pods_admin_setup_edit_field_options', array( $this, 'pod_field_options' ), 10, 2 );
					}

				}

				/*foreach ( pods_form()->field_types() as $type => $data ) {
					//add_filter( 'pods_form_ui_field_' . $type . '_data', array( $this, 'field_options_i18n' ), 10, 3 );
					//add_filter( 'pods_form_ui_field_' . $type . '_merge_attributes', array( $this, 'merge_attributes_fields_i18n' ), 10, 3 );
				}*/

				// Default filters for all fields
				add_filter( 'pods_form_ui_label_text', array( $this, 'fields_ui_label_text_i18n' ), 10, 4 );
				add_filter( 'pods_form_ui_comment_text', array( $this, 'fields_ui_comment_text_i18n' ), 10, 3 );

				// Field specific
				add_filter( 'pods_field_pick_data', array( $this, 'field_pick_data_i18n' ), 10, 6 );

				// Setting pages
				add_filter( 'pods_admin_menu_page_title', array( $this, 'admin_menu_page_title_i18n' ), 10, 2 );
				add_filter( 'pods_admin_menu_label', array( $this, 'admin_menu_label_i18n' ), 10, 2 );
			}

			// Object filters
			add_filter( 'pods_register_post_type', array( $this, 'pods_register_object_i18n' ), 10, 2 );
			add_filter( 'pods_register_taxonomy', array( $this, 'pods_register_object_i18n' ), 10, 2 );


			global $wp_version;
			if ( version_compare( $wp_version, '4.6', '<' ) ) {
				add_filter( 'pods_form_ui_field_date_args', array( $this, 'field_date_args_i18n' ), 10, 6 );
				add_filter( 'pods_form_ui_field_datetime_args', array( $this, 'field_date_args_i18n' ), 10, 6 );
			}

		}
	}

	/**
	 * Load assets for this component
	 * @since 0.1
	 */
	public function admin_assets() {
		wp_enqueue_script( 'pods-admin-i18n', PODS_URL . 'components/I18n/pods-admin-i18n.js', array( 'jquery', 'pods-i18n' ), '1.0', true );
		$localize_script = array();
		foreach ( $this->languages as $lang => $lang_data ) {
			$lang_label = $this->create_lang_label( $lang_data );
			if ( ! empty( $lang_label ) ) {
				$lang_label = $lang . ' ('. $lang_label .')';
			} else {
				$lang_label = $lang;
			}
			$localize_script[ $lang ] = $lang_label;
		}
		wp_localize_script( 'pods-admin-i18n', 'pods_admin_i18n_strings', $localize_script );

		// Add strings to the i18n js object
		add_filter( 'pods_localized_strings', array( $this, 'localize_assets' ) );
	}

	/**
	 * Localize the assets
	 */
	public function localize_assets( $str ) {
		$str['Add translation'] = __( 'Add translation', 'pods' );
		$str['Toggle translations'] = __( 'Toggle translations', 'pods' );
		$str['Show translations'] = __( 'Show translations', 'pods' );
		$str['Hide translations'] = __( 'Hide translations', 'pods' );
		$str['Select'] = __( 'Select', 'pods' );
		return $str;
	}

	/**
	 * Not sure if this is needed
	 * @since 0.1
	 * @todo Make it work or delete :)
	 */
	function field_options_i18n( $options, $type, $bla ) {
		print_r($options);
		return $options;
	}

	/**
	 * Not sure if this is needed
	 * @since 0.1
	 * @todo Make it work or delete :)
	 */
	public function merge_attributes_fields_i18n( $attributes, $name, $options ) {
		print_r($attributes);
		return $attributes;
	}

	/**
	 * Check is a field name is set for translation
	 * 
	 * @since 0.1
	 * @param string $name
	 * @return bool
	 */
	public function is_translatable_field( $name ) {

		$translatable_fields = apply_filters( 'pods_translatable_fields', $this->translatable_fields );

		// All fields that start with "label"
		if ( strpos( $name, 'label' ) === 0 ) {
			return true;
		}
		// All translateable fields
		if ( in_array( $name, $translatable_fields ) ) {
			return true;
		}
		// Custom fields data, the name must begin with field_data[
		if ( strpos( $name, 'field_data[' ) === 0 ) {
			$name = str_replace( 'field_data[', '', $name );
			$name = rtrim( $name, ']' );
			$name = explode( '][', $name );
			$name = end( $name );
			// All translateable fields from field_data[ (int) ][ $name ]
			if ( in_array( $name, $translatable_fields ) ) {
				return true;
			}           
		}
		return false;
	}

	/**
	 * Get a translated option for a field key (if available)
	 * 
	 * @since  0.1
	 * @param  string $current Current value
	 * @param  string $key     The key / opion name to search for
	 * @param  array  $data    Pod data (can also be an options array of a pod or field)
	 * @return string
	 */
	public function get_field_translation( $current, $key, $data ) {
		$locale = $this->locale;
		// Validate locale and pod
		if ( is_array( $data ) && array_key_exists( $locale, $this->languages ) && $this->obj_is_language_enabled( $locale, $data ) ) {
			// Add option keys to $data array
			if ( $data['options'] ) {
				$data = array_merge( $data, $data['options'] );
			}
			// Check if the i18n option exists and isn't empty
			if ( ! empty( $data[ $key . '_' . $locale ] ) ) {
				return (string) $data[ $key . '_' . $locale ];
			}
		}
		return $current;
	}

	/**
	 * Page title for setting pages
	 * 
	 * @since  0.1
	 * @see    PodsAdmin.php >> admin_menu()
	 * @see    PodsAdmin.php >> admin_content_settings()
	 * @param  string $page_title Current page title
	 * @param  array  $pod        Pod data
	 * @return string
	 */
	public function admin_menu_page_title_i18n( $page_title, $pod ) {
		return (string) $this->get_field_translation( $page_title, 'label', $pod );
	}

	/**
	 * Menu title for setting pages
	 * 
	 * @since  0.1
	 * @see    PodsAdmin.php >> admin_menu()
	 * @param  string $menu_label Current menu label
	 * @param  array  $pod        Pod data
	 * @return string
	 */
	public function admin_menu_label_i18n( $menu_label, $pod ) {
		return (string) $this->get_field_translation( $menu_label, 'menu_name', $pod );
	}

	/**
	 * Returns the translated label if available
	 * 
	 * @since  0.1
	 * @see    PodsForm.php >> 'pods_form_ui_label_text' (filter)
	 * @param  string $label   The default label
	 * @param  string $name    The field name
	 * @param  string $help    The help text
	 * @param  array  $options The field options
	 * @return string
	 */
	public function fields_ui_label_text_i18n( $label, $name, $help, $options ) {
		return (string) $this->get_field_translation( $label, 'label', $options );
	}

	/**
	 * Returns the translated description if available
	 * 
	 * @since  0.1
	 * @see    PodsForm.php >> 'pods_form_ui_comment_text' (filter)
	 * @param  string $message The default description
	 * @param  string $name    The field name
	 * @param  array  $options The field options
	 * @return string
	 */
	public function fields_ui_comment_text_i18n( $message, $name, $options ) {
		return (string) $this->get_field_translation( $message, 'description', $options );
	}

	/**
	 * Replaces the default selected text with a translation if available
	 * 
	 * @since  0.1
	 * @see    pick.php >> 'pods_field_pick_data' (filter)
	 * @param  array  $data    The default data of the field
	 * @param  string $name    The field name
	 * @param  string $value   The field value
	 * @param  array  $options The field options
	 * @param  array  $pod     The Pod
	 * @param  int    $id      The field ID
	 * @return array
	 */ 
	public function field_pick_data_i18n( $data, $name, $value, $options, $pod, $id ) {
		if ( isset( $data[''] ) && isset( $options['pick_select_text'] ) ) {
			$locale = $this->locale;
			if (   isset( $options['pick_select_text_' . $locale ] ) 
				&& array_key_exists( $locale, $this->languages ) 
				&& $this->obj_is_language_enabled( $locale, $pod ) 
			) {
				$data[''] = $options['pick_select_text_' . $locale ];
			}
		}
		return $data;
	}

	/**
	 * Get the i18n files for jquery datepicker from the github repository
	 * 
	 * @since  0.1
	 * @link   https://jqueryui.com/datepicker/#localization
	 * @link   https://github.com/jquery/jquery-ui/tree/master/ui/i18n
	 * @param  array  $args            datepicker arguments
	 * @param  string $type            datepicker type
	 * @param  array  $options         field options
	 * @param  array  $attributes      field attibutes
	 * @param  string $name            field name
	 * @param  string $form_field_type field type
	 * @return array
	 */
	public function field_date_args_i18n($args, $type, $options, $attributes, $name, $form_field_type) {
		$locale = $this->get_locale_jquery_ui_i18n();
		if ( ! empty( $locale ) ) {
			// URL to the raw file on github
			$url_base = 'https://rawgit.com/jquery/jquery-ui/master/ui/i18n/';
			// Filename prefix
			$file_prefix = 'datepicker-';
			// Full URL
			$i18n_file = $url_base.$file_prefix.$locale.'.js';
			// Enqueue script
			wp_enqueue_script('jquery-ui-i18n-'.$locale, $i18n_file, array('jquery-ui-datepicker'));
			// Add i18n argument to the datepicker
			$args['regional'] = $locale;
		}
		return $args;
	}

	/**
	 * Get the locale according to the format available in the jquery ui i18n file list
	 * 
	 * @url    https://github.com/jquery/jquery-ui/tree/master/ui/i18n
	 * @return string ex: "fr" or "en-GB"
	 */
	public function get_locale_jquery_ui_i18n() {
		//replace _ by - in "en_GB" for example
		$locale = str_replace( '_', '-', get_locale() );
		switch ($locale) {
			case 'ar-DZ':
			case 'cy-GB':
			case 'en-AU':
			case 'en-GB':
			case 'en-NZ':
			case 'fr-CH':
			case 'nl-BE':
			case 'pt-BR':
			case 'sr-SR':
			case 'zh-CN':
			case 'zh-HK':
			case 'zh-TW':
				//For all this locale do nothing the file already exist
				break;
			default:
				//for other locale keep the first part of the locale (ex: "fr-FR" -> "fr")
				$locale = substr( $locale, 0, strpos( $locale, '-' ) );
				//English is the default locale
				$locale = ( $locale == 'en' ) ? '' : $locale;
				break;
		}
		return $locale;
	}

	/**
	 * Filter hook function to overwrite the labels and description with translations (if available)
	 * 
	 * @since  0.1
	 * @see    PodsInit.php >> setup_content_types()
	 * @param  array   $options  The array of object options
	 * @param  string  $object   The object type name/slug
	 * @return array
	 */
	public function pods_register_object_i18n( $options, $object ) {

		$locale = $this->locale;

		if ( ! array_key_exists( $locale, $this->languages ) || $this->obj_is_language_enabled( $locale, $options ) ) {
			return $options;
		}

		$temp_options = $options;

		// Load the pod
		$pod = pods_api()->load_pod( $object );
		foreach ( $pod[ 'options' ] as $_option => $_value ) {
			$pod[ $_option ] = $_value;
		}

		$cpt_locale_label    = esc_html( pods_v( 'label_'.$locale, $pod, ucwords( str_replace( '_', ' ', pods_v( 'name', $pod ) ) ), null, true ) );
		$cpt_locale_singular = esc_html( pods_v( 'label_singular_'.$locale, $pod, ucwords( str_replace( '_', ' ', pods_v( 'label', $pod, $options['label'], null, true ) ) ), null, true ) );

		$cpt_locale_labels                       = array();
		$cpt_locale_labels['name']               = $cpt_locale_label;
		$cpt_locale_labels['singular_name']      = $cpt_locale_singular;
		$cpt_locale_labels['menu_name']          = pods_v( 'menu_name_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['add_new']            = pods_v( 'label_add_new_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['add_new_item']       = pods_v( 'label_add_new_item_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['new_item']           = pods_v( 'label_new_item_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['edit']               = pods_v( 'label_edit_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['edit_item']          = pods_v( 'label_edit_item_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['view']               = pods_v( 'label_view_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['view_item']          = pods_v( 'label_view_item_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['all_items']          = pods_v( 'label_all_items_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['search_items']       = pods_v( 'label_search_items_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['not_found']          = pods_v( 'label_not_found_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['not_found_in_trash'] = pods_v( 'label_not_found_in_trash_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['parent']             = pods_v( 'label_parent_' . $locale, $pod, '', null, true );
		$cpt_locale_labels['parent_item_colon']  = pods_v( 'label_parent_item_colon_' . $locale, $pod, '', null, true );

		// Label
		if ( isset( $cpt_locale_label ) && ! empty( $cpt_locale_label ) ) {
			$options['label'] = $cpt_locale_label;
		}
		// Label singular
		if ( isset( $cpt_locale_singular ) && ! empty( $cpt_locale_singular ) ) {
			$options['label'] = $cpt_locale_singular;
		}
		// Other options
		if ( isset( $options['labels'] ) && is_array( $options['labels'] ) ) {
			foreach( $options['labels'] as $key => $value ) {
				if ( isset( $cpt_locale_labels[ $key ] ) && ! empty( $cpt_locale_labels[ $key ] ) ) {
					$options['labels'][ $key ] = $cpt_locale_labels[ $key ];
				}
			}
		}

		return $options;
	}

	/**
	 * Save component settings
	 * 
	 * @since 0.1
	 */
	public function admin_save() {

		$this->languages_available = get_available_languages();
		$this->admin_assets();

		/**
		 * format: array( language, version, updated, english_name, native_name, package, iso, strings )
		 */
		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		$this->languages_translated = wp_get_available_translations();

		$new_languages = array();
		if ( isset( $_POST['pods_i18n_enabled_languages'] ) && is_array( $_POST['pods_i18n_enabled_languages'] ) ) {
			foreach ( $_POST['pods_i18n_enabled_languages'] as $locale ) {
				if ( in_array( strip_tags( $locale ), $this->languages_available ) ) {
					$new_languages[ $locale ] = array();
					if ( isset( $this->languages_translated[ $locale ]['language'] ) ) {
						$new_languages[ $locale ]['language'] = $this->languages_translated[ $locale ]['language'];
					}
					if ( isset( $this->languages_translated[ $locale ]['english_name'] ) ) {
						$new_languages[ $locale ]['english_name'] = $this->languages_translated[ $locale ]['english_name'];
					}
					if ( isset( $this->languages_translated[ $locale ]['native_name'] ) ) {
						$new_languages[ $locale ]['native_name'] = $this->languages_translated[ $locale ]['native_name'];
					}
				}
			}
		}
		$this->settings['enabled_languages'] = $new_languages;
		update_option( $this->option_key, $this->settings );
		$this->languages = $new_languages;
	}

	/**
	 * Build admin area
	 *
	 * @since  0.1
	 * @param  $options
	 * @param  $component
	 * @return void
	 */
	public function admin ( $options, $component ) {

		$this->languages_available = get_available_languages();
		$this->admin_assets();

		/**
		 * format: array( language, version, updated, english_name, native_name, package, iso, strings )
		 */
		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		$this->languages_translated = wp_get_available_translations();

		// en_US is always installed
		$data = array( 'en_US' => array(
			'id' => 'en_US',
			'locale' => 'en_US',
			'lang' => 'English',
			'lang_native' => 'English',
			'enabled' => 'Default',
		) );

		foreach ( $this->languages_available as $locale ) {
			$lang_label = $this->create_lang_label( $this->languages_translated[ $locale ] );
			if ( ! empty( $lang_label ) ) {
				$lang_label = $locale . ' ('. $lang_label .')';
			} else {
				$lang_label = $locale;
			}

			if ( array_key_exists( $locale, $this->languages ) ) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}
			$enabled = '<input type="checkbox" name="pods_i18n_enabled_languages['.$locale.']" value="'.$locale.'" '.$checked.'/>';

			$data[ $locale ] = array(
				'id' => $locale,
				'locale' => $locale,
				'lang' => $this->languages_translated[ $locale ]['english_name'],
				'lang_native' => $this->languages_translated[ $locale ]['native_name'],
				'enabled' => $enabled,
			);
		}

		$ui = array(
			'component' => $component,
			//'data' => $data,
			//'total' => count( $data ),
			//'total_found' => count( $data ),
			'items' => __('Languages', 'pods'),
			'item' => __('Language', 'pods'),
			'fields' => array(
				'manage' => array(
					'enabled' => array( 
						'label' => __( 'Active', 'pods' ),
						'type' => 'text', 
					),
					'locale' => array( 'label' => __( 'Locale', 'pods' ) ),
					'lang' => array( 'label' => __( 'Language', 'pods' ) ),
					'lang_native' => array( 'label' => __( 'Native name', 'pods' ) ),
					/*'fields' => array(
						'label' => __( 'Fields', 'pods' ),
						'type' => 'text',
						'options' => array(
							'text_allow_html' => 1,
							'text_allowed_html_tags' => 'br code'
						)
					),*/
				)
			),
			'actions_disabled' => array( 'edit', 'add', 'delete', 'duplicate', 'view', 'export' ),
			'actions_custom' => array(
				//'add' => array( $this, 'admin_add' ),
				//'edit' => array( $this, 'admin_edit' ),
				//'delete' => array( $this, 'admin_delete' )
			),
			'search' => false,
			'searchable' => false,
			'sortable' => false,
			'pagination' => false
		);

		/**
		 * Filter the language data
		 * @since 0.1
		 * @param array
		 */
		$data = apply_filters( 'pods_component_i18n_admin_data', $data );

		/**
		 * Filter the UI fields
		 * @since 0.1
		 * @param array
		 */
		$ui['fields'] = apply_filters( 'pods_component_i18n_admin_ui_fields', $ui['fields'], $data );

		$ui['data'] = $data;
		$ui['total'] = count( $data );
		$ui['total_found'] = count( $data );

		/*if ( !pods_is_admin( array( 'pods_i18n_activate_lanuages' ) ) )
			$ui[ 'actions_disabled' ][] = 'edit';*/

		echo '<div id="pods_admin_i18n" class="pods-submittable-fields">';

		pods_ui( $ui );

		// @todo Do this in pods_ui so we don't rely on javascript
		echo '<div id="pods_i18n_settings_save">';
		echo '<input type="hidden" id="nonce_i18n" name="_nonce_i18n" value="' . wp_create_nonce('pods_i18n_activate_lanuages') . '" />';
		submit_button();
		echo '</div>';

		echo '</div>';
	}

	/**
	 * The i18n option tab.
	 *
	 * @since  0.1
	 * @param  array $tabs
	 * @param  array $pod
	 * @param  array $args
	 * @return array
	 */
	public function pod_tab( $tabs, $pod, $args ) {
		$tabs[ 'pods-i18n' ] = __( 'Translation Options', 'pods' );
		return $tabs;
	}

	/**
	 * The i18n options
	 *
	 * @since  0.1
	 * @param  array $options
	 * @param  array $pod
	 * @return array
	 */
	public function pod_options( $options, $pod ) {

		//if ( $pod['type'] === '' )
		$options[ 'pods-i18n' ] = array(
			'enabled_languages' => array(
				'label' => __( 'Enable/Disable languages for this Pod', 'pods' ),
				'help' => __( 'This overwrites the defaults set in the component admin.', 'pods' ),
				'group' => array(),
			),
		);

		foreach ( $this->languages as $locale => $lang_data ) {
			$options['pods-i18n']['enabled_languages']['group']['enable_i18n_' . $locale] = array(
				'label'      => $locale . ' (' . $this->create_lang_label( $lang_data ) . ')',
				'default'    => 1,
				'type'       => 'boolean',
			);
		}

		return $options;

	}

	/**
	 * Add the i18n metabox.
	 *
	 * @since 0.1
	 */
	public function admin_meta_box() {
		add_meta_box( 'pods_i18n', __( 'Translation options', 'pods' ), array( $this, 'meta_box' ), '_pods_pod', 'side', 'default' );
	}

	/**
	 * The i18n metabox.
	 * 
	 * @todo Store enabled languages serialized instead of separate inputs
	 *
	 * @since  0.1
	 * @param  array $pod
	 * @return array
	 */
	public function meta_box( $pod ) {

		if ( ! empty( $this->languages ) ) {
		?>
			<p><?php _e( 'Enable/Disable languages for this Pod', 'pods' ); ?></p>
			<p><small class="description"><?php _e( 'This overwrites the defaults set in the component admin.', 'pods' ); ?></small>
			<br>
		<?php

			foreach ( $this->languages as $locale => $lang_data ) {

				if ( ! isset( $pod['options']['enable_i18n_' . $locale] ) ) {
					// Enabled by default
					$pod['options']['enable_i18n_' . $locale] = 1;
				}
		?>
				<div class="pods-field-option pods-enable-disable-language">
		<?php 
					echo PodsForm::field( 'enable_i18n_' . $locale, pods_v( 'enable_i18n_' . $locale, $pod ), 'boolean', array( 
						'boolean_yes_label' =>  '<code>' . $locale . '</code> (' . $this->create_lang_label( $lang_data ) . ')',
						'boolean_no_label' =>  '',
					) );
		?>
				</div>
		<?php
			}
		?>
			</p>
			<hr>
			<p><button id="toggle_i18n" class="button-secondary"><?php _e('Toggle translation visibility', 'pods'); ?></button></p>
		<?php
		}
	}

	/**
	 * The i18n field option tab.
	 * 
	 * @todo check / remove
	 *
	 * @since  0.1
	 * @param  array $tabs
	 * @param  array $pod
	 * @return array
	 */
	public function pod_field_tab( $tabs, $pod ) {
		$tabs[ 'i18n' ] = __( 'Translations', 'pods' );
		return $tabs;
	}

	/**
	 * The i18n options
	 * 
	 * @todo check / remove
	 *
	 * @since  0.1
	 * @param  array $options
	 * @param  array $pod
	 * @return array
	 */
	public function pod_field_options( $options, $pod ) {
		return $options;
	}

	/**
	 * Adds translation inputs to fields
	 * 
	 * @since  0.1
	 * @see    PodsForm.php >> 'pods_form_ui_field_' . $type (filter)
	 * @param  string $output The default output of the field
	 * @param  string $name The field name
	 * @param  string $value The field value
	 * @param  array $options The field options
	 * @param  array $pod The Pod
	 * @param  int $id The field ID
	 * @return string
	 */
	public function add_i18n_inputs( $output, $name, $value, $options, $pod, $id ) {
		
		if ( ! empty( $pod ) || empty( $name ) || ! $this->is_translatable_field( $name ) ) {
			return $output;
		}

		$pod = $this->cur_pod;
		if ( empty( $pod ) ) {
			// Setting the $pod var to a non-empty value is mandatory to prevent a loop
			$pod = true;
		}

		$output .= '<br clear="both" />';
		$output .= '<div class="pods-i18n-field">';
		foreach ( $this->languages as $locale => $lang_data ) {

			if ( ! $this->obj_is_language_enabled( $locale, $pod ) ) {
				continue;
			}
			// Our own shiny label with language information
			$lang_code = '<code style="font-size: 1em;">' . $locale . '</code>';
			/*$lang_label = $this->create_lang_label( $lang_data );
			if ( ! empty( $lang_label ) ) {
				$lang_label = $lang_code . ' ('. $lang_label .')';
			} else {*/
				$lang_label = $lang_code;
			//}
			$lang_label = '<small>' . $lang_label . '</small>';

			$style = '';

			// Add language data to name for normal strings and array formatted strings
			if ( strpos( $name, ']' ) !== false ) {
				// Hide the i18n options for fields by default if they are empty
				if ( strpos( $name, 'field_data' ) !== false && empty( pods_v( $name, $pod ) ) ) {
					$style = ' style="display: none;"';
				}
				$field_name = rtrim( $name, ']' );
				$field_name .= '_'.$locale.']';
			} else {
				$field_name = $name.'_'.$locale;
			}

			// Add the translation fields
			$output .= '<div class="pods-i18n-input pods-i18n-input-' . $locale . '" data-locale="' . $locale . '" ' . $style . '>';
			$output .= PodsForm::label( $field_name, $lang_label, __( 'help', 'pods' ) );
			$output .= PodsForm::field( $field_name, pods_v( $field_name, $pod ), 'text', null, $pod );
			$output .= '</div>';
		}
		$output .= '</div>';
		return $output;
	}

	/**
	 * Check if a language is get to enables for an object
	 * 
	 * @since  0.1
	 * @param  string $locale The locale to validate
	 * @param  array  $data   Object data
	 * @return bool
	 */
	public function obj_is_language_enabled( $locale, $data ) {
		$data = (array) $data;
		$options = ( isset( $data['options'] ) ) ? $data['options'] : $data;
		if ( isset( $options['enable_i18n_'.$locale] ) && false == (bool) $options['enable_i18n_'.$locale] ) {
			return false;
		}
		return true;
	}

	/**
	 * Create a label with the english and native name combined
	 * 
	 * @since  0.1 
	 * @param  array $lang_data
	 * @return string
	 */
	public function create_lang_label( $lang_data ) {

		$english_name = '';
		$native_name = '';

		if ( isset( $lang_data['english_name'] ) ) {
			$english_name = $lang_data['english_name'];
		}
		if ( isset( $lang_data['native_name'] ) ) {
			$native_name = $lang_data['native_name'];
		}

		if ( ! empty( $native_name ) && ! empty( $english_name ) ) {
			if ( $native_name == $english_name ) {
				return $english_name;
			} else {
				return $english_name . ' / ' . $native_name;
			}
		} else {
			if ( ! empty( $english_name ) ) {
				return $english_name;
			}
			if ( ! empty( $native_name ) ) {
				return $native_name;
			}
		}
		return '';
	}

}
