<?php
/**
 * Name: Multilingual Pods labels
 *
 * Menu Name: Multilingual Pods labels
 *
 * Description: Allow Pod name, labels and description to be translated
 *
 * Version: 0.1
 *
 * Category: Tools
 *
 * @package Pods\Components
 * @subpackage i18n
 */

! defined( 'ABSPATH' ) and die();

if ( class_exists( 'Pods_I18n' ) )
	return;

class Pods_I18n extends PodsComponent {

	public $locale = null;
	public $languages = array();
	public $language_translations = array();
	public $cur_pod = null;

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

		$this->locale = get_locale();

		if ( is_admin() && ( isset( $_GET['page'] ) && ( $_GET['page'] == 'pods' /*|| $_GET['page'] == 'pods-add-new'*/ ) ) ) {

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

			$this->languages = get_available_languages();

			/**
			 * format: array( language, version, updated, english_name, native_name, package, iso, strings )
			 */
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
			$this->language_translations = wp_get_available_translations();

			add_filter( 'pods_form_ui_field_text', array( $this, 'add_i18n_inputs' ), 10, 6 );
		
		} elseif ( is_admin() ) {
			/*foreach ( pods_form()->field_types() as $type => $data ) {
				//add_filter( 'pods_form_ui_field_' . $type . '_data', array( $this, 'field_options_i18n' ), 10, 3 );
				//add_filter( 'pods_form_ui_field_' . $type . '_merge_attributes', array( $this, 'merge_attributes_fields_i18n' ), 10, 3 );
			}*/

			// Default filters for all fields
			add_filter( 'pods_form_ui_label_text', array( $this, 'fields_ui_label_text_i18n' ), 10, 4 );
			add_filter( 'pods_form_ui_comment_text', array( $this, 'fields_ui_comment_text_i18n' ), 10, 3 );

			// Field specific
			add_filter( 'pods_field_pick_data', array( $this, 'field_pick_data_i18n' ), 10, 6 );
		}

		// Object filters
		add_filter( 'pods_register_post_type', array( $this, 'pods_register_object_i18n' ), 10, 2 );
		add_filter( 'pods_register_taxonomy', array( $this, 'pods_register_object_i18n' ), 10, 2 );

		add_filter( 'pods_form_ui_field_date_args', array( $this, 'field_date_args_i18n' ), 10, 6 );
		add_filter( 'pods_form_ui_field_datetime_args', array( $this, 'field_date_args_i18n' ), 10, 6 );

	}

	/**
	 * @since 0.1
	 * @todo Make it work :)
	 */
	function field_options_i18n( $options, $type, $bla ) {
		print_r($options);
		return $options;
	}

	/**
	 * @since 0.1
	 * @todo Make it work :)
	 */
	public function merge_attributes_fields_i18n( $attributes, $name, $options ) {
		print_r($attributes);
		return $attributes;
	}

	/**
	 * Adds translation inputs to fields
	 * 
	 * @since 0.1
	 * @see PodsForm.php >> 'pods_form_ui_field_' . $type (filter)
	 * 
	 * @param string $output The default output of the field
	 * @param string $name The field name
	 * @param string $value The field value
	 * @param array $options The field options
	 * @param array $pod The Pod
	 * @param int $id The field ID
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

		foreach ( $this->languages as $lang ) {
			// Our own shiny label with language information
			$lang_label = '<code style="font-size: 1em;">' . $lang . '</code>';
			if ( array_key_exists( $lang, $this->language_translations ) ) {
				$lang_label = $lang_label . ' ('. $this->language_translations[ $lang ]['english_name'] .'/'. $this->language_translations[ $lang ]['native_name'] .')';
			}
			$lang_label = '<small>' . $lang_label . '</small>';

			// Add language data to name for normal strings and array formatted strings
			if ( strpos( $name, ']' ) !== false ) {
				$field_name = rtrim( $name, ']' );
				$field_name .= '_'.$lang.']';
			} else {
				$field_name = $name.'_'.$lang;
			}

			// Add the translation fields
			$output .= '<br clear="both" />';
			$output .= PodsForm::label( $field_name, $lang_label, __( 'help', 'pods' ) );
			$output .= PodsForm::field( $field_name, pods_v( $field_name, $pod ), 'text', null, $pod );
		}
		return $output;
	}

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
			$name = end($name);
			// All translateable fields from field_data[ (int) ][ $name ]
			if ( in_array( $name, $translatable_fields ) ) {
				return true;
			}           
		}
		return false;
	}

	/**
	 * Returns the translated label if available
	 * 
	 * @since 0.1
	 * @see PodsForm.php >> 'pods_form_ui_label_text' (filter)
	 * 
	 * @param string $label The default label
	 * @param string $name The field name
	 * @param string $help The help text
	 * @param array $options The field options
	 * @return string
	 */
	public function fields_ui_label_text_i18n( $label, $name, $help, $options ) {
		$locale = $this->locale;
		// Validate field by checking if the default label is set and is the same as the label send with this function
		if ( isset( $options['label'] ) && $options['label'] == $label && ! empty( $options[ 'label_' . $locale ] ) ) {
			return $options[ 'label_' . $locale ];
		}
		return $label;
	}

	/**
	 * Returns the translated description if available
	 * 
	 * @since 0.1
	 * @see PodsForm.php >> 'pods_form_ui_comment_text' (filter)
	 * 
	 * @param string $message The default description
	 * @param string $name The field name
	 * @param array $options The field options
	 * @return string
	 */
	public function fields_ui_comment_text_i18n( $message, $name, $options ) {
		$locale = $this->locale;
		// Validate field by checking if the default description is set and is the same as the message send with this function
		if ( isset( $options['description'] ) && $options['description'] == $message && ! empty( $options[ 'description_' . $locale ] ) ) {
			return $options[ 'description_' . $locale ];
		}
		return $message;
	}

	/**
	 * Replaces the default selected text with a translation if available
	 * 
	 * @since 0.1
	 * @see pick.php >> 'pods_field_pick_data' (filter)
	 * 
	 * @param array $data The default data of the field
	 * @param string $name The field name
	 * @param string $value The field value
	 * @param array $options The field options
	 * @param array $pod The Pod
	 * @param int $id The field ID
	 * @return array
	 */ 
	public function field_pick_data_i18n( $data, $name, $value, $options, $pod, $id ) {
		if ( isset( $data[''] ) && isset( $options['pick_select_text'] ) ) {
			$locale = $this->locale;
			if ( isset( $options['pick_select_text_' . $locale ] ) ) {
				$data[''] = $options['pick_select_text_' . $locale ];
			}
		}
		return $data;
	}

	/**
	 * Get the i18n files for jquery datepicker from the github repository
	 * 
	 * @since 0.1
	 * @link https://jqueryui.com/datepicker/#localization
	 * @link https://github.com/jquery/jquery-ui/tree/master/ui/i18n
	 * 
	 * @param array $args datepicker arguments
	 * @param string $type datepicker type
	 * @param array $options field options
	 * @param array $attributes field attibutes
	 * @param string $name field name
	 * @param string $form_field_type field type
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
	 * @url https://github.com/jquery/jquery-ui/tree/master/ui/i18n
	 * @return string ex: "fr" ou "en-GB"
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
				$locale = substr($locale, 0, strpos($locale, '-'));
				//English is the default locale
				$locale = ($locale == 'en') ? '' : $locale;
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
		$cpt_locale_labels['menu_name']          = pods_v( 'menu_name_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['add_new']            = pods_v( 'label_add_new_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['add_new_item']       = pods_v( 'label_add_new_item_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['new_item']           = pods_v( 'label_new_item_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['edit']               = pods_v( 'label_edit_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['edit_item']          = pods_v( 'label_edit_item_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['view']               = pods_v( 'label_view_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['view_item']          = pods_v( 'label_view_item_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['all_items']          = pods_v( 'label_all_items_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['search_items']       = pods_v( 'label_search_items_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['not_found']          = pods_v( 'label_not_found_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['not_found_in_trash'] = pods_v( 'label_not_found_in_trash_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['parent']             = pods_v( 'label_parent_'.$locale, $pod, '', null, true );
		$cpt_locale_labels['parent_item_colon']  = pods_v( 'label_parent_item_colon_'.$locale, $pod, '', null, true );

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
	 * Build admin area
	 *
	 * @param $options
	 * @param $component
	 *
	 * @return void
	 * @since 0.1
	 */
	/*
	public function admin ( $options, $component ) {

		//wp_get_installed_translations()
		$api = pods_api();

		$pods = $api->load_pods();
		//$pod_templates = $api->load_templates();
		//$pod_pages = $api->load_pages();
		//$pod_helpers = $api->load_helpers();

		//echo '<pre>';print_r($pods);echo'</pre>';

		$data = array();

		foreach ( $pods as $key => $pod ) {
			$fields = array();
			$total_labels = count( $this->get_pod_labels( $pod ) );
			$total_field_labels = 0;
			foreach ( $pod['fields'] as $field ) {
				$fields[] = $field['label'] . ' <code>'.$field['name'].'</code>';
				$total_field_labels += count( $this->get_field_labels( $field ) );
			}

			$data[ $key ] = array(
				'id' => $pod['id'],
				'pod' => $pod['label'],
				'total_labels' => $total_labels,
				'fields' => implode( '<br />', $fields ),
				'total_field_labels' => $total_field_labels,
			);
		}

		$ui = array(
			'component' => $component,
			'data' => $data,
			'total' => count( $data ),
			'total_found' => count( $data ),
			'items' => 'Multilingual Pods labels',
			'item' => 'Multilingual Pods labels',
			'fields' => array(
				'manage' => array(
					'pod' => array( 'label' => __( 'Pod', 'pods' ) ),
					'total_labels' => array( 'label' => __( 'Total labels', 'pods' ) ),
					'fields' => array(
						'label' => __( 'Fields', 'pods' ),
						'type' => 'text',
						'options' => array(
							'text_allow_html' => 1,
							'text_allowed_html_tags' => 'br code'
						)
					),
					'total_field_labels' => array( 'label' => __( 'Total field labels', 'pods' ) ),
				)
			),
			'actions_disabled' => array( 'add', 'delete', 'duplicate', 'view', 'export' ),
			'actions_custom' => array(
				//'add' => array( $this, 'admin_add' ),
				'edit' => array( $this, 'admin_edit' ),
				//'delete' => array( $this, 'admin_delete' )
			),
			'search' => false,
			'searchable' => false,
			'sortable' => false,
			'pagination' => false
		);

		if ( !pods_is_admin( array( 'pods_i18n_labels_edit' ) ) )
			$ui[ 'actions_disabled' ][] = 'edit';

		pods_ui( $ui );
	}

	public function admin_edit() {
		
	}

	public function get_pod_labels( $pod ) {

		$labels = array();

		if ( isset( $pod['label'] ) ) {
			$labels['label'] = $pod['label'];
		}
		// Add option labels
		if ( isset( $pod['options'] ) && is_array( $pod['options'] ) ) {
			foreach( $pod['options'] as $key => $value ) {
				if ( strpos( $key, 'label' ) !== false ) {
					$labels[ $key ] = $value;
				}
			}
		}
		if ( isset( $pod['description'] ) ) {
			$labels['description'] = $pod['description'];
		}

		return $labels;

	}

	public function get_field_labels( $pod ) {

		$labels = array();
		$field_option_labels = array(
			'file_add_button',
			'file_modal_title',
			'file_modal_add_button',
			'placeholder',
		);

		if ( isset( $pod['label'] ) ) {
			$labels['label'] = $pod['label'];
		}
		// Add option labels
		if ( isset( $pod['options'] ) && is_array( $pod['options'] ) ) {
			foreach( $pod['options'] as $key => $value ) {
				if ( strpos( $key, 'label' ) !== false ) {
					$labels[ $key ] = $value;
				}
				if ( in_array( $key, $field_option_labels ) ) {
					$labels[ $key ] = $value;
				}
			}
		}
		if ( isset( $pod['description'] ) ) {
			$labels['description'] = $pod['description'];
		}

		return $labels;

	}
	*/
}
