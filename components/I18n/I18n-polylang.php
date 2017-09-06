<?php 

class Pods_I18n_Polylang {

	public $languages = array();
	public $textdomain = 'polylang';

	public function __construct() {

		if ( function_exists( 'pll_languages_list' ) ) {
			$this->languages = pll_languages_list( array( 'fields' => 'locale' ) );

			add_filter( 'pods_component_i18n_admin_data', array( $this, 'pods_component_i18n_admin_data' ) );
			add_filter( 'pods_component_i18n_admin_ui_fields', array( $this, 'pods_component_i18n_admin_ui_fields' ), 10, 2 );
		}

	}

	public function pods_component_i18n_admin_data( $data ) {
		foreach ( $data as $lang => $field_data ) {
			if ( in_array( $lang, $this->languages ) ) {
				$data[$lang]['polylang'] = true;
			} else {
				$data[$lang]['polylang'] = false;
			}
		}
		return $data;
	}

	public function pods_component_i18n_admin_ui_fields( $fields, $data ) {
		$fields['manage']['polylang'] = array(
			'label' => __( 'Polylang' , $this->textdomain ),
			'type' => 'boolean',
			/*'options' => array(
				'text_allow_html' => 1,
				'text_allowed_html_tags' => 'br a',
			)*/
		);
		return $fields;
	}

}

new Pods_I18n_Polylang();