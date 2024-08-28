<?php

namespace Pods\Integrations\Query_Monitor\Collectors;

use QM_DataCollector;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Constants
 *
 * @since 3.2.7
 */
class Constants extends QM_DataCollector {

	public $id = 'pods-constants';

	public function process() {
		$defined_constants = [];
		$undefined_constants = [];

		$constants = [
			'PODS_ACCESS_HIDE_NOTICES',
			'PODS_ALLOW_FULL_META',
			'PODS_API_CACHE',
			'PODS_COMPATIBILITY',
			'PODS_DB_VERSION',
			'PODS_DEBUG_LOGGING',
			'PODS_DEPRECATED',
			'PODS_DEVELOPER',
			'PODS_DIR',
			'PODS_DISABLE_ADMIN_MENU',
			'PODS_DISABLE_CONTENT_MENU',
			'PODS_DISABLE_DYNAMIC_TEMPLATE',
			'PODS_DISABLE_EVAL',
			'PODS_DISABLE_FILE_BROWSER',
			'PODS_DISABLE_FILE_UPLOAD',
			'PODS_DISABLE_META',
			'PODS_DISABLE_META_BODY_CLASSES',
			'PODS_DISABLE_POD_PAGE_CHECK',
			'PODS_DISABLE_SHORTCODE',
			'PODS_DISABLE_SHORTCODE_SQL',
			'PODS_DISABLE_VERSION_OUTPUT',
			'PODS_DISPLAY_CALLBACKS',
			'PODS_DISPLAY_CALLBACKS_ALLOWED',
			'PODS_DYNAMIC_FEATURES_ALLOW',
			'PODS_DYNAMIC_FEATURES_ALLOW_SQL_CLAUSES',
			'PODS_DYNAMIC_FEATURES_ENABLED',
			'PODS_DYNAMIC_FEATURES_RESTRICT',
			'PODS_DYNAMIC_FEATURES_RESTRICTED',
			'PODS_DYNAMIC_FEATURES_RESTRICTED_FORMS',
			'PODS_FIELD_STRICT',
			'PODS_FILE',
			'PODS_FILE_DIRECTORY',
			'PODS_FILES_REQUIRE_LOGIN',
			'PODS_GLOBAL_POD_PAGINATION',
			'PODS_GLOBAL_POD_SEARCH',
			'PODS_GLOBAL_POD_SEARCH_MODE',
			'PODS_LIGHT',
			'PODS_MEDIA',
			'PODS_META_TYPES_ONLY',
			'PODS_MYSQL_VERSION_MINIMUM',
			'PODS_PHP_VERSION_MINIMUM',
			'PODS_PRELOAD_CONFIG_AFTER_FLUSH',
			'PODS_REMOTE_VIEWS',
			'PODS_SESSION_AUTO_START',
			'PODS_SHORTCODE_ALLOW_BLOG_SWITCHING',
			'PODS_SHORTCODE_ALLOW_EVALUATE_TAGS',
			'PODS_SHORTCODE_ALLOW_SUB_SHORTCODES',
			'PODS_SHORTCODE_ALLOW_USER_EDIT',
			'PODS_SHORTCODE_ALLOW_VIEWS',
			'PODS_SLUG',
			'PODS_STATS_TRACKING',
			'PODS_STRICT',
			'PODS_STRICT_MODE',
			'PODS_TABLELESS',
			'PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES',
			'PODS_TEXTDOMAIN',
			'PODS_UPLOAD_REQUIRE_LOGIN',
			'PODS_URL',
			'PODS_VERSION',
			'PODS_WP_VERSION_MINIMUM',
		];

		$undefined_text = __( 'undefined', 'pods' );

		foreach ( $constants as $constant ) {
			if ( defined( $constant ) ) {
				$constant_value = constant( $constant );

				if ( is_bool( $constant_value ) ) {
					$constant_value = parent::format_bool_constant( $constant );
				} elseif ( ! is_scalar( $constant_value ) && null !== $constant_value ) {
					$constant_value = json_encode( $constant_value, JSON_PRETTY_PRINT );
				} else {
					$constant_value = (string) $constant_value;
				}

				$defined_constants[ $constant ] = $constant_value;
			} else {
				$undefined_constants[ $constant ] = $undefined_text;
			}
		}

		$data = array_merge( $defined_constants, $undefined_constants );

		$this->data['constants'] = $data;
	}
}
