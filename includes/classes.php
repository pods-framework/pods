<?php
/**
 * @package Pods\Global\Functions\Classes
 */
/**
 * Include and Init the PodsObject class
 *
 * @param string|array|WP_Post $name Get the Object by Name, or pass an array/WP_Post of Object
 * @param int $id Get the Object by ID (overrides $name)
 * @param bool $live Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed $parent Parent Object or ID
 *
 * @return PodsObject|null
 *
 * @since 2.3.10
 */
function pods_object( $name = null, $id = 0, $live = false, $parent = null ) {
    require_once( PODS_DIR . 'classes/PodsObject.php' );

	if ( null !== $name || 0 !== $id || false !== $live || null !== $parent ) {
    	return new PodsObject( $name, $id, $live, $parent );
	}

	return null;
}

/**
 * Include and Init the PodsObject_Pod class
 *
 * @param string|array|WP_Post $name Get the Object by Name, or pass an array/WP_Post of Object
 * @param int $id Get the Object by ID (overrides $name)
 * @param bool $live Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed $parent Parent Object or ID
 *
 * @return PodsObject_Pod|null
 *
 * @since 2.3.10
 */
function pods_object_pod( $name = null, $id = 0, $live = false, $parent = null ) {
    require_once( PODS_DIR . 'classes/PodsObject.php' );
    require_once( PODS_DIR . 'classes/PodsObject_Pod.php' );

	if ( null !== $name || 0 !== $id || false !== $live || null !== $parent ) {
    	return new PodsObject_Pod( $name, $id, $live, $parent );
	}

	return null;
}

/**
 * Include and Init the PodsObject_Field class
 *
 * @param string|array|WP_Post $name Get the Object by Name, or pass an array/WP_Post of Object
 * @param int $id Get the Object by ID (overrides $name)
 * @param bool $live Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed $parent Parent Object or ID
 *
 * @return PodsObject_Field|null
 *
 * @since 2.3.10
 */
function pods_object_field( $name = null, $id = 0, $live = false, $parent = null ) {
    require_once( PODS_DIR . 'classes/PodsObject.php' );
    require_once( PODS_DIR . 'classes/PodsObject_Field.php' );

	if ( null !== $name || 0 !== $id || false !== $live || null !== $parent ) {
    	return new PodsObject_Field( $name, $id, $live, $parent );
	}

	return null;
}

/**
 * Include and Init the PodsObject_Group class
 *
 * @param string|array|WP_Post $name Get the Object by Name, or pass an array/WP_Post of Object
 * @param int $id Get the Object by ID (overrides $name)
 * @param bool $live Set to true to automatically save values in the DB when you $object['option']='value'
 * @param mixed $parent Parent Object or ID
 *
 * @return PodsObject_Group|null
 *
 * @since 2.3.10
 */
function pods_object_group( $name = null, $id = 0, $live = false, $parent = null ) {
    require_once( PODS_DIR . 'classes/PodsObject.php' );
    require_once( PODS_DIR . 'classes/PodsObject_Group.php' );

	if ( null !== $name || 0 !== $id || false !== $live || null !== $parent ) {
    	return new PodsObject_Group( $name, $id, $live, $parent );
	}

	return null;
}

/**
 * Include and Init the Pods class
 *
 * @see Pods
 *
 * @param string $type The pod name
 * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
 * @param bool $strict (optional) If set to true, return false instead of an object if the Pod doesn't exist
 *
 * @return bool|\Pods
 * @since 2.0
 * @link http://pods.io/docs/pods/
 */
function pods ( $type = null, $id = null, $strict = null ) {
    require_once( PODS_DIR . 'classes/Pods.php' );

    $pod = new Pods( $type, $id );

    if ( null === $strict )
        $strict = pods_strict();

    if ( true === $strict && null !== $type && !$pod->valid() )
        return false;

    return $pod;
}

/**
 * Easily create content admin screens with in-depth customization. This is the primary interface function that Pods
 * runs off of. It's also the only function required to be run in order to have a fully functional Manage interface.
 *
 * @see PodsUI
 *
 * @param array|string|Pods $obj (optional) Configuration options for the UI
 * @param boolean $deprecated (optional) Whether to enable deprecated options (used by pods_ui_manage)
 *
 * @return PodsUI
 *
 * @since 2.0
 * @link http://pods.io/docs/pods-ui/
 */
function pods_ui ( $obj, $deprecated = false ) {
    require_once( PODS_DIR . 'classes/PodsUI.php' );

    return new PodsUI( $obj, $deprecated );
}

/**
 * Include and get the PodsAPI object, for use with all calls that Pods makes for add, save, delete, and more.
 *
 * @see PodsAPI
 *
 * @param string $pod (optional) (deprecated) The Pod name
 * @param string $format (optional) (deprecated) Format used in import() and export()
 *
 * @return PodsAPI
 *
 * @since 2.0
 * @link http://pods.io/docs/pods-api/
 */
function pods_api ( $pod = null, $format = null ) {
    require_once( PODS_DIR . 'classes/PodsAPI.php' );

    return PodsAPI::init( $pod, $format );
}

/**
 * Include and Init the PodsData class
 *
 * @see PodsData
 *
 * @param string|\Pod $pod The pod object to load
 * @param int $id (optional) Id of the pod to fetch
 * @param bool $strict (optional) If true throw an error if the pod does not exist
 * @param bool $unique (optional) If true always return a unique class
 *
 * @return PodsData
 *
 * @since 2.0
 */
function pods_data ( $pod = null, $id = null, $strict = true, $unique = true ) {
    require_once( PODS_DIR . 'classes/PodsData.php' );

    if ( $unique && false !== $pod )
        return new PodsData( $pod, $id, $strict );

    return PodsData::init( $pod, $id, $strict );
}

/**
 * Include and Init the PodsFormUI class
 *
 * @see PodsForm
 *
 * @return PodsForm
 *
 * @since 2.0
 */
function pods_form () {
    require_once( PODS_DIR . 'classes/PodsForm.php' );

    return PodsForm::init();
}

/**
 * Include and Init the Pods class
 *
 * @see PodsInit
 *
 * @return PodsInit
 *
 * @since 2.0
 */
function pods_init () {
    require_once( PODS_DIR . 'classes/PodsInit.php' );

    return PodsInit::init();
}

/**
 * Include and Init the Pods Components class
 *
 * @see PodsComponents
 *
 * @return PodsComponents
 *
 * @since 2.0
 */
function pods_components () {
    require_once( PODS_DIR . 'classes/PodsComponents.php' );
    require_once( PODS_DIR . 'classes/PodsComponent.php' );

    return PodsComponents::init();
}

/**
 * Include and Init the PodsAdmin class
 *
 * @see PodsAdmin
 *
 * @return PodsAdmin
 *
 * @since 2.0
 */
function pods_admin () {
    require_once( PODS_DIR . 'classes/PodsAdmin.php' );

    return PodsAdmin::init();
}

/**
 * Include and Init the PodsMeta class
 *
 * @see PodsMeta
 *
 * @return PodsMeta
 *
 * @since 2.0
 */
function pods_meta () {
    require_once( PODS_DIR . 'classes/PodsMeta.php' );

    return PodsMeta::init();
}

/**
 * Include and Init the PodsArray class
 *
 * @see PodsArray
 *
 * @param mixed $container Object (or existing Array)
 *
 * @return PodsArray
 *
 * @since 2.0
 */
function pods_array ( $container ) {
    require_once( PODS_DIR . 'classes/PodsArray.php' );

    return new PodsArray( $container );
}

/**
 * Include a file that's child/parent theme-aware, and can be cached into object cache or transients
 *
 * @see PodsView::view
 *
 * @param string $view Path of the file to be included, this is relative to the current theme
 * @param array|null $data (optional) Data to pass on to the template, using variable => value format
 * @param int|bool $expires (optional) Time in seconds for the cache to expire, if false caching is disabled.
 * @param string $cache_mode (optional) Specify the caching method to use for the view, available options include cache, transient, or site-transient
 * @param bool $return (optional) Whether to return the view or not, defaults to false and will echo it
 *
 * @return string|bool The view output
 *
 * @since 2.0
 * @link http://pods.io/docs/pods-view/
 */
function pods_view ( $view, $data = null, $expires = false, $cache_mode = 'cache', $return = false ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    $view = PodsView::view( $view, $data, $expires, $cache_mode );

    if ( $return )
        return $view;

    echo $view;
}

/**
 * Include and Init the PodsMigrate class
 *
 * @see PodsMigrate
 *
 * @param string $type Export Type (php, json, sv, xml)
 * @param string $delimiter Delimiter for export type 'sv'
 * @param array $data Array of data
 *
 * @return PodsMigrate
 *
 * @since 2.2
 */
function pods_migrate ( $type = null, $delimiter = null, $data = null ) {
    require_once( PODS_DIR . 'classes/PodsMigrate.php' );

    return new PodsMigrate( $type, $delimiter, $data );
}

/**
 * Include and Init the PodsUpgrade class
 *
 * @param string $version Version number of upgrade to get
 *
 * @see PodsUpgrade
 *
 * @return PodsUpgrade
 *
 * @since 2.1
 */
function pods_upgrade ( $version = '' ) {
    include_once PODS_DIR . 'sql/upgrade/PodsUpgrade.php';

    $class_name = str_replace( '.', '_', $version );
    $class_name = "PodsUpgrade_{$class_name}";

    $class_name = trim( $class_name, '_' );

    if ( !class_exists( $class_name ) ) {
        $file = PODS_DIR . 'sql/upgrade/' . basename( $class_name ) . '.php';

        if ( file_exists( $file ) )
            include_once $file;
    }

    $class = false;

    if ( class_exists( $class_name ) )
        $class = new $class_name();

    return $class;
}