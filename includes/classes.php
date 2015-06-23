<?php
/**
 * @package Pods
 * @category Utilities
 */

/**
 * Include and Init the PodsObject class
 *
 * @param string|array|WP_Post $name   Get the Object by Name, or pass an array/WP_Post of Object
 * @param int                  $id     Get the Object by ID (overrides $name)
 * @param bool                 $live   Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed                $parent Parent Object or ID
 *
 * @return Pods_Object|null
 *
 * @since 2.3.10
 */
function pods_object( $name = null, $id = 0, $live = false, $parent = null ) {

	if ( false === $name ) {
		return null;
	}

	return new Pods_Object( $name, $id, $live, $parent );

}

/**
 * Include and Init the PodsObject_Pod class
 *
 * @param string|array|WP_Post $name   Get the Object by Name, or pass an array/WP_Post of Object
 * @param int                  $id     Get the Object by ID (overrides $name)
 * @param bool                 $live   Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed                $parent Parent Object or ID
 *
 * @return Pods_Object_Pod|null
 *
 * @since 2.3.10
 */
function pods_object_pod( $name = null, $id = 0, $live = false, $parent = null ) {

	if ( false === $name ) {
		return null;
	}

	return new Pods_Object_Pod( $name, $id, $live, $parent );

}

/**
 * Include and Init the PodsObject_Field class
 *
 * @param string|array|WP_Post $name   Get the Object by Name, or pass an array/WP_Post of Object
 * @param int                  $id     Get the Object by ID (overrides $name)
 * @param bool                 $live   Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed                $parent Parent Object or ID
 *
 * @return Pods_Object_Field|null
 *
 * @since 2.3.10
 */
function pods_object_field( $name = null, $id = 0, $live = false, $parent = null ) {

	if ( false === $name ) {
		return null;
	}

	return new Pods_Object_Field( $name, $id, $live, $parent );

}

/**
 * Include and Init the PodsObject_Group class
 *
 * @param string|array|WP_Post $name   Get the Object by Name, or pass an array/WP_Post of Object
 * @param int                  $id     Get the Object by ID (overrides $name)
 * @param bool                 $live   Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed                $parent Parent Object or ID
 *
 * @return Pods_Object_Group|null
 *
 * @since 2.3.10
 */
function pods_object_group( $name = null, $id = 0, $live = false, $parent = null ) {

	if ( false === $name ) {
		return null;
	}

	return new Pods_Object_Group( $name, $id, $live, $parent );

}

/**
 * Include and Init the PodsObject class based on the $object name passed
 *
 * @param string               $object Object name
 * @param string|array|WP_Post $name   Get the Object by Name, or pass an array/WP_Post of Object
 * @param int                  $id     Get the Object by ID (overrides $name)
 * @param bool                 $live   Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed                $parent Parent Object or ID
 *
 * @return Pods_Object_Pod|Pods_Object_Field|Pods_Object_Group|Pods_Object|null
 *
 * @since 3.0
 */
function pods_object_get( $object, $name = null, $id = 0, $live = false, $parent = null ) {

	if ( '_pods_pod' == $object ) {
		return pods_object_pod( $name, $id, $live, $parent );
	} elseif ( '_pods_field' == $object ) {
		return pods_object_field( $name, $id, $live, $parent );
	} elseif ( '_pods_group' == $object ) {
		return pods_object_group( $name, $id, $live, $parent );
	}

	return pods_object( $name, $id, $live, $parent );

}

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
 * @since 2.0
 * @link  http://pods.io/docs/pods/
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
 * @see   Pods_UI
 *
 * @param array|string|Pods $obj        (optional) Configuration options for the UI
 * @param boolean           $deprecated (optional) Whether to enable deprecated options (used by pods_ui_manage)
 *
 * @return Pods_UI
 *
 * @since 2.0
 * @link  http://pods.io/docs/pods-ui/
 */
function pods_ui( $obj, $deprecated = false ) {

	return new Pods_UI( $obj, $deprecated );
}

/**
 * Include and get the Pods_API object, for use with all calls that Pods makes for add, save, delete, and more.
 *
 * @see   Pods_API
 *
 * @param string $pod    (optional) (deprecated) The Pod name
 * @param string $format (optional) (deprecated) Format used in import() and export()
 *
 * @return Pods_API
 *
 * @since 2.0
 * @link  http://pods.io/docs/pods-api/
 */
function pods_api( $pod = null, $format = null ) {

	return Pods_API::init( $pod, $format );

}

/**
 * Include and Init the Pods_Data class
 *
 * @see   Pods_Data
 *
 * @param string|\Pod $pod    The pod object to load
 * @param int         $id     (optional) Id of the pod to fetch
 * @param bool        $strict (optional) If true throw an error if the pod does not exist
 * @param bool        $unique (optional) If true always return a unique class
 *
 * @return Pods_Data
 *
 * @since 2.0
 */
function pods_data( $pod = null, $id = null, $strict = true, $unique = true ) {

	if ( $unique && false !== $pod ) {
		return new Pods_Data( $pod, $id, $strict );
	}

	return Pods_Data::init( $pod, $id, $strict );

}

/**
 * Include and Init the Pods_FormUI class
 *
 * @see   Pods_Form
 *
 * @return Pods_Form
 *
 * @since 2.0
 */
function pods_form() {

	return Pods_Form::init();

}

/**
 * Include and Init the Pods class
 *
 * @see   Pods_Init
 *
 * @return Pods_Init
 *
 * @since 2.0
 */
function pods_init() {

	return Pods_Init::init();

}

/**
 * Include and Init the Pods Components class
 *
 * @see   Pods_Components
 *
 * @return Pods_Components
 *
 * @since 2.0
 */
function pods_components() {

	return Pods_Components::init();

}

/**
 * Include and Init the Pods_Admin class
 *
 * @see   Pods_Admin
 *
 * @return Pods_Admin
 *
 * @since 2.0
 */
function pods_admin() {

	return Pods_Admin::init();

}

/**
 * Include and Init the Pods_Meta class
 *
 * @see   Pods_Meta
 *
 * @return Pods_Meta
 *
 * @since 2.0
 */
function pods_meta() {

	return Pods_Meta::init();

}

/**
 * Include and Init the Pods_Array class
 *
 * @see   Pods_Array
 *
 * @param mixed $container Object (or existing Array)
 *
 * @return Pods_Array
 *
 * @since 2.0
 */
function pods_array( $container ) {

	return new Pods_Array( $container );

}

/**
 * @return Pods_Service_Container
 */
function pods_service( ) {

	return Pods_Service_Container::init();

}

/**
 * Include a file that's child/parent theme-aware, and can be cached into object cache or transients
 *
 * @see   Pods_View::view
 *
 * @param string     $view       Path of the file to be included, this is relative to the current theme
 * @param array|null $data       (optional) Data to pass on to the template, using variable => value format
 * @param int|bool   $expires    (optional) Time in seconds for the cache to expire, if false caching is disabled.
 * @param string     $cache_mode (optional) Specify the caching method to use for the view, available options include cache, transient, or site-transient
 * @param bool       $return     (optional) Whether to return the view or not, defaults to false and will echo it
 *
 * @return string|bool The view output
 *
 * @since 2.0
 * @link  http://pods.io/docs/pods-view/
 */
function pods_view( $view, $data = null, $expires = false, $cache_mode = 'cache', $return = false ) {

	$view = Pods_View::view( $view, $data, $expires, $cache_mode );

	if ( $return ) {
		return $view;
	}

	echo $view;

}

/**
 * Include and Init the Pods_Migrate class
 *
 * @see   Pods_Migrate
 *
 * @param string $type      Export Type (php, json, sv, xml)
 * @param string $delimiter Delimiter for export type 'sv'
 * @param array  $data      Array of data
 *
 * @return Pods_Migrate
 *
 * @since 2.2
 */
function pods_migrate( $type = null, $delimiter = null, $data = null ) {

	return new Pods_Migrate( $type, $delimiter, $data );

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
 * @since 2.1
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
