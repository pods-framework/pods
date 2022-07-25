<?php

namespace Pods\Integrations;

use Pods\Integration;

/**
 * Class WPML
 *
 * @since 2.8.0
 */
class WPML extends Integration {

	/**
	 * @inheritdoc
	 */
	protected $hooks = [
		'action' => [
			'wpml_language_has_switched' => [ 'wpml_language_has_switched' ],
		],
		'filter' => [
			'pods_get_current_language' => [ 'pods_get_current_language', 10, 2 ],
			'pods_api_get_table_info' => [ 'pods_api_get_table_info', 10, 7 ],
			'pods_data_traverse_recurse_ignore_aliases' => [ 'pods_data_traverse_recurse_ignore_aliases', 10 ],
			'pods_pods_field_get_metadata_object_id' => [ 'pods_pods_field_get_metadata_object_id', 10, 4 ],
			'pods_component_i18n_admin_data' => [ 'pods_component_i18n_admin_data' ],
			'pods_component_i18n_admin_ui_fields' => [ 'pods_component_i18n_admin_ui_fields', 10, 2 ],
			'pods_var_post_id' => [ 'pods_var_post_id' ],
		],
	];

	/**
	 * @inheritDoc
	 */
	public static function is_active() {
		return defined( 'ICL_SITEPRESS_VERSION' ) || ! empty( $GLOBALS['sitepress'] );
	}

	/**
	 * Refresh language cache after WPML has switch language.
	 *
	 * @since 2.8.11
	 *
	 * @see \SitePress::switch_lang()
	 *
	 * @return void
	 */
	public function wpml_language_has_switched() {
		pods_i18n()->get_current_language( array( 'refresh' => true ) );
	}

	/**
	 * @since 2.8.2
	 *
	 * @param int $id
	 *
	 * @return mixed|void
	 */
	public function pods_var_post_id( $id ) {
		return apply_filters( 'wpml_object_id', $id, get_post_type( $id ), true );
	}

	/**
	 * @since 2.8.0
	 *
	 * @param array $ignore_aliases
	 *
	 * @return array
	 */
	public function pods_data_traverse_recurse_ignore_aliases( $ignore_aliases ) {

		$ignore_aliases[] = 'wpml_languages';

		return $ignore_aliases;
	}

	/**
	 * Get the current language.
	 *
	 * @since 2.8.0
	 *
	 * @param string $current_language
	 * @param array  $context
	 *
	 * @return string
	 */
	public function pods_get_current_language( $current_language, $context ) {
		// Get the global current language (if set).
		$wpml_language = apply_filters( 'wpml_current_language', null );
		$current_language = ( 'all' !== $wpml_language ) ? $wpml_language : '';

		if ( ! is_admin() ) {
			return $current_language;
		}

		$defaults = [
			'is_admin'            => is_admin(),
			'is_ajax'             => null,
			'is_pods_ajax'        => null,
			'current_page'        => '',
			'current_object_type' => '',
			'current_item_id'     => '',
			'current_item_type'   => '',
		];

		$context = wp_parse_args( $context, $defaults );

		$object_type = $context['current_object_type'];
		$item_type   = $context['current_item_type'];

		if ( ! $item_type ) {
			return $current_language;
		}

		/**
		 * In WPML the current language is always set to default on an edit screen.
		 * We need to overwrite this when the current object is not-translatable to enable relationships with different languages.
		 */
		switch ( $object_type ) {
			case 'post':
				if ( ! $this->is_translated_post_type( $item_type ) ) {
					// Overwrite the current language to nothing if this is a NOT-translatable post_type.
					$current_language = '';
				}
				break;

			case 'term':
				if ( ! $this->is_translated_taxonomy( $item_type ) ) {
					// Overwrite the current language to nothing if this is a NOT-translatable taxonomy.
					$current_language = '';
				}
				break;

			case 'comment';
				// @todo Get comment post parent??
				//$current_language = '';
				break;
		}

		return $current_language;
	}

	/**
	 * Support for WPML 'duplicated' translation handling.
	 *
	 * @param int         $id
	 * @param string      $metadata_type
	 * @param array       $params
	 * @param array|\Pods $pod
	 *
	 * @return int
	 */
	public function pods_pods_field_get_metadata_object_id( $id, $metadata_type, $params, $pod ) {
		if ( ! did_action( 'wpml_loaded' ) ) {
			return $id;
		}
		switch ( $metadata_type ) {
			case 'post':
				if ( $this->is_translated_post_type( $pod->pod_data['name'] ) ) {
					$master_post_id = (int) apply_filters( 'wpml_master_post_from_duplicate', $id );

					if ( $master_post_id ) {
						$id = $master_post_id;
					}
				}
				break;
		}
		return $id;
	}

	/**
	 * Filter table info data.
	 *
	 * @since 2.8.0
	 *
	 * @param array       $info
	 * @param string      $object_type
	 * @param string      $object
	 * @param string      $name
	 * @param array|\Pods $pod
	 * @param array       $field
	 * @param \PodsAPI    $pods_api
	 *
	 * @return array
	 */
	public function pods_api_get_table_info( $info, $object_type, $object, $name, $pod, $field, $pods_api ) {
		global $wpdb;

		if ( ! apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
			return $info;
		}

		// Get current language
		$current_language = pods_i18n()->get_current_language();

		if ( ! $current_language ) {
			return $info;
		}

		$object_name = pods_sanitize( ( empty( $object ) ? $name : $object ) );

		$db_prefix = $wpdb->get_blog_prefix();

		$wpml_translations = false;

		switch ( $object_type ) {

			case 'post':
			case 'post_type':
			case 'media':
				if ( $this->is_translated_post_type( $object_name ) ) {
					$wpml_translations = "
						LEFT JOIN `{$db_prefix}icl_translations` AS `wpml_translations`
							ON `wpml_translations`.`element_id` = `t`.`ID`
								AND `wpml_translations`.`element_type` = 'post_" . pods_sanitize( $object_name ) . "'
								AND `wpml_translations`.`language_code` = '" . pods_sanitize( $current_language ) . "'
					";
				}
				break;

			case 'taxonomy':
			case 'term':
			case 'nav_menu':
			case 'post_format':
				if ( $this->is_translated_taxonomy( $object_name ) ) {
					$wpml_translations = "
						LEFT JOIN `{$db_prefix}icl_translations` AS `wpml_translations`
							ON `wpml_translations`.`element_id` = `tt`.`term_taxonomy_id`
								AND `wpml_translations`.`element_type` = 'tax_" . pods_sanitize( $object_name ) . "'
								AND `wpml_translations`.`language_code` = '" . pods_sanitize( $current_language ) . "'
					";
				}
				break;
		}

		if ( $wpml_translations ) {

			$info['join']['wpml_translations'] = $wpml_translations;

			$info['join']['wpml_languages'] = "
				LEFT JOIN `{$db_prefix}icl_languages` AS `wpml_languages`
					ON `wpml_languages`.`code` = `wpml_translations`.`language_code` AND `wpml_languages`.`active` = 1
			";

			$info['where']['wpml_languages'] = "`wpml_languages`.`code` IS NOT NULL";
		}

		return $info;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function pods_component_i18n_admin_data( $data ) {

		foreach ( $data as $lang => $field_data ) {
			if ( in_array( $lang, $this->get_locales(), true ) ) {
				$data[ $lang ]['wpml'] = true;
			} else {
				$data[ $lang ]['wpml'] = false;
			}
		}

		return $data;
	}

	/**
	 * @param array $fields
	 * @param array $data
	 *
	 * @return array
	 */
	public function pods_component_i18n_admin_ui_fields( $fields, $data ) {

		$fields['manage']['wpml'] = array(
			'label' => __( 'WPML', 'pods' ),
			'type'  => 'boolean',
		);

		return $fields;
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

	/**
	 * @return array
	 */
	public function get_language( $locale ) {
		$languages = $this->get_languages();
		$language  = null;
		if ( ! empty( $languages ) ) {
			foreach ( $languages as $lang => $lang_data ) {
				if ( isset( $lang_data['default_locale'] ) && $locale === $lang_data['default_locale'] ) {
					$language = $lang_data;
					break;
				}
			}
		}
		return $language;
	}

	/**
	 * @return string[]
	 */
	public function get_locales() {
		$languages = $this->get_languages();
		$locales   = [];
		if ( ! empty( $languages ) ) {
			foreach ( $languages as $lang => $lang_data ) {
				if ( isset( $lang_data['default_locale'] ) ) {
					$locales[] = $lang_data['default_locale'];;
				}
			}
		}
		return $locales;
	}

	/**
	 * @return array
	 */
	public function get_languages() {
		return apply_filters( 'wpml_active_languages', array() );
	}
}
