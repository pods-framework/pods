<?php

namespace Pods\Integrations;

/**
 * Class Polylang
 *
 * @since 2.8.0
 */
class Polylang {

	/**
	 * Whether the plugin is active.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function is_active() {
		return function_exists( 'PLL' ) || ! empty( $GLOBALS['polylang'] );
	}

	/**
	 * Add the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function hook() {
		add_action( 'pods_meta_init', [ $this, 'pods_meta_init' ] );

		add_filter( 'pll_get_post_types', [ $this, 'pll_get_post_types' ], 10, 2 );
		add_filter( 'pods_api_get_table_info', [ $this, 'pods_api_get_table_info' ], 10, 7 );
		add_filter( 'pods_data_traverse_recurse_ignore_aliases', [ $this, 'pods_data_traverse_recurse_ignore_aliases' ], 10 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		remove_action( 'pods_meta_init', [ $this, 'pods_meta_init' ] );

		remove_filter( 'pll_get_post_types', [ $this, 'pll_get_post_types' ], 10 );
		remove_filter( 'pods_api_get_table_info', [ $this, 'pods_api_get_table_info' ], 10 );
		remove_filter( 'pods_data_traverse_recurse_ignore_aliases', [ $this, 'pods_data_traverse_recurse_ignore_aliases' ], 10 );
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
	 * @param \PodsMeta $pods_meta
	 *
	 * @since 2.8.0
	 */
	public function pods_meta_init( $pods_meta ) {

		if ( function_exists( 'pll_current_language' ) ) {
			add_action( 'init', array( $pods_meta, 'cache_pods' ), 101, 0 );
		}
	}

	/**
	 * @since 2.8.0
	 * @param array $ignore_aliases
	 * @return array
	 */
	public function pods_data_traverse_recurse_ignore_aliases( $ignore_aliases ) {

		$ignore_aliases[] = 'polylang_languages';

		return $ignore_aliases;
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
		global $wpdb;
		$object_name = pods_sanitize( ( empty( $object ) ? $name : $object ) );

		// Get current language data
		$lang_data = pods_i18n()->get_current_language_data();

		$current_language_tt_id    = 0;
		$current_language_tl_tt_id = 0;

		if ( $lang_data ) {
			if ( ! empty( $lang_data['tt_id'] ) ) {
				$current_language_tt_id = $lang_data['tt_id'];
			}
			if ( ! empty( $lang_data['tl_tt_id'] ) ) {
				$current_language_tl_tt_id = $lang_data['tl_tt_id'];
			}
		}

		switch ( $object_type ) {

			case 'post':
			case 'post_type':
				if ( $this->is_translated_post_type( $object_name ) ) {
					$info['join']['polylang_languages'] = "
						LEFT JOIN `{$wpdb->term_relationships}` AS `polylang_languages`
							ON `polylang_languages`.`object_id` = `t`.`ID`
								AND `polylang_languages`.`term_taxonomy_id` = {$current_language_tt_id}
					";

					$info['where']['polylang_languages'] = "`polylang_languages`.`object_id` IS NOT NULL";
				}
				break;

			case 'taxonomy':
				if ( $this->is_translated_taxonomy( $object_name ) ) {
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
}
