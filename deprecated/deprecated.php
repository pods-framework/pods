<?php
/**
 * Mapping function to new function name (following normalization of function names from pod_ to pods_)
 *
 * @since 1.x
 * @deprecated deprecated since version 2.0.0
 */
function pod_query ($sql, $error = 'SQL failed', $results_error = null, $no_results_error = null) {
    pods_deprecated('pod_query', '2.0.0', 'pods_query');
    global $wpdb;
    $result = pods_query($sql, $error, $results_error, $no_results_error);
    return $wpdb->result;
}

/**
 * Include and Init the Pods class
 *
 * @since 1.x
 * @deprecated deprecated since version 2.0.0
 */
class Pod
{
    function __construct ($type = null, $id = null) {
        pods_deprecated('Pod (class)', '2.0.0', 'pods (function)');
        return pods($type, $id);
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
    function __construct () {
        pods_deprecated('PodAPI (class)', '2.0.0', 'pods_api (function)');
        return pods_api();
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
 * @param string $type (optional) "uri", "get", "post", "cookie", or "session"
 * @return string The requested value, or null
 * @since 1.6.2
 * @deprecated deprecated since version 2.0.0
 */
function pods_url_variable ($key = 'last', $type = 'uri') {
    pods_deprecated('pods_url_variable', '2.0.0', 'pods_deprecated');
    return pods_var($key, $type);
}