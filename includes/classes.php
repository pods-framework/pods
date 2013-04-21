<?php
/**
 * @package Pods\Global\Functions\Classes
 */
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
function pods ( $type = null, $id = null, $strict = false ) {
    require_once( PODS_DIR . 'classes/Pods.php' );

    $pod = null;

    if ( empty( $pod ) ) {
        $pod = new Pods( $type, $id );

        if ( true === $strict && null !== $type && !$pod->valid() )
            return false;
    }

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

    return new PodsAPI( $pod, $format );

    /* @todo instance caching
    if ( is_object( $pod ) )
        $api = new PodsAPI( $pod, $format );
    else {
        $identifier = (string) $pod . (string) $format;

        if ( !isset( $GLOBALS[ 'pods_class_cache' ] ) )
            $GLOBALS[ 'pods_class_cache' ] = array();

        if ( !isset( $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ] ) )
            $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ] = array();

        if ( isset( $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ][ $identifier ] ) && is_object( $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ][ $identifier ] ) )
            $api =& $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ][ $identifier ];
        else {
            $api = new PodsAPI( $pod, $format );

            if ( !isset( $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ][ $identifier ] ) )
                $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ][ $identifier ] = 1;
            else
                $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ][ $identifier ]++;

            // Start caching if the calls become excessive (calling the same reference more than once)
            if ( 1 < $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ][ $identifier ] )
                $GLOBALS[ 'pods_class_cache' ][ 'pods_api' ][ $identifier ] =& $api;
        }
    }

    return $api;*/
}

/**
 * Include and Init the PodsData class
 *
 * @see PodsData
 *
 * @param string|\Pod $pod The pod object to load
 * @param int $id (optional) Id of the pod to fetch
 * @param bool $strict (optional) If true throw an error if pod does not exist
 *
 * @return PodsData
 *
 * @since 2.0
 */
function pods_data ( $pod = null, $id = null, $strict = true ) {
    require_once( PODS_DIR . 'classes/PodsData.php' );

    return new PodsData( $pod, $id );

    /* @todo instance caching
    if ( is_object( $pod ) )
        $data = new PodsData( $pod, $id );
    else {
        $identifier = (string) $pod . (string) $id . (string) $strict;

        if ( !isset( $GLOBALS[ 'pods_class_cache' ] ) )
            $GLOBALS[ 'pods_class_cache' ] = array();

        if ( !isset( $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ] ) )
            $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ] = array();

        if ( isset( $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ][ $identifier ] ) && is_object( $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ][ $identifier ] ) )
            $data =& $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ][ $identifier ];
        else {
            $data = new PodsData( $pod, $id );

            if ( !isset( $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ][ $identifier ] ) )
                $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ][ $identifier ] = 1;
            else
                $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ][ $identifier ]++;

            // Start caching if the calls become excessive (calling the same reference more than once)
            if ( 1 < $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ][ $identifier ] )
                $GLOBALS[ 'pods_class_cache' ][ 'pods_data' ][ $identifier ] =& $data;
        }
    }

    return $data;*/
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

    return PodsForm::instance();
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

    return new PodsInit();
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

    return new PodsComponents();
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

    return new PodsAdmin();
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

    return new PodsMeta();
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