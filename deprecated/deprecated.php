<?php
// JSON support
if (!function_exists('json_encode')) {
    require_once(ABSPATH . '/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php');

    function json_encode ($str) {
        $json = new Moxiecode_JSON();
        return $json->encode($str);
    }

    function json_decode ($str) {
        $json = new Moxiecode_JSON();
        return $json->decode($str);
    }
}

// Get Called Class
if (!function_exists('get_called_class')) {
    function get_called_class () {
        $bt = debug_backtrace();
        $l = 0;
        do {
            $l++;
            $lines = file($bt[$l]['file']);
            $callerLine = $lines[$bt[$l]['line'] - 1];
            preg_match('/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/',
                       $callerLine,
                       $matches);

            if ($matches[1] == 'self') {
                $line = $bt[$l]['line'] - 1;
                while ($line > 0 && strpos($lines[$line], 'class') === false) {
                    $line--;
                }
                preg_match('/class[\s]+(.+?)[\s]+/si', $lines[$line], $matches);
            }
        }
        while ($matches[1] == 'parent' && $matches[1]);
        return $matches[1];
    }
}

/**
 * Mapping function to new function name (following normalization of function names from pod_ to pods_)
 *
 * @since 1.x
 * @deprecated deprecated since version 2.0.0
 */
function pod_query ($sql, $error = 'SQL failed', $results_error = null, $no_results_error = null) {
    pods_deprecated('pod_query', '2.0.0', 'pods_query');
    global $wpdb;

    $sql = trim($sql);
    // Using @wp_users is deprecated! use $wpdb->users instead!
    $sql = str_replace('@wp_users', $wpdb->users, $sql);
    $sql = str_replace('@wp_', $wpdb->prefix, $sql);
    $sql = str_replace('{prefix}', '@wp_', $sql);

    $sql = apply_filters('pod_query', $sql, $error, $results_error, $no_results_error);

    // Return cached resultset
    if ('SELECT' == substr($sql, 0, 6)) {
        $cache = PodCache::instance();
        if ($cache->cache_enabled && isset($cache->results[$sql])) {
            $result = $cache->results[$sql];
            if (0 < mysql_num_rows($result)) {
                mysql_data_seek($result, 0);
            }
            $result = apply_filters('pod_query_return', $result, $sql, $error, $results_error, $no_results_error);
            return $result;
        }
    }
    if (false !== $error)
        $result = mysql_query($sql, $wpdb->dbh) or die("<e>$error; SQL: $sql; Response: " . mysql_error($wpdb->dbh));
    else
        $result = @mysql_query($sql, $wpdb->dbh);

    if (0 < @mysql_num_rows($result)) {
        if (!empty($results_error)) {
            die("<e>$results_error");
        }
    }
    elseif (!empty($no_results_error)) {
        die("<e>$no_results_error");
    }

    if ('INSERT' == substr($sql, 0, 6)) {
        $result = mysql_insert_id($wpdb->dbh);
    }
    elseif ('SELECT' == substr($sql, 0, 6)) {
        if ('SELECT FOUND_ROWS()' != $sql) {
            $cache->results[$sql] = $result;
        }
    }
    $result = apply_filters('pod_query_return', $result, $sql, $error, $results_error, $no_results_error);
    return $result;
}

/**
 * Include and Init the Pods class
 *
 * @since 1.x
 * @deprecated deprecated since version 2.0.0
 */
class Pod
{
    private $deprecated;
    private $new;

    function __construct ($type = null, $id = null) {
        pods_deprecated('Pod (class)', '2.0.0', 'pods (function)');
    }

    /**
     * Handle variables that have been deprecated
     *
     * @since 2.0.0
     */
    public function __get ( $name ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/Pods.php' );
            $this->deprecated = new Pods_Deprecated( $this );
        }

        $var = null;

        if ( isset( $this->deprecated->{$name} ) ) {
            pods_deprecated( "Pod->{$name}", '2.0.0' );

            $var = $this->deprecated->{$name};
        }
        else {
            if ( !isset( $this->new ) )
                $this->new = pods();

            if ( isset( $this->new->{$name} ) )
                $var = $this->new->{$name};
            else
                pods_deprecated( "Pod->{$name}", '2.0.0' );
        }

        return $var;
    }

    /**
     * Handle methods that have been deprecated
     *
     * @since 2.0.0
     */
    public function __call ( $name, $args ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/Pods.php' );
            $this->deprecated = new Pods_Deprecated( $this );
        }

        if ( method_exists( $this->deprecated, $name ) )
            return call_user_func_array( array( $this->deprecated, $name ), $args );
        else {
            if ( !isset( $this->new ) )
                $this->new = pods();

            if ( method_exists( $this->new, $name ) )
                return call_user_func_array( array( $this->new, $name ), $args );
            else
                pods_deprecated( "Pod::{$name}", '2.0.0' );
        }
    }
}

/**
 * Include and Init the PodsAPI class
 *
 * @since 1.x
 * @deprecated deprecated since version 2.0.0
 */
class PodAPI
{
    private $deprecated;
    private $new;

    function __construct ( $type = null, $id = null ) {
        pods_deprecated( 'PodAPI (class)', '2.0.0', 'pods_api (function)' );
    }

    /**
     * Handle variables that have been deprecated
     *
     * @since 2.0.0
     */
    public function __get ( $name ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/PodsAPI.php' );
            $this->deprecated = new PodsAPI_Deprecated( $this );
        }

        $var = null;

        if ( isset( $this->deprecated->{$name} ) ) {
            pods_deprecated( "PodAPI->{$name}", '2.0.0' );

            $var = $this->deprecated->{$name};
        }
        else {
            if ( !isset( $this->new ) )
                $this->new = pods_api();

            if ( isset( $this->new->{$name} ) )
                $var = $this->new->{$name};
            else
                pods_deprecated( "PodAPI->{$name}", '2.0.0' );
        }

        return $var;
    }

    /**
     * Handle methods that have been deprecated
     *
     * @since 2.0.0
     */
    public function __call ( $name, $args ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/PodsAPI.php' );
            $this->deprecated = new PodsAPI_Deprecated( $this );
        }

        if ( method_exists( $this->deprecated, $name ) )
            return call_user_func_array( array( $this->deprecated, $name ), $args );
        else {
            if ( !isset( $this->new ) )
                $this->new = pods_api();

            if ( method_exists( $this->new, $name ) )
                return call_user_func_array( array( $this->new, $name ), $args );
            else
                pods_deprecated( "PodAPI::{$name}", '2.0.0' );
        }
    }
}

/**
 * Include and Init the PodsUI class
 *
 * @since 2.0.0
 * @deprecated deprecated since version 2.0.0
 */
function pods_ui_manage ($obj) {
    pods_deprecated('pods_ui_manage', '2.0.0', 'pods_ui');

    return pods_ui($obj);
}


/**
 * Limit Access based on Field Value
 *
 * @since 1.x
 * @deprecated deprecated since version 2.0.0
 */
function pods_ui_access ($object, $access, $what) {
    pods_deprecated('pods_ui_access', '2.0.0');
    if (is_array($access)) {
        foreach ($access as $field => $match) {
            if (is_array($match)) {
                $okay = false;
                foreach ($match as $the_field => $the_match) {
                    if ($object->get_field($the_field) == $the_match)
                        $okay = true;
                }
                if (false === $okay)
                    return false;
            }
            elseif ($object->get_field($field) != $match)
                return false;
        }
    }
    return true;
}

/**
 * Return a GET, POST, COOKIE, SESSION, or URI string segment
 *
 * @param mixed $key The variable name or URI segment position
 * @param string $type (optional) "uri", "get", "post", "request", "server", "session", or "cookie"
 * @return string The requested value, or null
 * @since 1.6.2
 * @deprecated deprecated since version 2.0.0
 */
function pods_url_variable ($key = 'last', $type = 'url') {
    $output = apply_filters('pods_url_variable', pods_var($key, $type), $key, $type);
    return $output;
}