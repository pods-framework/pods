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
 * @package    Pods\Components
 * @subpackage i18n
 */

! defined( 'ABSPATH' ) and die();

if ( class_exists( 'Pods_Component_I18n' ) ) {
	return;
}

/**
 * Class Pods_Component_I18n
 */
class Pods_Component_I18n extends PodsComponent {

	public $settings             = array();
	public $locale               = null;
	public $languages            = array();
	public $languages_available  = array();
	public $languages_translated = array();
	public $cur_pod              = null;
	public $option_key           = 'pods_component_i18n_settings';
	public $admin_page           = 'pods-component-translate-pods-admin';
	public $capability           = 'pods_i18n_activate_lanuages';
	public $nonce                = 'pods_i18n_activate_lanuages';

	public $translatable_fields = array(
		'label',
		'description',
		'placeholder',
		'menu_name',
		'name_admin_bar',
		'pick_select_text',
		'file_add_button',
		'file_modal_title',
		'file_modal_add_button',
	);

	/**
	 * {@inheritdoc}
	 */
	public function init() {

		$this->settings = get_option( $this->option_key, array() );

		// Polylang
		if ( function_exists( 'PLL' ) && file_exists( plugin_dir_path( __FILE__ ) . 'I18n-polylang.php' ) ) {
			include_once plugin_dir_path( __FILE__ ) . 'I18n-polylang.php';
		}
		// WPML
		if ( did_action( 'wpml_loaded' ) && file_exists( plugin_dir_path( __FILE__ ) . 'I18n-wpml.php' ) ) {
			include_once plugin_dir_path( __FILE__ ) . 'I18n-wpml.php';
		}

		$active = false;
		// Are there active languages?
		if ( ! empty( $this->settings['enabled_languages'] ) ) {
			$this->languages = $this->settings['enabled_languages'];
			$this->locale    = get_locale();
			$active          = true;
		}

		$is_component_page = false;
		$is_pods_edit_page = false;

		if ( is_admin() && isset( $_GET['page'] ) ) {

			// Is the current page the admin page of this component or a Pods edit page?
			if ( $_GET['page'] === $this->admin_page ) {
				$is_component_page = true;
			} elseif ( $_GET['page'] === 'pods' ) {
				$is_pods_edit_page = true;
			}

			if ( $is_component_page || ( $is_pods_edit_page && $active ) ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
			}
		}

		if ( $is_component_page ) {
			// Do save action here because otherwise the loading of post_types get done first and labels aren't translated
			if ( pods_is_admin( $this->capability ) && isset( $_POST['_nonce_i18n'] ) && wp_verify_nonce( $_POST['_nonce_i18n'], $this->nonce ) ) {
				$this->admin_save();
			}
		}

		if ( $active ) {
			// WP Object filters (post_type and taxonomy)
			add_filter( 'pods_register_post_type', array( $this, 'pods_register_wp_object_i18n' ), 10, 2 );
			add_filter( 'pods_register_taxonomy', array( $this, 'pods_register_wp_object_i18n' ), 10, 2 );

			// ACT's
			add_filter(
				'pods_advanced_content_type_pod_data', array(
					$this,
					'pods_filter_object_strings_i18n',
				), 10, 2
			);

			// Setting pages
			add_filter( 'pods_admin_menu_page_title', array( $this, 'admin_menu_page_title_i18n' ), 10, 2 );
			add_filter( 'pods_admin_menu_label', array( $this, 'admin_menu_label_i18n' ), 10, 2 );

			// Default filters for all fields
			add_filter( 'pods_form_ui_label_text', array( $this, 'fields_ui_label_text_i18n' ), 10, 4 );
			add_filter( 'pods_form_ui_comment_text', array( $this, 'fields_ui_comment_text_i18n' ), 10, 3 );

			foreach ( pods_form()->field_types() as $type => $data ) {
				add_filter(
					'pods_form_ui_field_' . $type . '_options', array(
						$this,
						'form_ui_field_options_i18n',
					), 10, 5
				);
			}

			// Field specific
			// add_filter( 'pods_field_pick_data', array( $this, 'field_pick_data_i18n' ), 10, 6 );
			if ( $is_pods_edit_page ) {

				$pod = null;
				// Get the pod if available
				if ( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
					$pod = pods_api()->load_pod( array( 'id' => $_GET['id'] ) );
					// Append options to root array for pods_v function to work
					foreach ( $pod['options'] as $_option => $_value ) {
						$pod[ $_option ] = $_value;
					}
				}
				$this->cur_pod = $pod;

				// Add option tab for post types
				// add_filter( 'pods_admin_setup_edit_tabs_post_type', array( $this, 'pod_tab' ), 11, 3 );
				// Add the same tab for taxonomies
				// add_filter( 'pods_admin_setup_edit_tabs_taxonomy', array( $this, 'pod_tab' ), 11, 3 );
				// Add options to the new tab
				// add_filter( 'pods_admin_setup_edit_options', array( $this, 'pod_options' ), 12, 2 );
				// Add options metabox to the pod edit screens
				add_action( 'pods_add_meta_boxes', array( $this, 'admin_meta_box' ) );

				// Add the i18n input fields based on existing fields
				add_filter( 'pods_form_ui_field_text', array( $this, 'add_i18n_inputs' ), 10, 6 );
			}//end if
		}//end if
	}

	/**
	 * Load assets for this component
	 *
	 * @since 0.1.0
	 */
	public function admin_assets() {

		wp_enqueue_script(
			'pods-admin-i18n', PODS_URL . 'components/I18n/pods-admin-i18n.js', array(
				'jquery',
				'pods-i18n',
			), '1.0', true
		);
		$localize_script = array();
		if ( ! empty( $this->languages ) ) {
			foreach ( $this->languages as $lang => $lang_data ) {
				$lang_label = $this->create_lang_label( $lang_data );
				if ( ! empty( $lang_label ) ) {
					$lang_label = $lang . ' (' . $lang_label . ')';
				} else {
					$lang_label = $lang;
				}
				$localize_script[ $lang ] = $lang_label;
			}
		}
		wp_localize_script( 'pods-admin-i18n', 'pods_admin_i18n_strings', $localize_script );

		// Add strings to the i18n js object
		add_filter( 'pods_localized_strings', array( $this, 'localize_assets' ) );
	}

	/**
	 * Localize the assets
	 *
	 * @param  array $str Existing strings
	 *
	 * @return array
	 */
	public function localize_assets( $str ) {

		$str['Add translation']     = __( 'Add translation', 'pods' );
		$str['Toggle translations'] = __( 'Toggle translations', 'pods' );
		$str['Show translations']   = __( 'Show translations', 'pods' );
		$str['Hide translations']   = __( 'Hide translations', 'pods' );
		$str['Select']              = __( 'Select', 'pods' );

		return $str;
	}

	/**
	 * Check is a field name is set for translation
	 *
	 * @since 0.1.0
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function is_translatable_field( $name ) {

		$translatable_fields = $this->get_translatable_fields();

		// All fields that start with "label"
		if ( strpos( $name, 'label' ) === 0 ) {
			return true;
		}
		// All translatable fields
		if ( in_array( $name, $translatable_fields, true ) ) {
			return true;
		}
		// Custom fields data, the name must begin with field_data[
		if ( strpos( $name, 'field_data[' ) === 0 ) {
			$name = str_replace( 'field_data[', '', $name );
			$name = rtrim( $name, ']' );
			$name = explode( '][', $name );
			$name = end( $name );
			// All translatable fields from field_data[ (int) ][ $name ]
			if ( in_array( $name, $translatable_fields, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a translated option for a field key (if available)
	 *
	 * @since 0.1.0
	 *
	 * @param  string $current Current value
	 * @param  string $key     The key / opion name to search for
	 * @param  array  $data    Pod data (can also be an options array of a pod or field)
	 *
	 * @return string
	 */
	public function get_value_translation( $current, $key, $data ) {

		$locale = $this->locale;
		// Validate locale and pod
		if ( is_array( $data ) && array_key_exists( $locale, $this->languages ) && $this->obj_is_language_enabled( $locale, $data ) ) {
			// Add option keys to $data array
			if ( ! empty( $data['options'] ) ) {
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
	 * @since 0.1.0
	 * @see    PodsAdmin.php >> admin_menu()
	 * @see    PodsAdmin.php >> admin_content_settings()
	 *
	 * @param  string $page_title Current page title
	 * @param  array  $pod        Pod data
	 *
	 * @return string
	 */
	public function admin_menu_page_title_i18n( $page_title, $pod ) {

		return (string) $this->get_value_translation( $page_title, 'label', $pod );
	}

	/**
	 * Menu title for setting pages
	 *
	 * @since 0.1.0
	 * @see    PodsAdmin.php >> admin_menu()
	 *
	 * @param  string $menu_label Current menu label
	 * @param  array  $pod        Pod data
	 *
	 * @return string
	 */
	public function admin_menu_label_i18n( $menu_label, $pod ) {

		return (string) $this->get_value_translation( $menu_label, 'menu_name', $pod );
	}

	/**
	 * Returns the translated label if available
	 *
	 * @since 0.1.0
	 * @see    PodsForm.php >> 'pods_form_ui_label_text' (filter)
	 *
	 * @param  string $label   The default label
	 * @param  string $name    The field name
	 * @param  string $help    The help text
	 * @param  array  $options The field options
	 *
	 * @return string
	 */
	public function fields_ui_label_text_i18n( $label, $name, $help, $options ) {

		return (string) $this->get_value_translation( $label, 'label', $options );
	}

	/**
	 * Returns the translated description if available
	 *
	 * @since 0.1.0
	 * @see    PodsForm.php >> 'pods_form_ui_comment_text' (filter)
	 *
	 * @param  string $message The default description
	 * @param  string $name    The field name
	 * @param  array  $options The field options
	 *
	 * @return string
	 */
	public function fields_ui_comment_text_i18n( $message, $name, $options ) {

		return (string) $this->get_value_translation( $message, 'description', $options );
	}

	/**
	 * Replaces the default selected text with a translation if available
	 *
	 * @since 0.1.0
	 * @see    pick.php >> 'pods_field_pick_data' (filter)
	 *
	 * @param  array  $data    The default data of the field
	 * @param  string $name    The field name
	 * @param  string $value   The field value
	 * @param  array  $options The field options
	 * @param  array  $pod     The Pod
	 * @param  int    $id      The field ID
	 *
	 * @return array
	 */
	public function field_pick_data_i18n( $data, $name, $value, $options, $pod, $id ) {

		if ( isset( $data[''] ) && isset( $options['pick_select_text'] ) ) {
			$locale = $this->locale;
			if ( isset( $options[ 'pick_select_text_' . $locale ] ) && array_key_exists( $locale, $this->languages ) && $this->obj_is_language_enabled( $locale, $pod ) ) {
				$data[''] = $options[ 'pick_select_text_' . $locale ];
			}
		}

		return $data;
	}

	/**
	 * Replaces the default values with a translation if available
	 *
	 * @since 0.1.0
	 * @see    PodsForm.php >> 'pods_form_ui_field_' . $type . '_options' (filter)
	 *
	 * @param  array  $options The field options
	 * @param  string $name    The field name
	 * @param  string $value   The field value
	 * @param  array  $pod     The Pod
	 * @param  int    $id      The field ID
	 *
	 * @return array
	 */
	public function form_ui_field_options_i18n( $options, $name, $value, $pod, $id ) {

		foreach ( $this->get_translatable_fields() as $field ) {
			$locale = $this->locale;
			if ( isset( $options[ $field . '_' . $locale ] ) && array_key_exists( $locale, $this->languages ) && $this->obj_is_language_enabled( $locale, $pod ) ) {
				$options[ $field ] = $options[ $field . '_' . $locale ];
			}
		}

		return $options;
	}

	/**
	 * Filter hook function to overwrite the labels and description with translations (if available)
	 *
	 * @since 0.1.0
	 * @see    PodsInit.php >> setup_content_types()
	 *
	 * @param  array  $options The array of object options
	 * @param  string $object  The object type name/slug
	 *
	 * @return array
	 */
	public function pods_register_wp_object_i18n( $options, $object ) {

		$locale = $this->locale;

		$pod = pods_api()->load_pod( $object );

		if ( ! $this->obj_is_language_enabled( $locale, $pod ) ) {
			return $options;
		}

		// Load the pod
		foreach ( $pod['options'] as $_option => $_value ) {
			$pod[ $_option ] = $_value;
		}

		// Default
		$locale_labels                          = array();
		$locale_labels['name']                  = esc_html( pods_v( 'label_' . $locale, $pod, '', true ) );
		$locale_labels['singular_name']         = esc_html( pods_v( 'label_singular_' . $locale, $pod, '', true ) );
		$locale_labels['menu_name']             = pods_v( 'menu_name_' . $locale, $pod, '', true );
		$locale_labels['add_new_item']          = pods_v( 'label_add_new_item_' . $locale, $pod, '', true );
		$locale_labels['edit_item']             = pods_v( 'label_edit_item_' . $locale, $pod, '', true );
		$locale_labels['view_item']             = pods_v( 'label_view_item_' . $locale, $pod, '', true );
		$locale_labels['all_items']             = pods_v( 'label_all_items_' . $locale, $pod, '', true );
		$locale_labels['search_items']          = pods_v( 'label_search_items_' . $locale, $pod, '', true );
		$locale_labels['parent_item_colon']     = pods_v( 'label_parent_item_colon_' . $locale, $pod, '', true );
		$locale_labels['not_found']             = pods_v( 'label_not_found_' . $locale, $pod, '', true );
		$locale_labels['items_list_navigation'] = pods_v( 'label_items_list_navigation_' . $locale, $pod, '', true );
		$locale_labels['items_list']            = pods_v( 'label_items_list_' . $locale, $pod, '', true );

		// Post Types
		$locale_labels['name_admin_bar']        = pods_v( 'name_admin_bar_' . $locale, $pod, '', true );
		$locale_labels['add_new']               = pods_v( 'label_add_new_' . $locale, $pod, '', true );
		$locale_labels['new_item']              = pods_v( 'label_new_item_' . $locale, $pod, '', true );
		$locale_labels['edit']                  = pods_v( 'label_edit_' . $locale, $pod, '', true );
		$locale_labels['view']                  = pods_v( 'label_view_' . $locale, $pod, '', true );
		$locale_labels['view_items']            = pods_v( 'label_view_items_' . $locale, $pod, '', true );
		$locale_labels['parent']                = pods_v( 'label_parent_' . $locale, $pod, '', true );
		$locale_labels['not_found_in_trash']    = pods_v( 'label_not_found_in_trash_' . $locale, $pod, '', true );
		$locale_labels['archives']              = pods_v( 'label_archives_' . $locale, $pod, '', true );
		$locale_labels['attributes']            = pods_v( 'label_attributes_' . $locale, $pod, '', true );
		$locale_labels['insert_into_item']      = pods_v( 'label_insert_into_item_' . $locale, $pod, '', true );
		$locale_labels['uploaded_to_this_item'] = pods_v( 'label_uploaded_to_this_item_' . $locale, $pod, '', true );
		$locale_labels['featured_image']        = pods_v( 'label_featured_image_' . $locale, $pod, '', true );
		$locale_labels['set_featured_image']    = pods_v( 'label_set_featured_image_' . $locale, $pod, '', true );
		$locale_labels['remove_featured_image'] = pods_v( 'label_remove_featured_image_' . $locale, $pod, '', true );
		$locale_labels['use_featured_image']    = pods_v( 'label_use_featured_image_' . $locale, $pod, '', true );
		$locale_labels['filter_items_list']     = pods_v( 'label_filter_items_list_' . $locale, $pod, '', true );

		// Taxonomies
		$locale_labels['update_item']                = pods_v( 'label_update_item_' . $locale, $pod, '', true );
		$locale_labels['popular_items']              = pods_v( 'label_popular_items_' . $locale, $pod, '', true );
		$locale_labels['parent_item']                = pods_v( 'label_parent_item_' . $locale, $pod, '', true );
		$locale_labels['new_item_name']              = pods_v( 'label_new_item_name_' . $locale, $pod, '', true );
		$locale_labels['separate_items_with_commas'] = pods_v( 'label_separate_items_with_commas_' . $locale, $pod, '', true );
		$locale_labels['add_or_remove_items']        = pods_v( 'label_add_or_remove_items_' . $locale, $pod, '', true );
		$locale_labels['choose_from_most_used']      = pods_v( 'label_choose_from_the_most_used_' . $locale, $pod, '', true );
		$locale_labels['no_terms']                   = pods_v( 'label_no_terms_' . $locale, $pod, '', true );

		// Assign to label array
		if ( isset( $options['labels'] ) && is_array( $options['labels'] ) ) {
			foreach ( $options['labels'] as $key => $value ) {
				// @todo Currently I only overwrite, maybe also append even if the default locale isn't set?
				if ( isset( $locale_labels[ $key ] ) && ! empty( $locale_labels[ $key ] ) ) {
					$options['labels'][ $key ] = $locale_labels[ $key ];
				}
			}
		}

		return $options;
	}

	/**
	 * Filter hook function to overwrite the labels and description with translations (if available)
	 *
	 * @since 0.1.0
	 * @see    PodsInit.php >> admin_menu()
	 *
	 * @param  array  $options The array of object options
	 * @param  string $object  The object type name/slug
	 *
	 * @return array
	 */
	public function pods_filter_object_strings_i18n( $options, $object ) {

		/**
		 * @todo allow labels to be set even if the default language isn't
		 *
		 * - Find all keys that end with the current locale
		 * - Assign them to the keys without that locale
		 */

		foreach ( $options as $key => $option ) {
			if ( is_string( $option ) && $this->is_translatable_field( $key ) ) {
				$options[ $key ] = $this->get_value_translation( $option, $key, $options );
			}
		}

		if ( ! empty( $options['options'] ) ) {
			foreach ( $options['options'] as $key => $option ) {
				if ( is_string( $option ) && $this->is_translatable_field( $key ) ) {
					$options['options'][ $key ] = $this->get_value_translation( $option, $key, $options['options'] );
				}
			}
		}

		return $options;
	}

	/**
	 * Save component settings
	 *
	 * @since 0.1.0
	 */
	public function admin_save() {

		$this->languages_available = get_available_languages();

		/**
		 * format: array( language, version, updated, english_name, native_name, package, iso, strings )
		 */
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$this->languages_translated = wp_get_available_translations();

		$new_languages = array();

		if ( isset( $_POST['pods_i18n_enabled_languages'] ) && is_array( $_POST['pods_i18n_enabled_languages'] ) ) {
			foreach ( $_POST['pods_i18n_enabled_languages'] as $locale ) {
				$locale = sanitize_text_field( $locale );

				if ( in_array( $locale, $this->languages_available, true ) ) {
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
		}//end if

		$this->languages                     = $new_languages;
		$this->settings['enabled_languages'] = $new_languages;

		update_option( $this->option_key, $this->settings );

	}

	/**
	 * Build admin area
	 *
	 * @since 0.1.0
	 *
	 * @param  $options
	 * @param  $component
	 *
	 * @return void
	 */
	public function admin( $options, $component ) {

		$this->languages_available = get_available_languages();

		/**
		 * format: array( language, version, updated, english_name, native_name, package, iso, strings )
		 */
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';

		$this->languages_translated = wp_get_available_translations();

		// en_US is always installed (default locale of WP)
		$data = array(
			'en_US' => array(
				'id'          => 'en_US',
				'locale'      => 'en_US',
				'lang'        => 'English',
				'lang_native' => 'English',
				'enabled'     => 'Default',
			),
		);

		foreach ( $this->languages_available as $locale ) {
			$checked = checked( isset( $this->languages[ $locale ] ), true, false );

			$enabled = sprintf( '<input type="checkbox" name="pods_i18n_enabled_languages[%s]" value="%s"%s />', esc_attr( $locale ), esc_attr( $locale ), $checked );

			$data[ $locale ] = array(
				'id'          => $locale,
				'locale'      => $locale,
				'lang'        => $this->languages_translated[ $locale ]['english_name'],
				'lang_native' => $this->languages_translated[ $locale ]['native_name'],
				'enabled'     => $enabled,
			);
		}

		$ui = array(
			'component'        => $component,
			// 'data' => $data,
			// 'total' => count( $data ),
			// 'total_found' => count( $data ),
			'items'            => __( 'Languages', 'pods' ),
			'item'             => __( 'Language', 'pods' ),
			'fields'           => array(
				'manage' => array(
					'enabled'     => array(
						'label' => __( 'Active', 'pods' ),
						'type'  => 'raw',
					),
					'locale'      => array(
						'label'   => __( 'Locale', 'pods' ),
						'classes' => array( 'column-secondary' ),
					),
					'lang'        => array( 'label' => __( 'Language', 'pods' ) ),
					'lang_native' => array( 'label' => __( 'Native name', 'pods' ) ),
					/*
					'fields' => array(
						'label' => __( 'Fields', 'pods' ),
						'type' => 'text',
						'options' => array(
							'text_allow_html' => 1,
							'text_allowed_html_tags' => 'br code'
						)
					),*/
				),
			),
			'actions_disabled' => array( 'edit', 'add', 'delete', 'duplicate', 'view', 'export' ),
			'actions_custom'   => array(
				// 'add' => array( $this, 'admin_add' ),
				// 'edit' => array( $this, 'admin_edit' ),
				// 'delete' => array( $this, 'admin_delete' )
			),
			'search'           => false,
			'searchable'       => false,
			'sortable'         => false,
			'pagination'       => false,
		);

		/**
		 * Filter the language data
		 *
		 * @since 0.1.0
		 *
		 * @param array
		 */
		$data = apply_filters( 'pods_component_i18n_admin_data', $data );

		/**
		 * Filter the UI fields
		 *
		 * @since 0.1.0
		 *
		 * @param array
		 */
		$ui['fields'] = apply_filters( 'pods_component_i18n_admin_ui_fields', $ui['fields'], $data );

		$ui['data']        = $data;
		$ui['total']       = count( $data );
		$ui['total_found'] = count( $data );

		/*
		if ( !pods_is_admin( array( 'pods_i18n_activate_lanuages' ) ) )
			$ui[ 'actions_disabled' ][] = 'edit';*/

		echo '<div id="pods_admin_i18n" class="pods-submittable-fields">';

		// Do save action here because otherwise the loading of post_types get done first and labels aren't translated
		if ( pods_is_admin( $this->capability ) && isset( $_POST['_nonce_i18n'] ) && wp_verify_nonce( $_POST['_nonce_i18n'], $this->nonce ) ) {
			pods_message( __( 'Updated active languages.', 'pods' ) );
		}

		pods_ui( $ui );

		// @todo Do this in pods_ui so we don't rely on javascript
		echo '<div id="pods_i18n_settings_save">';
		wp_nonce_field( $this->nonce, '_nonce_i18n', false );
		submit_button();
		echo '</div>';

		echo '</div>';
	}

	/**
	 * The i18n option tab.
	 *
	 * @todo   Remove if not used in final version
	 *
	 * @since 0.1.0
	 *
	 * @param  array $tabs
	 * @param  array $pod
	 * @param  array $args
	 *
	 * @return array
	 */
	public function pod_tab( $tabs, $pod, $args ) {

		$tabs['pods-i18n'] = __( 'Translation Options', 'pods' );

		return $tabs;
	}

	/**
	 * The i18n options
	 *
	 * @todo   Remove if not used in final version
	 *
	 * @since 0.1.0
	 *
	 * @param  array $options
	 * @param  array $pod
	 *
	 * @return array
	 */
	public function pod_options( $options, $pod ) {

		// if ( $pod['type'] === '' )
		/*
		$options[ 'pods-i18n' ] = array(
			'enabled_languages' => array(
				'label' => __( 'Enable/Disable languages for this Pod', 'pods' ),
				'help' => __( 'This overwrites the defaults set in the component admin.', 'pods' ),
				'group' => array(),
			),
		);

		foreach ( $this->languages as $locale => $lang_data ) {
			$options['pods-i18n']['enabled_languages']['group']['enable_i18n'][ $locale ] = array(
				'label'      => $locale . ' (' . $this->create_lang_label( $lang_data ) . ')',
				'default'    => 1,
				'type'       => 'boolean',
			);
		}*/

		return $options;

	}

	/**
	 * Add the i18n metabox.
	 *
	 * @since 0.1.0
	 */
	public function admin_meta_box() {

		add_meta_box(
			'pods_i18n',
			// ID
			__( 'Translation options', 'pods' ),
			// Label
			array( $this, 'meta_box' ),
			// Callback
			'_pods_pod',
			// Screen
			'side',
			// Context (side)
			'default'
			// Priority
		);
	}

	/**
	 * The i18n metabox.
	 *
	 * @todo   Store enabled languages serialized instead of separate inputs
	 *
	 * @since 0.1.0
	 */
	public function meta_box() {

		$pod = $this->cur_pod;

		if ( ! empty( $this->languages ) ) {
			?>
			<p><?php _e( 'Enable/Disable languages for this Pod', 'pods' ); ?></p>
			<p>
				<small class="description"><?php _e( 'This overwrites the defaults set in the component admin.', 'pods' ); ?></small>
			</p>
			<div class="pods-field-enable-disable-language">
				<?php
				foreach ( $this->languages as $locale => $lang_data ) {

					if ( ! isset( $pod['options']['enable_i18n'][ $locale ] ) ) {
						// Enabled by default
						$pod['options']['enable_i18n'][ $locale ] = 1;
					}
					?>
					<div class="pods-field-option pods-enable-disable-language" data-locale="<?php echo esc_attr( $locale ); ?>">
						<?php
						echo PodsForm::field(
							'enable_i18n[' . $locale . ']', $pod['options']['enable_i18n'][ $locale ], 'boolean', array(
								'boolean_yes_label' => '<code>' . $locale . '</code> ' . $this->create_lang_label( $lang_data ),
								'boolean_no_label'  => '',
							)
						);
						?>
					</div>
					<?php
				}
				?>
			</div>
			<hr>
			<p>
				<button id="toggle_i18n" class="button-secondary"><?php _e( 'Toggle translation visibility', 'pods' ); ?></button>
			</p>
			<?php
		}//end if
	}

	/**
	 * Adds translation inputs to fields
	 *
	 * @since 0.1.0
	 * @see    PodsForm.php >> 'pods_form_ui_field_' . $type (filter)
	 *
	 * @param  string $output  The default output of the field
	 * @param  string $name    The field name
	 * @param  string $value   The field value
	 * @param  array  $options The field options
	 * @param  array  $pod     The Pod
	 * @param  int    $id      The field ID
	 *
	 * @return string
	 */
	public function add_i18n_inputs( $output, $name, $value, $options, $pod, $id ) {

		if ( ! empty( $pod ) || empty( $name ) || ! $this->is_translatable_field( $name ) ) {
			return $output;
		}

		$pod = $this->cur_pod;
		// print_r( $pod );
		if ( empty( $pod ) ) {
			// Setting the $pod var to a non-empty value is mandatory to prevent a loop
			$pod = true;
		}

		$output .= '<br clear="both" />';
		$output .= '<div class="pods-i18n-field">';
		foreach ( $this->languages as $locale => $lang_data ) {

			if ( ! $this->obj_is_language_enabled( $locale, (array) $pod ) ) {
				continue;
			}
			// Our own shiny label with language information
			$lang_code = '<code style="font-size: 1em;">' . $locale . '</code>';
			/*
			$lang_label = $this->create_lang_label( $lang_data );
			if ( ! empty( $lang_label ) ) {
				$lang_label = $lang_code . ' ('. $lang_label .')';
			} else {*/
			$lang_label = $lang_code;
			// }
			$lang_label = '<small>' . $lang_label . '</small>';

			$style = '';

			// Add language data to name for normal strings and array formatted strings
			if ( strpos( $name, ']' ) !== false ) {
				// Hide the i18n options for fields by default if they are empty
				$field_value = pods_v( $name, $pod );

				if ( strpos( $name, 'field_data' ) !== false && empty( $field_value ) ) {
					$style = ' style="display: none;"';
				}

				$field_name  = rtrim( $name, ']' );
				$field_name .= '_' . $locale . ']';
			} else {
				$field_name = $name . '_' . $locale;
			}

			// Add the translation fields
			$output .= '<div class="pods-i18n-input pods-i18n-input-' . $locale . '" data-locale="' . $locale . '" ' . $style . '>';
			$output .= PodsForm::label( $field_name, $lang_label );
			$output .= PodsForm::field( $field_name, pods_v( $field_name, $pod ), 'text', null, $pod );
			$output .= '</div>';
		}//end foreach
		$output .= '</div>';

		return $output;
	}

	/**
	 * Check if a language is get to enabled for an object
	 *
	 * @since 0.1.0
	 *
	 * @param  string $locale The locale to validate
	 * @param  array  $data   Object data
	 *
	 * @return bool
	 */
	public function obj_is_language_enabled( $locale, $data ) {

		// If the locale isn't enabled in the global scope from the component it's always disabled
		if ( ! array_key_exists( $locale, $this->languages ) ) {
			return false;
		}
		$data    = (array) $data;
		$options = ( isset( $data['options'] ) ) ? $data['options'] : $data;
		// If it doesn't exist in the object data then use the default (enabled)
		if ( isset( $options['enable_i18n'][ $locale ] ) && false === (bool) $options['enable_i18n'][ $locale ] ) {
			return false;
		}

		return true;
	}

	/**
	 * Create a label with the english and native name combined
	 *
	 * @since 0.1.0
	 *
	 * @param  array $lang_data
	 *
	 * @return string
	 */
	public function create_lang_label( $lang_data ) {

		$english_name = '';
		$native_name  = '';

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

	/**
	 * @return mixed|void
	 */
	public function get_translatable_fields() {

		return apply_filters( 'pods_translatable_fields', $this->translatable_fields );
	}

}
