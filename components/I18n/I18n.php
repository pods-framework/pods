<?php
/**
 * Name: Translate Pods Admin
 *
 * Menu Name: Translate Pods
 *
 * Description: Allow UI of Pods and fields to be translated.
 *
 * Version: 1.1
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

	/**
	 * All fields that are translatable.
	 * @var array
	 */
	protected $translatable_fields = [
		'label' => [],
		'description' => [],
		'placeholder' => [],
		'menu_name' => [],
		'name_admin_bar' => [],
		'repeatable_add_new_label' => [
			'depends-on' => [ 'repeatable' => true ],
		],
		'boolean_yes_label' => [
			'depends-on' => [ 'type' => 'boolean' ],
		],
		'boolean_no_label' => [
			'depends-on' => [ 'type' => 'boolean' ],
		],
		'color_select_label' => [
			'depends-on' => [ 'type' => 'color' ],
		],
		'color_clear_label' => [
			'depends-on' => [ 'type' => 'color' ],
		],
		'file_add_button' => [
			'depends-on' => [ 'type' => 'file' ],
		],
		'file_modal_title' => [
			'depends-on' => [ 'type' => 'file' ],
		],
		'file_modal_add_button' => [
			'depends-on' => [ 'type' => 'file' ],
		],
		'pick_select_text' => [
			'depends-on' => [ 'type' => 'pick' ],
		],
		'pick_add_new_label' => [
			'depends-on' => [ 'type' => 'pick' ],
		],
	];

	/**
	 * {@inheritdoc}
	 */
	public function init() {

		$this->settings = get_option( $this->option_key, array() );

		$active = false;
		// Are there active languages?
		if ( ! empty( $this->settings['enabled_languages'] ) ) {
			$this->languages = $this->settings['enabled_languages'];

			if ( function_exists( 'get_user_locale' ) ) {
				// WP 4.7+
				$this->locale = get_user_locale();
			} else {
				$this->locale = get_locale();
			}

			$active = true;
		}

		$is_component_page = false;
		$is_pods_edit_page = false;

		if ( is_admin() && isset( $_GET['page'] ) ) {

			$page = $_GET['page'];

			// Is the current page the admin page of this component or a Pods edit page?
			if ( $this->admin_page === $page ) {
				$is_component_page = true;
			} elseif ( 'pods' === $page ) {
				$is_pods_edit_page = true;
			}
		}

		if ( $is_component_page ) {
			// Do save action here because otherwise the loading of post_types get done first and labels aren't translated
			if ( pods_is_admin( $this->capability ) && isset( $_POST['_nonce_i18n'] ) && wp_verify_nonce( $_POST['_nonce_i18n'], $this->nonce ) ) {
				$this->admin_save();
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		}

		if ( $active ) {

			/**
			 * PODS ADMIN UI.
			 */

			// Pod.
			add_filter( 'pods_admin_setup_edit_tabs', array( $this, 'translation_tab' ), 99, 2 );
			add_filter( 'pods_admin_setup_edit_options', array( $this, 'translation_options' ), 99, 2 );
			// Pod Groups.
			add_filter( 'pods_admin_setup_edit_group_tabs', array( $this, 'translation_tab' ), 99, 2 );
			add_filter( 'pods_admin_setup_edit_group_options', array( $this, 'translation_options' ), 99, 2 );
			// Pod Fields.
			add_filter( 'pods_admin_setup_edit_field_tabs', array( $this, 'translation_tab' ), 99, 2 );
			add_filter( 'pods_admin_setup_edit_field_options', array( $this, 'translation_options' ), 99, 2 );

			/**
			 * REGISTERING OBJ LABELS.
			 */

			// WP Object filters (post_type and taxonomy).
			add_filter( 'pods_register_post_type', array( $this, 'translate_register_wp_object' ), 10, 2 );
			add_filter( 'pods_register_taxonomy', array( $this, 'translate_register_wp_object' ), 10, 2 );

			// ACT's
			add_filter(
				'pods_advanced_content_type_pod_data',
				array( $this, 'translate_object_options' ),
				10,
				2
			);

			/**
			 * LABEL REPLACEMENT.
			 */

			// Menu labels.
			add_filter( 'pods_admin_menu_page_title', array( $this, 'translate_admin_menu_page_title' ), 10, 2 );
			add_filter( 'pods_admin_menu_label', array( $this, 'translate_admin_menu_label' ), 10, 2 );

			// Do not overwrite Whatsit object fields if we're editing Pods.
			if ( ! $is_pods_edit_page && ! pods_doing_json() && ! wp_doing_ajax() ) {

				// Pod Objects.
				add_filter( 'pods_whatsit_get_label', array( $this, 'translate_label' ), 10, 2 );
				add_filter( 'pods_whatsit_get_description', array( $this, 'translate_description' ), 10, 2 );
				add_filter( 'pods_whatsit_get_arg', array( $this, 'translate_arg' ), 10, 3 );
				add_filter( 'pods_whatsit_get_args', array( $this, 'translate_args' ), 10, 2 );

				// Non DFV field UI.
				foreach ( pods_form()->field_types() as $type => $data ) {
					add_filter(
						'pods_form_ui_field_' . $type . '_options',
						array( $this, 'translate_field_options' ),
						10,
						5
					);
				}
			}

		}//end if
	}

	/**
	 * Load assets for this component
	 *
	 * @since 0.1.0
	 */
	public function admin_assets() {

		wp_enqueue_script(
			'pods-admin-i18n',
			PODS_URL . 'components/I18n/pods-admin-i18n.js',
			array(
				'jquery',
				'pods-i18n',
			),
			'1.0',
			true
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
	 * Check is a field name is set for translation.
	 *
	 * @since 0.1.0
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function is_translatable_field( $name ) {

		// All fields that start with "label".
		if ( strpos( $name, 'label' ) === 0 && false === strpos( $name, $this->locale ) ) {
			return true;
		}

		// All translatable fields.
		if ( in_array( $name, $this->get_translatable_fields( 'names' ), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get a translated option for a field key if available.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $current Current value
	 * @param  string $key     The key / opion name to search for
	 * @param  array|Pods\Whatsit  $data    Pod data (can also be an options array of a pod or field)
	 *
	 * @return mixed
	 */
	public function get_value_translation( $current, $key, $data ) {

		$locale = $this->locale;
		if ( ! array_key_exists( $locale, $this->languages ) ) {
			return $current;
		}

		if ( $this->obj_is_language_enabled( $locale, $data ) ) {
			$translation = pods_v( $key . '_' . $locale, $data, null );
			if ( $translation ) {
				return $translation;
			}
		}

		return $current;
	}

	/**
	 * Page title for setting pages.
	 *
	 * @since 1.0.0
	 * @see   PodsAdmin.php >> admin_menu()
	 * @see   PodsAdmin.php >> admin_content_settings()
	 *
	 * @param  string $page_title Current page title
	 * @param  array  $pod        Pod data
	 *
	 * @return string
	 */
	public function translate_admin_menu_page_title( $page_title, $pod ) {

		return (string) $this->get_value_translation( $page_title, 'label', $pod );
	}

	/**
	 * Menu title for setting pages.
	 *
	 * @since 1.0.0
	 * @see   PodsAdmin.php >> admin_menu()
	 *
	 * @param  string $menu_label Current menu label
	 * @param  array  $pod        Pod data
	 *
	 * @return string
	 */
	public function translate_admin_menu_label( $menu_label, $pod ) {

		return (string) $this->get_value_translation( $menu_label, 'menu_name', $pod );
	}

	/**
	 * Returns the translated label if available.
	 *
	 * @since 1.0.0
	 * @see   \Pods\Whatsit >> 'pods_whatsit_get_label' (filter)
	 *
	 * @param  string       $label  The default label.
	 * @param  Pods\Whatsit $object The Pod Object.
	 *
	 * @return string
	 */
	public function translate_label( $label, $object ) {

		return (string) $this->get_value_translation( $label, 'label', $object );
	}

	/**
	 * Returns the translated description if available.
	 *
	 * @since 1.0.0
	 * @see   \Pods\Whatsit >> 'pods_whatsit_get_description' (filter)
	 *
	 * @param  string       $description  The default description.
	 * @param  Pods\Whatsit $object The Pod Object.
	 *
	 * @return string
	 */
	public function translate_description( $description, $object ) {

		return (string) $this->get_value_translation( $description, 'description', $object );
	}

	/**
	 * Returns the translated argument if available.
	 *
	 * @since 2.8.4
	 * @see   \Pods\Whatsit >> 'pods_whatsit_get_arg' (filter)
	 *
	 * @param  mixed        $arg    The object argument.
	 * @param  string       $name   The argument name.
	 * @param  array|Pods\Whatsit $object The Pod Object.
	 *
	 * @return string
	 */
	public function translate_arg( $arg, $name, $object ) {

		if ( $this->is_translatable_field( $name ) ) {
			return $this->get_value_translation( $arg, $name, $object );
		}

		return $arg;
	}

	/**
	 * Returns the translated arguments if available.
	 *
	 * @since 2.8.4
	 * @see   \Pods\Whatsit >> 'pods_whatsit_get_args' (filter)
	 *
	 * @param  array        $args   The object arguments.
	 * @param  Pods\Whatsit $object The Pod Object.
	 *
	 * @return array
	 */
	public function translate_args( $args, $object ) {

		foreach ( $args as $name => $value ) {
			if ( $this->is_translatable_field( $name ) ) {
				$args[ $name ] = $this->translate_arg( $value, $name, $object );
			}
		}

		return $args;
	}

	/**
	 * Replaces the default selected text with a translation if available.
	 *
	 * @since 1.0.0
	 * @see   pick.php >> 'pods_field_pick_data' (filter)
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
	public function translate_field_pick_data( $data, $name, $value, $options, $pod, $id ) {

		if ( isset( $data[''] ) && isset( $options['pick_select_text'] ) ) {
			$locale = $this->locale;
			if ( isset( $options[ 'pick_select_text_' . $locale ] ) && array_key_exists( $locale, $this->languages ) && $this->obj_is_language_enabled( $locale, $pod ) ) {
				$data[''] = $options[ 'pick_select_text_' . $locale ];
			}
		}

		return $data;
	}

	/**
	 * Replaces the default values with a translation if available.
	 *
	 * @since 1.0.0
	 * @see   PodsForm.php >> 'pods_form_ui_field_' . $type . '_options' (filter)
	 *
	 * @param  array  $options The field options
	 * @param  string $name    The field name
	 * @param  string $value   The field value
	 * @param  array  $pod     The Pod
	 * @param  int    $id      The field ID
	 *
	 * @return array
	 */
	public function translate_field_options( $options, $name, $value, $pod, $id ) {
		$locale = $this->locale;
		if ( ! array_key_exists( $locale, $this->languages ) || ! $this->obj_is_language_enabled( $locale, $pod ) ) {
			return $options;
		}

		foreach ( $this->get_translatable_fields( 'names' ) as $field ) {
			$translation = pods_v( $field . '_' . $locale, $options, null );
			if ( $translation ) {
				$options[ $field ] = $translation;
			}
		}

		return $options;
	}

	/**
	 * Filter hook function to overwrite the labels and description with translations (if available)
	 *
	 * @since 1.0.0
	 * @see   PodsInit.php >> setup_content_types()
	 *
	 * @param  array  $options The array of object options
	 * @param  string $object  The object type name/slug
	 *
	 * @return array
	 */
	public function translate_register_wp_object( $options, $object ) {

		$locale = $this->locale;

		$pod = pods_api()->load_pod( $object );

		if ( ! $this->obj_is_language_enabled( $locale, $pod ) ) {
			return $options;
		}

		$labels = array(
			// Default
			'name'                       => 'label', // Different.
			'singular_name'              => 'label_singular', // Different.
			'menu_name'                  => 'menu_name',
			'add_new_item'               => 'label_add_new_item',
			'edit_item'                  => 'label_edit_item',
			'view_item'                  => 'label_view_item',
			'all_items'                  => 'label_all_items',
			'search_items'               => 'label_search_items',
			'parent_item_colon'          => 'label_parent_item_colon',
			'not_found'                  => 'label_not_found',
			'items_list_navigation'      => 'label_items_list_navigation',
			'items_list'                 => 'label_items_list',

			// Post Types
			'name_admin_bar'             => 'name_admin_bar',
			'add_new'                    => 'label_add_new',
			'new_item'                   => 'label_new_item',
			'edit'                       => 'label_edit',
			'view'                       => 'label_view',
			'view_items'                 => 'label_view_items',
			'parent'                     => 'label_parent',
			'not_found_in_trash'         => 'label_not_found_in_trash',
			'archives'                   => 'label_archives',
			'attributes'                 => 'label_attributes',
			'insert_into_item'           => 'label_insert_into_item',
			'uploaded_to_this_item'      => 'label_uploaded_to_this_item',
			'featured_image'             => 'label_featured_image',
			'set_featured_image'         => 'label_set_featured_image',
			'remove_featured_image'      => 'label_remove_featured_image',
			'use_featured_image'         => 'label_use_featured_image',
			'filter_items_list'          => 'label_filter_items_list',
			// Block Editor (WP 5.0+)
			'item_published'             => 'label_item_published',
			'item_published_privately'   => 'label_item_published_privately',
			'item_reverted_to_draft'     => 'label_item_reverted_to_draft',
			'item_scheduled'             => 'label_item_scheduled',
			'item_updated'               => 'label_item_updated',
			'filter_by_date'             => 'label_filter_by_date', // WP 5.7

			// Taxonomies
			'update_item'                => 'label_update_item',
			'popular_items'              => 'label_popular_items',
			'parent_item'                => 'label_parent_item',
			'new_item_name'              => 'label_new_item_name',
			'separate_items_with_commas' => 'label_separate_items_with_commas',
			'add_or_remove_items'        => 'label_add_or_remove_items',
			'choose_from_most_used'      => 'label_choose_from_the_most_used', // Different.
			'no_terms'                   => 'label_no_terms',
			'filter_by_item'             => 'label_filter_by_item', // WP 5.7
		);

		if ( ! isset( $options['labels'] ) || ! is_array( $options['labels'] ) ) {
			$options['labels'] = array();
		} else {
			// Try to find new labels.
			foreach ( $options['labels'] as $key => $val ) {
				if ( ! isset( $labels[ $key ] ) ) {
					$labels[ $key ] = 'label_' . $key;
				}
			}
		}

		foreach ( $labels as $key => $pods_key ) {
			$label = pods_v( $pods_key . '_' . $locale, $pod, '', true );
			if ( $label ) {
				$options['labels'][ $key ] = $label;
			}
		}

		return $options;
	}

	/**
	 * Filter hook function to overwrite the labels and description with translations (if available)
	 *
	 * @since 1.0.0
	 * @see   PodsInit.php >> admin_menu()
	 *
	 * @param  array  $options The array of object options
	 * @param  string $object  The object type name/slug
	 *
	 * @return array
	 */
	public function translate_object_options( $options, $object ) {

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
	 * @since 1.0.0
	 *
	 * @param  array $tabs
	 *
	 * @return array
	 */
	public function translation_tab( $tabs ) {

		$tabs['i18n'] = __( 'Translations', 'pods' );

		return $tabs;
	}

	/**
	 * The i18n options.
	 *
	 * @since 1.0.0
	 *
	 * @param array              $options
	 * @param array|Pods\Whatsit $object
	 *
	 * @return array
	 */
	public function translation_options( $options, $object ) {
		$i18n_fields = [];

		foreach ( $options as $tab => $fields ) {
			foreach ( $fields as $name => $field ) {
				if ( ! $this->is_translatable_field( $name ) ) {
					continue;
				}

				$i18n_options = $this->get_translatable_field_options( $name );

				// None of the i18n fields are required!
				$field['required'] = false;

				$heading_field = $field;
				$heading_field['type'] = 'heading';
				$heading_field['name'] = $name . '_i18n';

				$i18n_fields[][ $name . '_i18n' ] = array_merge_recursive( $heading_field, $i18n_options );

				$default_field = $field;
				$default_field['type'] = 'html';
				$default_field['name'] = $name . '_i18n_default';
				$default_field['label'] = __( 'Default', 'pods' );
				$default_field['html_content'] = '%s';
				$default_field['html_content_param'] = $name;
				$default_field['html_content_param_default'] = '-';

				$i18n_fields[][ $name . '_i18n_default' ] = array_merge_recursive( $default_field, $i18n_options );

				foreach ( $this->languages as $locale => $lang_data ) {
					if ( ! $this->obj_is_language_enabled( $locale, $object ) ) {
						continue;
					}

					$locale_name              = $name . '_' . $locale;
					$locale_field             = $field;
					$locale_field['name']     = $locale_name;
					$locale_field['label']    = $locale;
					$locale_field['default']  = '';

					$i18n_fields[][ $locale_name ] = array_merge_recursive( $locale_field, $i18n_options );
				}
			}
		}

		$options['i18n'] = $i18n_fields;

		// if ( $object['type'] === '' )
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
		if ( $data instanceof Pods\Whatsit ) {
			$enable_i18n = $data->get_arg( 'enable_i18n' );
		} else {
			$options = pods_v( 'options', $data, $data );
			$enable_i18n = pods_v( 'enable_i18n', $options, null );
		}

		if ( null === $enable_i18n ) {
			// If there are no i18n settings in the object data then assume it's enabled.
			return true;
		}

		return (bool) pods_v( $locale, $enable_i18n, true );
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

		$label       = pods_v( 'english_name', $lang_data, '' );
		$native_name = pods_v( 'native_name', $lang_data, '' );

		if ( ! empty( $native_name ) && $label !== $native_name ) {
			$label .= ' / ' . $native_name;
		}

		return $label;
	}

	/**
	 * @param string $return Return type (supports 'names').
	 * @return array
	 */
	public function get_translatable_fields( $return = '' ) {

		/**
		 * Overwrite translatable fields.
		 *
		 * @since 2.8.4
		 *
		 * @param string[] $fields The translatable fields.
		 */
		$fields = apply_filters( 'pods_translatable_fields', $this->translatable_fields );

		// Backwards compatibility: Before v1.1 this was a list of field names instead of options.
		foreach ( $fields as $name => $value ) {
			if ( is_string( $value ) ) {
				unset( $fields[ $name ] );
				if ( ! isset( $fields[ $value ] ) ) {
					$fields[ $value ] = [];
				}
			}
		}

		if ( 'names' === $return ) {
			return array_keys( $fields );
		}

		return $fields;
	}

	/**
	 * @since 2.8.4
	 * @return array[]
	 */
	public function get_translatable_field_options( $key ) {

		$field_options = pods_v( $key, $this->get_translatable_fields(), array() );

		/**
		 * Overwrite translatable field options.
		 *
		 * @since 2.8.4
		 *
		 * @param array  $field_options The translatable field options.
		 * @param string $key           The field name.
		 */
		return apply_filters( 'pods_translatable_field_options', $field_options, $key );
	}
}
