<?php

namespace Pods\Integrations;

/**
 * Class WPML
 *
 * @since 2.8.0
 */
class WPML {

	/**
	 * Add the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function hook() {
		add_filter( 'pods_api_get_table_info', [ $this, 'pods_api_get_table_info' ], 10, 7 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		remove_action( 'pods_api_get_table_info', [ $this, 'pods_api_get_table_info' ], 10, 7 );
	}

	/**
	 * Filter table info data.
	 *
	 * @param $info
	 * @param $object_type
	 * @param $object
	 * @param $name
	 * @param $pod
	 * @param $field
	 * @param $pods_api
	 *
	 * @since 2.8.0
	 */
	public function pods_api_get_table_info( $info, $object_type, $object, $name, $pod, $field, $pods_api ) {
		global $wpdb, $sitepress;

		if ( ! apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
			return $info;
		}

		$object_name = pods_sanitize( ( empty( $object ) ? $name : $object ) );

		// Get current language
		$current_language = pods_i18n()->get_current_language();

		switch ( $object_type ) {

			case 'post':
			case 'post_type':
				if ( $this->is_translated_post_type( $object_name ) ) {
					$info['join']['wpml_translations'] = "
						LEFT JOIN `{$wpdb->prefix}icl_translations` AS `wpml_translations`
							ON `wpml_translations`.`element_id` = `t`.`ID`
								AND `wpml_translations`.`element_type` = 'post_" . pods_sanitize( $object_name ) . "'
								AND `wpml_translations`.`language_code` = '" . pods_sanitize( $current_language ) . "'
					";

					$info['join']['wpml_languages'] = "
						LEFT JOIN `{$wpdb->prefix}icl_languages` AS `wpml_languages`
							ON `wpml_languages`.`code` = `wpml_translations`.`language_code` AND `wpml_languages`.`active` = 1
					";

					$info['where']['wpml_languages'] = "`wpml_languages`.`code` IS NOT NULL";
				}
				break;

			case 'taxonomy':
				if ( $this->is_translated_taxonomy( $object_name ) ) {
					$info['join']['wpml_translations'] = "
						LEFT JOIN `{$wpdb->prefix}icl_translations` AS `wpml_translations`
							ON `wpml_translations`.`element_id` = `tt`.`term_taxonomy_id`
								AND `wpml_translations`.`element_type` = 'tax_" . pods_sanitize( $object_name ) . "'
								AND `wpml_translations`.`language_code` = '" . pods_sanitize( $current_language ) . "'
					";

					$info['join']['wpml_languages'] = "
						LEFT JOIN `{$wpdb->prefix}icl_languages` AS `wpml_languages`
							ON `wpml_languages`.`code` = `wpml_translations`.`language_code` AND `wpml_languages`.`active` = 1
					";

					$info['where']['wpml_languages'] = "`wpml_languages`.`code` IS NOT NULL";
				}
				break;
		}

		return $info;
	}

	/**
	 * Helper method for backwards compatibility.
	 *
	 * @since 2.8.0
	 *
	 * @param string $object_name
	 *
	 * @return false|mixed|void
	 */
	public function is_translated_post_type( $object_name ) {
		global $sitepress;
		if ( has_filter( 'wpml_is_translated_post_type' ) ) {
			return apply_filters( 'wpml_is_translated_post_type', false, $object_name );
		} elseif ( is_callable( [ $sitepress, 'is_translated_post_type' ] ) ) {
			return $sitepress->is_translated_post_type( $object_name );
		}
		return false;
	}

	/**
	 * Helper method for backwards compatibility.
	 *
	 * @since 2.8.0
	 *
	 * @param string $object_name
	 *
	 * @return false|mixed|void
	 */
	public function is_translated_taxonomy( $object_name ) {
		global $sitepress;
		if ( has_filter( 'wpml_is_translated_taxonomy' ) ) {
			return apply_filters( 'wpml_is_translated_taxonomy', false, $object_name );
		} elseif ( is_callable( [ $sitepress, 'is_translated_taxonomy' ] ) ) {
			return $sitepress->is_translated_taxonomy( $object_name );
		}
		return false;
	}
}
