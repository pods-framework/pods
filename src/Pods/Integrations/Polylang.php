<?php

namespace Pods\Integrations;

use Pods\Integration;

/**
 * Class Polylang
 *
 * @since 2.8.0
 */
class Polylang extends Integration {

	/**
	 * @inheritdoc
	 */
	protected $hooks = [
		'action' => [
			'pods_meta_init' => [ 'pods_meta_init' ],
			'pods_form_ui_field_pick_related_objects_other' => [ 'pods_pick_field_add_related_objects' ],
		],
		'filter' => [
			'pods_get_current_language' => [ 'pods_get_current_language', 10, 2 ],
			'pods_api_get_table_info' => [ 'pods_api_get_table_info', 10, 7 ],
			'pods_data_traverse_recurse_ignore_aliases' => [ 'pods_data_traverse_recurse_ignore_aliases', 10 ],
			'pods_meta_ignored_types' => [ 'pods_meta_ignored_types' ],
			'pods_component_i18n_admin_data' => [ 'pods_component_i18n_admin_data' ],
			'pods_component_i18n_admin_ui_fields' => [ 'pods_component_i18n_admin_ui_fields', 10, 2 ],
			'pods_var_post_id' => [ 'pods_var_post_id' ],
			'pll_get_post_types' => [ 'pll_get_post_types', 10, 2 ],
		],
	];

	/**
	 * @inheritDoc
	 */
	public static function is_active() {
		return function_exists( 'PLL' ) || ! empty( $GLOBALS['polylang'] );
	}

	/**
	 * @since 2.8.2
	 *
	 * @param int $id
	 *
	 * @return mixed|void
	 */
	public function pods_var_post_id( $id ) {
		$polylang_id = pll_get_post( $id );
		if ( ! empty( $polylang_id ) ) {
			$id = $polylang_id;
		}
		return $id;
	}

	/**
	 * Add Pods templates to possible i18n enabled post-types (polylang settings).
	 *
	 * @since 2.7.0
	 * @since 2.8.0 Moved from PodsI18n class.
	 *
	 * @param  array $post_types
	 * @param  bool  $is_settings
	 *
	 * @return array  mixed
	 */
	public function pll_get_post_types( $post_types, $is_settings = false ) {

		if ( $is_settings ) {
			$post_types['_pods_template'] = '_pods_template';
		}

		return $post_types;
	}

	/**
	 * @since 2.8.0
	 *
	 * @param \PodsMeta $pods_meta
	 */
	public function pods_meta_init( $pods_meta ) {

		if ( function_exists( 'pll_current_language' ) ) {
			add_action( 'init', array( $pods_meta, 'cache_pods' ), 101, 0 );
		}
	}

	/**
	 * @since 2.8.8
	 *
	 * @param array[] $ignored_types
	 *
	 * @return mixed
	 */
	public function pods_meta_ignored_types( $ignored_types ) {

		// Add Polylang related taxonomies to the ignored types for PodsMeta.
		$ignored_types['taxonomy']['language'] = true;
		$ignored_types['taxonomy']['term_language'] = true;
		$ignored_types['taxonomy']['post_translations'] = true;
		$ignored_types['taxonomy']['term_translations'] = true;

		return $ignored_types;
	}

	/**
	 * Add the Polylang language taxonomy to be used in relationships.
	 *
	 * @since 2.8.21
	 */
	public function pods_pick_field_add_related_objects() {
		$taxonomy = get_taxonomy( 'language' );

		\PodsField_Pick::$related_objects[ 'taxonomy-language' ] = array(
			'label'         => $taxonomy->label . ' (' . $taxonomy->name . ')',
			'group'         => __( 'Polylang', 'pods' ),
			'bidirectional' => false,
		);
	}

	/**
	 * @since 2.8.0
	 *
	 * @param array $ignore_aliases
	 *
	 * @return array
	 */
	public function pods_data_traverse_recurse_ignore_aliases( $ignore_aliases ) {

		$ignore_aliases[] = 'polylang_languages';

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

		if ( ! is_admin() ) {
			// Get the global current language (if set).
			return pll_current_language( 'slug' );
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
		$item_id     = $context['current_item_id'];
		$item_type   = $context['current_item_type'];

		/**
		 * Get the current user's preferred language.
		 * This is a user meta setting that will overwrite the language returned from pll_current_language().
		 *
		 * @see \PLL_Admin_Base::init_user() (polylang/admin/admin-base.php)
		 */
		$current_language = get_user_meta( get_current_user_id(), 'pll_filter_content', true );

		if ( ! $item_type ) {
			return $current_language;
		}

		/**
		 * In polylang the preferred language could be anything.
		 */
		switch ( $object_type ) {
			case 'post':
				if ( $this->is_translated_post_type( $item_type ) ) {

					/**
					 * Polylang (1.5.4+).
					 * We only want the related objects if they are not translatable OR the same language as the current object.
					 */
					if ( $item_id && function_exists( 'pll_get_post_language' ) ) {
						// Overwrite the current language if this is a translatable post_type.
						$current_language = pll_get_post_language( $item_id );
					}

					/**
					 * Polylang (1.0.1+).
					 * When we're adding a new object and language is set we only want the related objects if they are not translatable OR the same language.
					 */
					$current_language = pods_v( 'new_lang', 'request', $current_language );
				}
				break;

			case 'term':
				if ( $this->is_translated_taxonomy( $item_type ) ) {

					/**
					 * Polylang (1.5.4+).
					 * We only want the related objects if they are not translatable OR the same language as the current object.
					 */
					if ( $item_id && function_exists( 'pll_get_term_language' ) ) {
						// Overwrite the current language if this is a translatable taxonomy
						$current_language = pll_get_term_language( $item_id );
					}

					/**
					 * Polylang (1.0.1+).
					 * When we're adding a new object and language is set we only want the related objects if they are not translatable OR the same language.
					 */
					$current_language = pods_v( 'new_lang', 'request', $current_language );
				}
				break;
		}

		return $current_language;
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
		$object_name = pods_sanitize( ( empty( $object ) ? $name : $object ) );

		// Get current language data
		$lang_data = $this->get_language_data();

		if ( ! $lang_data ) {
			return $info;
		}

		$current_language_tt_id    = 0;
		$current_language_tl_tt_id = 0;

		if ( ! empty( $lang_data['tt_id'] ) ) {
			$current_language_tt_id = $lang_data['tt_id'];
		}
		if ( ! empty( $lang_data['tl_tt_id'] ) ) {
			$current_language_tl_tt_id = $lang_data['tl_tt_id'];
		}

		switch ( $object_type ) {

			case 'post':
			case 'post_type':
			case 'media':
				if ( $current_language_tt_id && $this->is_translated_post_type( $object_name ) ) {
					$info['join']['polylang_languages'] = "
						LEFT JOIN `{$wpdb->term_relationships}` AS `polylang_languages`
							ON `polylang_languages`.`object_id` = `t`.`ID`
								AND `polylang_languages`.`term_taxonomy_id` = {$current_language_tt_id}
					";

					$info['where']['polylang_languages'] = "`polylang_languages`.`object_id` IS NOT NULL";
				}
				break;

			case 'taxonomy':
			case 'term':
			case 'nav_menu':
			case 'post_format':
				if ( $current_language_tl_tt_id && $this->is_translated_taxonomy( $object_name ) ) {
					$info['join']['polylang_languages'] = "
					LEFT JOIN `{$wpdb->term_relationships}` AS `polylang_languages`
						ON `polylang_languages`.`object_id` = `t`.`term_id`
							AND `polylang_languages`.`term_taxonomy_id` = {$current_language_tl_tt_id}
					";

					$info['where']['polylang_languages'] = "`polylang_languages`.`object_id` IS NOT NULL";
				}
				break;
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
				$data[ $lang ]['polylang'] = true;
			} else {
				$data[ $lang ]['polylang'] = false;
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

		$fields['manage']['polylang'] = array(
			'label' => __( 'Polylang', 'pods' ),
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
		if ( function_exists( 'pll_is_translated_post_type' ) ) {
			return pll_is_translated_post_type( $object_name );
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
		if ( function_exists( 'pll_is_translated_taxonomy' ) ) {
			return pll_is_translated_taxonomy( $object_name );
		}
		return false;
	}

	/**
	 * Get the language taxonomy object for the current language.
	 *
	 * @since 2.8.0
	 *
	 * @param string $locale
	 *
	 * @return array
	 */
	public function get_language_data( $locale = null ) {
		static $lang_data = [];

		if ( ! $locale ) {
			$locale = pods_i18n()->get_current_language();
		}

		if ( isset( $lang_data[ $locale ] ) ) {
			return $lang_data[ $locale ];
		}

		if ( ! $locale ) {
			return null;
		}

		// We need to return language data
		$lang_data = array(
			'language' => $locale,
			't_id'     => 0,
			'tt_id'    => 0,
			'tl_t_id'  => 0,
			'tl_tt_id' => 0,
			'term'     => null,
		);

		$language = $this->get_language( $locale );

		// If the language object exists, add it!
		if ( $language && ! empty( $language->term_id ) ) {

			$lang_data['term'] = $language;
			$lang_data['t_id'] = (int) $language->term_id;

			if ( method_exists( $language, 'get_tax_prop' ) ) {
				// Since Polylang 3.4
				$lang_data['tt_id']    = (int) $language->get_tax_prop( 'language', 'term_taxonomy_id' );
				$lang_data['tl_t_id']  = (int) $language->get_tax_prop( 'term_language', 'term_id' );
				$lang_data['tl_tt_id'] = (int) $language->get_tax_prop( 'term_language', 'term_taxonomy_id' );
			} else {
				// Pre Polylang 3.4
				$lang_data['tt_id']    = (int) $language->term_taxonomy_id;
				$lang_data['tl_t_id']  = (int) $language->tl_term_id;
				$lang_data['tl_tt_id'] = (int) $language->tl_term_taxonomy_id;
			}
		}

		$lang_data[ $locale ] = $lang_data;

		return $lang_data[ $locale ];
	}

	/**
	 * @param $locale
	 *
	 * @return false|\PLL_Language
	 */
	public function get_language( $locale ) {
		$language = false;

		if ( ! $locale ) {
			$locale = pods_i18n()->get_current_language();
		}

		// Get the language term object.
		if ( function_exists( 'PLL' ) && isset( PLL()->model ) && method_exists( PLL()->model, 'get_language' ) ) {
			// Polylang 1.8 and newer.
			$language = PLL()->model->get_language( $locale );
		} else {
			global $polylang;
			if ( is_object( $polylang ) && isset( $polylang->model ) && method_exists( $polylang->model, 'get_language' ) ) {
				// Polylang 1.2 - 1.7.x
				$language = $polylang->model->get_language( $locale );
			} elseif ( is_object( $polylang ) && method_exists( $polylang, 'get_language' ) ) {
				// Polylang 1.1.x and older.
				$language = $polylang->get_language( $locale );
			}
		}

		return $language;
	}

	/**
	 * @return string[]
	 */
	public function get_locales() {
		$locales = [];
		if ( function_exists( 'pll_languages_list' ) ) {
			$locales = pll_languages_list( array( 'fields' => 'locale' ) );
		}
		return $locales;
	}

	/**
	 * @return array
	 */
	public function get_languages() {
		$languages = [];
		if ( function_exists( 'pll_languages_list' ) ) {
			$languages = pll_languages_list( array( 'fields' => null ) );
		}
		return $languages;
	}
}
