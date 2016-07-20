<?php 

class Pods_I18n_WPML {

	public $languages = array();
	public $textdomain = 'sitepress-multilingual-cms';

	public function __construct() {

		$languages = apply_filters( 'wpml_active_languages', array() );
		if ( ! empty( $languages ) ) {
			foreach ($languages as $lang => $lang_data) {
				if ( isset( $lang_data['default_locale'] ) ) {
					$locale = $lang_data['default_locale'];
					$this->languages[] = $locale;
				}
			}

			add_filter( 'pods_component_i18n_admin_data', array( $this, 'pods_component_i18n_admin_data' ) );
			add_filter( 'pods_component_i18n_admin_ui_fields', array( $this, 'pods_component_i18n_admin_ui_fields' ), 10, 2 );
		}

	}

	public function pods_component_i18n_admin_data( $data ) {
		foreach ( $data as $lang => $field_data ) {
			if ( in_array( $lang, $this->languages ) ) {
				$data[$lang]['wpml'] = true;
			} else {
				$data[$lang]['wpml'] = false;
			}
		}
		return $data;
	}

	public function pods_component_i18n_admin_ui_fields( $fields, $data ) {
		$fields['manage']['wpml'] = array(
			'label' => __( 'WPML' , $this->textdomain ),
			'type' => 'boolean',
			/*'options' => array(
				'text_allow_html' => 1,
				'text_allowed_html_tags' => 'br a',
			)*/
		);
		return $fields;
	}

}

new Pods_I18n_WPML();