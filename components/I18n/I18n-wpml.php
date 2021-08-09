<?php

/**
 * Class Pods_I18n_WPML
 */
class Pods_I18n_WPML {

	public $languages = array();

	/**
	 * Pods_I18n_WPML constructor.
	 */
	public function __construct() {

		$languages = apply_filters( 'wpml_active_languages', array() );
		if ( ! empty( $languages ) ) {
			foreach ( $languages as $lang => $lang_data ) {
				if ( isset( $lang_data['default_locale'] ) ) {
					$locale            = $lang_data['default_locale'];
					$this->languages[] = $locale;
				}
			}

			add_filter( 'pods_component_i18n_admin_data', array( $this, 'pods_component_i18n_admin_data' ) );
			add_filter(
				'pods_component_i18n_admin_ui_fields', array(
					$this,
					'pods_component_i18n_admin_ui_fields',
				), 10, 2
			);
		}

	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	public function pods_component_i18n_admin_data( $data ) {

		foreach ( $data as $lang => $field_data ) {
			if ( in_array( $lang, $this->languages, true ) ) {
				$data[ $lang ]['wpml'] = true;
			} else {
				$data[ $lang ]['wpml'] = false;
			}
		}

		return $data;
	}

	/**
	 * @param $fields
	 * @param $data
	 *
	 * @return mixed
	 */
	public function pods_component_i18n_admin_ui_fields( $fields, $data ) {

		$fields['manage']['wpml'] = array(
			'label' => __( 'WPML', 'pods' ),
			'type'  => 'boolean',
			/*
			'options' => array(
				'text_allow_html' => 1,
				'text_allowed_html_tags' => 'br a',
			)*/
		);

		return $fields;
	}

}

new Pods_I18n_WPML();
