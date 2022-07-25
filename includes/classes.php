<?php
/**
 * @package  Pods
 * @category Utilities
 */

use Pods\Whatsit\Pod;

/**
 * Include and Init the Pods class
 *
 * @see   Pods
 *
 * @param string $type   The pod name
 * @param mixed  $id     (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
 * @param bool   $strict (optional) If set to true, return false instead of an object if the Pod doesn't exist
 *
 * @return bool|\Pods returns false if $strict, WP_DEBUG, PODS_STRICT or (PODS_DEPRECATED && PODS_STRICT_MODE) are true
 * @since 2.0.0
 * @link  https://docs.pods.io/code/pods/
 */
function pods( $type = null, $id = null, $strict = null ) {
	$pod = new Pods( $type, $id );

	if ( null === $strict ) {
		$strict = pods_strict();
	}

	if ( true === $strict && null !== $type && ! $pod->valid() ) {
		return false;
	}

	return $pod;
}

/**
 * Easily create content admin screens with in-depth customization. This is the primary interface function that Pods
 * runs off of. It's also the only function required to be run in order to have a fully functional Manage interface.
 *
 * @see   PodsUI
 *
 * @param array|string|Pods $obj        (optional) Configuration options for the UI
 * @param boolean           $deprecated (optional) Whether to enable deprecated options (used by pods_ui_manage)
 *
 * @return PodsUI
 *
 * @since 2.0.0
 * @link  https://docs.pods.io/code/pods-ui/
 */
function pods_ui( $obj, $deprecated = false ) {
	return new PodsUI( $obj, $deprecated );
}

/**
 * Include and get the PodsAPI object, for use with all calls that Pods makes for add, save, delete, and more.
 *
 * @see   PodsAPI
 *
 * @param string $pod    (optional) (deprecated) The Pod name
 * @param string $format (optional) (deprecated) Format used in import() and export()
 *
 * @return PodsAPI
 *
 * @since 2.0.0
 * @link  https://docs.pods.io/code/pods-api/
 */
function pods_api( $pod = null, $format = null ) {
	return PodsAPI::init( $pod, $format );
}

/**
 * Include and Init the PodsData class.
 *
 * @see   PodsData
 *
 * @param string|Pod      $pod    The pod object to load.
 * @param null|null|string $id     (optional) Id of the pod to fetch.
 * @param bool             $strict (optional) If true throw an error if the pod does not exist.
 * @param bool             $unique (optional) If true always return a unique class.
 *
 * @return PodsData
 *
 * @since 2.0.0
 *
 * @throws Exception
 */
function pods_data( $pod = null, $id = null, $strict = true, $unique = true ) {
	if ( $unique ) {
		if ( $pod instanceof Pod || $pod instanceof Pods ) {
			return new PodsData( $pod, $id, $strict );
		}

		if ( ! in_array( $pod, array( null, false ), true ) ) {
			return new PodsData( $pod, $id, $strict );
		}

		return new PodsData;
	}

	return PodsData::init( $pod, $id, $strict );
}

/**
 * Include and Init the PodsFormUI class
 *
 * @see   PodsForm
 *
 * @return PodsForm
 *
 * @since 2.0.0
 */
function pods_form() {
	return PodsForm::init();
}

/**
 * Include and Init the Pods class
 *
 * @see   PodsInit
 *
 * @return PodsInit
 *
 * @since 2.0.0
 */
function pods_init() {
	return PodsInit::init();
}

/**
 * Include and Init the Pods Components class
 *
 * @see   PodsComponents
 *
 * @return PodsComponents
 *
 * @since 2.0.0
 */
function pods_components() {
	return PodsComponents::init();
}

/**
 * Include and Init the PodsAdmin class
 *
 * @see   PodsAdmin
 *
 * @return PodsAdmin
 *
 * @since 2.0.0
 */
function pods_admin() {
	return PodsAdmin::init();
}

/**
 * Include and Init the PodsMeta class
 *
 * @see   PodsMeta
 *
 * @return PodsMeta
 *
 * @since 2.0.0
 */
function pods_meta() {
	return PodsMeta::init();
}

/**
 * Include and Init the PodsArray class
 *
 * @see   PodsArray
 *
 * @param mixed $container Object (or existing Array)
 *
 * @return PodsArray
 *
 * @since 2.0.0
 */
function pods_array( $container ) {
	return new PodsArray( $container );
}

/**
 * @since 2.7.0
 */
function pods_i18n() {
	return PodsI18n::get_instance();
}

/**
 * Include a file that's child/parent theme-aware, and can be cached into object cache or transients
 *
 * @see   PodsView::view
 *
 * @param string     $view       Path of the file to be included, this is relative to the current theme
 * @param array|null $data       (optional) Data to pass on to the template, using variable => value format
 * @param int|bool   $expires    (optional) Time in seconds for the cache to expire, if false caching is disabled.
 * @param string     $cache_mode (optional) Specify the caching method to use for the view, available options include
 *                               cache, transient, or site-transient
 * @param bool       $return     (optional) Whether to return the view or not, defaults to false and will echo it
 *
 * @return string|bool The view output
 *
 * @since 2.0.0
 * @link  https://docs.pods.io/code/pods-view/
 */
function pods_view( $view, $data = null, $expires = false, $cache_mode = 'cache', $return = false ) {
	$view = PodsView::view( $view, $data, $expires, $cache_mode );

	if ( $return ) {
		return $view;
	}

	echo $view;
}

/**
 * Include and Init the PodsMigrate class
 *
 * @see   PodsMigrate
 *
 * @param string $type      Export Type (php, json, sv, xml)
 * @param string $delimiter Delimiter for export type 'sv'
 * @param array  $data      Array of data
 *
 * @return PodsMigrate
 *
 * @since 2.2.0
 */
function pods_migrate( $type = null, $delimiter = null, $data = null ) {
	return new PodsMigrate( $type, $delimiter, $data );
}

/**
 * Include and Init the PodsUpgrade class
 *
 * @param string $version Version number of upgrade to get
 *
 * @see   PodsUpgrade
 *
 * @return PodsUpgrade
 *
 * @since 2.1.0
 */
function pods_upgrade( $version = '' ) {
	include_once PODS_DIR . 'sql/upgrade/PodsUpgrade.php';

	$class_name = str_replace( '.', '_', $version );
	$class_name = "PodsUpgrade_{$class_name}";

	$class_name = trim( $class_name, '_' );

	if ( ! class_exists( $class_name ) ) {
		$file = PODS_DIR . 'sql/upgrade/' . basename( $class_name ) . '.php';

		if ( file_exists( $file ) ) {
			include_once $file;
		}
	}

	$class = false;

	if ( class_exists( $class_name ) ) {
		$class = new $class_name();
	}

	return $class;
}
