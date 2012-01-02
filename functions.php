<?php
/**
 * Standardize queries and error reporting
 *
 * @see PodsData::query
 *
 * @param string $query The SQL query
 * @param string $error (optional) The failure message
 * @param string $results_error (optional) Throw an error if a records are found
 * @param string $no_results_error (optional) Throw an error if no records are found
 * @since 2.0.0
 */
function pods_query ($sql, $error = 'Database Error', $results_error = null, $no_results_error = null) {
    $podsdata = pods_data();
    global $wpdb;
    $sql = str_replace('@wp_users', $wpdb->users, $sql);
    $sql = str_replace('@wp_', $wpdb->prefix, $sql);
    $sql = str_replace('{prefix}', '@wp_', $sql);
    return $podsdata->query($sql, $error, $results_error, $no_results_error);
}

/**
 * Standardize filters / actions
 *
 * @param string $scope Scope of the filter / action (ui for PodsUI, api for PodsAPI, etc..)
 * @param string $name Name of filter / action to run
 * @param mixed $args (optional) Arguments to send to filter / action
 * @param object $obj (optional) Object to reference for filter / action
 * @since 2.0.0
 * @to-do Need to figure out how to handle $scope = 'pods' for the Pods class
 */
function pods_do_hook ($scope, $name, $args = null, $obj = null) {
    $args = apply_filters("pods_{$scope}_{$name}", $args, &$obj);
    if (is_array($args) && isset($args[0]))
        return $args[0];
    return $args;
}

/**
 * Error Handling which throws / displays errors
 *
 * @param string $error The error message to be thrown / displayed
 * @param object / boolean $obj If object, if $obj->display_errors is set, and is set to true: display errors;
 *                              If boolean, and is set to true: display errors
 */
function pods_error ($error, &$obj = null) {
    $display_errors = false;
    if (is_object($obj) && isset($obj->display_errors) && true === $obj->display_errors)
        $display_errors = true;
    elseif (is_bool($obj) && true === $obj)
        $display_errors = true;
    // log error in WP
    $log_error = new WP_Error('pods-error-' . md5($error), $error);
    // throw error as Exception and return false if silent
    if (false !== $display_errors && !empty($error)) {
        throw new Exception($error);
        return false;
    }
    // die with error
    die("<e>$error</e>");
}

/**
 * Debug variable used in pods_debug to count the instances debug is used
 */
global $pods_debug;
$pods_debug = 0;
/**
 * Debugging common issues using this function saves a few lines and is compatible with
 *
 * @param mixed $debug The error message to be thrown / displayed
 * @param boolean $die If set to true, a die() will occur, if set to (int) 2 then a wp_die() will occur
 */
function pods_debug ($debug = '_null', $die = true) {
    global $pods_debug;
    $pods_debug++;
    echo "<e><pre>";
    ob_start();
    if ('_null' !== $debug)
        var_dump($debug);
    else
        var_dump('Pods Debug #' . $pods_debug);
    $debug = ob_get_clean() . '</pre>';
    if (2 === $die)
        wp_die($debug);
    elseif (true === $die)
        die($debug);
    echo $debug;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @uses do_action() Calls 'deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $function The function that was called
 * @param string $version The version of WordPress that deprecated the function
 * @param string $replacement Optional. The function that should have been called
 * @since 2.0.0
 */
function pods_deprecated ($function, $version, $replacement = null) {

    do_action('deprecated_function_run', $function, $replacement, $version);

    // Allow plugin to filter the output error trigger
    if (WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true)) {
        if (!is_null($replacement))
            trigger_error(sprintf(__('%1$s is <strong>deprecated</strong> since Pods version %2$s! Use %3$s instead.', 'pods'), $function, $version, $replacement));
        else
            trigger_error(sprintf(__('%1$s is <strong>deprecated</strong> since Pods version %2$s with no alternative available.', 'pods'), $function, $version));
    }
}

/**
 * Inline help
 *
 * @param string $text Help text
 * @since 2.0.0
 */
function pods_help ($text) {
    if (!wp_script_is('pods-qtip', 'registered'))
        wp_register_script('pods-qtip', PODS_URL . 'ui/js/jquery.qtip.min.js', array('jquery'), '2.0-2011-10-02');
    if (!wp_script_is('pods-qtip', 'queue') && !wp_script_is('pods-qtip', 'to_do') && !wp_script_is('pods-qtip', 'done'))
        wp_enqueue_script('pods-qtip');

    if (!wp_style_is('pods-qtip', 'registered'))
        wp_register_style('pods-qtip', PODS_URL . 'ui/css/jquery.qtip.min.css', array(), '2.0-2011-10-02');
    if (!wp_style_is('pods-qtip', 'queue') && !wp_style_is('pods-qtip', 'to_do') && !wp_style_is('pods-qtip', 'done'))
        wp_enqueue_style('pods-qtip');

    if (!wp_script_is('pods-qtip-init', 'registered'))
        wp_register_script('pods-qtip-init', PODS_URL . 'ui/js/qtip.js', array('jquery', 'pods-qtip'), PODS_VERSION);
    if (!wp_script_is('pods-qtip-init', 'queue') && !wp_script_is('pods-qtip-init', 'to_do') && !wp_script_is('pods-qtip-init', 'done'))
        wp_enqueue_script('pods-qtip-init');

    echo '<img src="' . PODS_URL . 'ui/images/help.png" alt="' . esc_attr($text) . '" class="pods-icon pods-qtip" />';
}

/**
 * Filter input and return sanitized output
 *
 * @param mixed $input The string, array, or object to sanitize
 * @since 1.2.0
 */
function pods_sanitize ($input) {
    $output = array();

    if (empty($input))
        $output = $input;
    elseif (is_object($input)) {
        $input = get_object_vars($input);
        foreach ($input as $key => $val) {
            $output[pods_sanitize($key)] = pods_sanitize($val);
        }
        $output = (object) $output;
    }
    elseif (is_array($input)) {
        foreach ($input as $key => $val) {
            $output[pods_sanitize($key)] = pods_sanitize($val);
        }
    }
    else
        $output = esc_sql($input);
    $output = apply_filters('pods_sanitize', $output, $input);
    return $output;
}

/**
 * Filter input and return unsanitized output
 *
 * @param mixed $input The string, array, or object to unsanitize
 * @since 1.2.0
 */
function pods_unsanitize ($input) {
    $output = array();
    if (empty($input))
        $output = $input;
    elseif (is_object($input)) {
        $input = get_object_vars($input);
        foreach ($input as $key => $val) {
            $output[pods_unsanitize($key)] = pods_unsanitize($val);
        }
        $output = (object) $output;
    }
    elseif (is_array($input)) {
        foreach ($input as $key => $val) {
            $output[pods_unsanitize($key)] = pods_unsanitize($val);
        }
    }
    else
        $output = stripslashes($input);
    return $output;
}

/**
 * Return a variable (if exists)
 *
 * @param mixed $var The variable name or URI segment position
 * @param string $type (optional) "url", "get", "post", "request", "server", "session", "cookie", "constant", or "user"
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param mixed $allowed (optional) The value(s) allowed
 * @param bool $strict (optional) Only allow values (must not be empty)
 * @return mixed The variable (if exists), or default value
 * @since 1.10.6
 */
function pods_var ($var = 'last', $type = 'get', $default = null, $allowed = null, $strict = false) {
    $output = $default;
    if (is_array($type))
        $output = isset($type[$var]) ? $type[$var] : $output;
    elseif (is_object($type))
        $output = isset($type->$var) ? $type->$var : $output;
    else {
        $type = strtolower((string) $type);
        if ('get' == $type && isset($_GET[$var]))
            $output = stripslashes_deep($_GET[$var]);
        elseif (in_array($type, array('url', 'uri'))) {
            $url = parse_url(get_current_url());
            $uri = trim($url['path'], '/');
            $uri = array_filter(explode('/', $uri));

            if ('first' == $var)
                $var = 0;
            elseif ('last' == $var)
                $var = -1;

            if (is_numeric($var))
                $output = ($var < 0) ? $uri[count($uri) + $var] : $uri[$var];
        }
        elseif ('post' == $type && isset($_POST[$var]))
            $output = stripslashes_deep($_POST[$var]);
        elseif ('request' == $type && isset($_REQUEST[$var]))
            $output = stripslashes_deep($_REQUEST[$var]);
        elseif ('server' == $type && isset($_SERVER[$var]))
            $output = stripslashes_deep($_SERVER[$var]);
        elseif ('session' == $type && isset($_SESSION[$var]))
            $output = $_SESSION[$var];
        elseif ('cookie' == $type && isset($_COOKIE[$var]))
            $output = stripslashes_deep($_COOKIE[$var]);
        elseif ('constant' == $type && defined($var))
            $output = constant($var);
        elseif ('user' == $type && is_user_logged_in()) {
            global $user_ID;
            get_currentuserinfo();
            $value = get_user_meta($user_ID, $var, true);
            if (is_array($value) || 0 < strlen($value))
                $output = $value;
        }
    }
    if (null !== $allowed) {
        if (is_array($allowed)) {
            if (!in_array($output, $allowed))
                $output = $default;
        }
        elseif ($allowed !== $output)
            $output = $default;
    }
    if (true === $strict && empty($output))
        $output = $default;
    $output = apply_filters('pods_var', $output, $var, $type);
    return pods_sanitize($output);
}

/**
 * Set a variable
 *
 * @param mixed $value The value to be set
 * @param mixed $key The variable name or URI segment position
 * @param string $type (optional) "url", "get", "post", "request", "server", "session", "cookie", "constant", or "user"
 * @return mixed $value (if set), $type (if $type is array or object), or $url (if $type is 'url')
 * @since 1.10.6
 */
function pods_var_set ($value, $key = 'last', $type = 'url') {
    $type = strtolower($type);
    $ret = false;
    if (is_array($type)) {
        $type[$key] = $value;
        $ret = $type;
    }
    elseif (is_object($type)) {
        $type->$key = $value;
        $ret = $type;
    }
    elseif ('url' == $type) {
        $url = parse_url(get_current_url());
        $uri = trim($url['path'], '/');
        $uri = array_filter(explode('/', $uri));

        if ('first' == $key)
            $key = 0;
        elseif ('last' == $key)
            $key = -1;

        if (is_numeric($key)) {
            if ($key < 0)
                $uri[count($uri) + $key] = $value;
            else
                $uri[$key] = $value;
        }
        $url['path'] = '/' . implode('/', $uri) . '/';
        $url['path'] = trim($url['path'], '/');
        $ret = http_build_url($url);
    }
    elseif ('get' == $type)
        $ret = $_GET[$key] = $value;
    elseif ('post' == $type)
        $ret = $_POST[$key] = $value;
    elseif ('request' == $type)
        $ret = $_REQUEST[$key] = $value;
    elseif ('server' == $type)
        $ret = $_SERVER[$key] = $value;
    elseif ('session' == $type)
        $ret = $_SESSION[$key] = $value;
    elseif ('cookie' == $type)
        $ret = $_COOKIE[$key] = $value;
    elseif ('constant' == $type && !defined($key)) {
        define($key, $value);
        $ret = constant($key);
    }
    elseif ('user' == $type && is_user_logged_in()) {
        global $user_ID;
        get_currentuserinfo();
        update_user_meta($user_ID, $key, $value);
        $ret = $value;
    }
    return apply_filters('pods_var_set', $ret, $value, $key, $type);
}

/**
 * Create a slug from an input string
 *
 * @param string $str
 * @since 1.8.9
 */
function pods_create_slug ($str) {
    $str = preg_replace("/([_ ])/", "-", trim($str));
    $str = preg_replace("/([^0-9a-z-.])/", "", strtolower($str));
    $str = preg_replace("/(-){2,}/", "-", $str);
    $str = apply_filters('pods_create_slug', $str);
    return $str;
}

/**
 * Return a lowercase alphanumeric name (with underscores)
 *
 * @param string $name Input string to clean
 * @since 1.2.0
 */
function pods_clean_name ($str) {
    $str = preg_replace("/([- ])/", "_", trim($str));
    $str = preg_replace("/([^0-9a-z_])/", "", strtolower($str));
    $str = preg_replace("/(_){2,}/", "_", $str);
    $str = trim($str, '_');
    $str = apply_filters('pods_clean_name', $str);
    return $str;
}

/**
 * Build a unique slug
 *
 * @param string $value The slug value
 * @param string $column_name The column name
 * @param string $pod The datatype name
 * @param int $pod_id The datatype ID
 * @return string The unique slug name
 * @since 1.7.2
 */
function pods_unique_slug ($value, $column_name, $pod, $pod_id = 0, &$obj = null) {
    $value = sanitize_title($value);

    $id = 0;
    if (is_object($pod)) {
        if (isset($pod->id))
            $id = $pod->id;
        if (isset($pod->pod_id))
            $pod_id = $pod->pod_id;
        if (isset($pod->datatype))
            $pod = $pod->datatype;
        else
            $pod = '';
    }
    $pod_id = absint($pod_id);
    $id = absint($id);

    $sql = "
    SELECT DISTINCT
        `t`.`{$column_name}` AS `slug`
    FROM
        `@wp_pods_tbl_{$pod}` `t`
    WHERE
        `t`.`{$column_name}` = %s
    LIMIT 1
    ";
    $sql = array($sql, array($value));
    if (0 < $id) {
        $sql = "
        SELECT DISTINCT
            `t`.`{$column_name}` AS `slug`
        FROM
            `@wp_pods_tbl_{$pod}` `t`
        WHERE
            `t`.`{$column_name}` = %s AND `t`.`id` != %d
        LIMIT 1
        ";
        $sql = array($sql, array($value, $id));
    }

    $result = pods_query($sql, $obj);
    if (0 < count($result)) {
        $unique_num = 0;
        $unique_found = false;
        while (!$unique_found) {
            $unique_num++;
            $test_slug = pods_sanitize($value . '-' . $unique_num);
            $sql[1][0] = $test_slug;
            $result = pods_query($sql, $obj);
            if (0 < count($result))
                continue;
            $value = $test_slug;
            $unique_found = true;
        }
    }
    $value = apply_filters('pods_unique_slug', $value, $column_name, $pod, $pod_id, $obj);
    return $value;
}

/**
 * Get the Absolute Integer of a value
 *
 * @param string $maybeint
 * @return integer
 * @since 2.0.0
 */
function pods_absint ($maybeint, $strict = true, $allow_negative = false) {
    if (true === $strict && !is_numeric(trim($maybeint)))
        return 0;
    if (false !== $allow_negative)
        return intval($maybeint);
    return absint($maybeint);
}

/**
 * Run a Pods Helper
 *
 * @param string $uri The Pod Page URI to check if currently on
 * @return bool
 * @since 1.7.5
 */
function pods_helper ($helper_name, $value = null, $name = null) {
    $pod = new Pod();
    return $pod->helper($helper_name, $value, $name);
}

/**
 * Find out if the current page is a Pod Page
 *
 * @param string $uri The Pod Page URI to check if currently on
 * @return bool
 * @since 1.7.5
 */
function is_pod_page ($uri = null) {
    global $pod_page_exists;
    if (false !== $pod_page_exists) {
        if (null === $uri || $uri == $pod_page_exists['uri']) {
            return true;
        }
    }
    return false;
}

/**
 * Get current URL of any page
 *
 * @return string
 * @since 1.9.6
 */
if (!function_exists('get_current_url')) {
    function get_current_url () {
        $url = 'http';
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && 0 != $_SERVER['HTTPS'])
            $url = 'https';
        $url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return apply_filters('get_current_url', $url);
    }
}

/**
 * Find out if the current page has a valid $pods
 *
 * @param object $object The Pod Object currently checking (optional)
 * @return bool
 * @since 2.0.0
 */
function is_pod ($object = null) {
    global $pods;
    if (is_object($object) && isset($object->pod) && !empty($object->pod))
        return true;
    if (is_object($pods) && isset($pods->pod) && !empty($pods->pod))
        return true;
    return false;
}

/**
 * Check to see if Pod Page exists and return data
 *
 * $uri not required, if NULL then returns REQUEST_URI matching Pod Page
 *
 * @param string $uri The Pod Page URI to check if exists
 * @return array
 */
function pod_page_exists ($uri = null) {
    if (null === $uri) {
        $uri = parse_url(get_current_url());
        $uri = $uri['path'];
        $home = parse_url(get_bloginfo('url'));
        if(!empty($home) && isset($home['path']) && '/' != $home['path'])
            $uri = substr($uri, strlen($home['path']));
    }
    $uri = trim($uri,'/');
    $uri_depth = count(array_filter(explode('/', $uri))) - 1;

    if (false !== strpos($uri, 'wp-admin') || false !== strpos($uri, 'wp-includes'))
        return false;

    // See if the custom template exists
    $sql = "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page' AND `name` = %s LIMIT 1";
    $sql = array($sql, array($uri));
    $result = pods_query($sql);
    if (empty($result)) {
        // Find any wildcards
        $sql = "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page' AND %s LIKE REPLACE(`name`, '*', '%%') AND (LENGTH(`name`) - LENGTH(REPLACE(`name`, '/', ''))) = %d ORDER BY LENGTH(`name`) DESC, `name` DESC LIMIT 1";
        $sql = array($sql, array($uri, $uri_depth));
        $result = pods_query($sql);
    }
    if (!empty($result))
        return get_object_vars($result[0]);
    return false;
}

/**
 * See if the current user has a certain privilege
 *
 * @param mixed $priv The privilege name or names (array if multiple)
 * @param string $method The access method ("AND", "OR")
 * @return bool
 * @since 1.2.0
 */
function pods_access ($privs, $method = 'OR') {
    global $pods_roles;

    if (empty($pods_roles) && !is_array($pods_roles)) {
        $pods_roles = @unserialize(get_option('pods_roles'));
        if (!is_array($pods_roles))
            $pods_roles = array();
    }

    // Convert $privs to an array
    $privs = (array) $privs;

    // Convert $method to uppercase
    $method = strtoupper($method);

    $check = apply_filters('pods_access', null, $privs, $method, $pods_roles);
    if (null !== $check && is_bool($check))
        return $check;

    if (!is_user_logged_in())
        return false;

    if (current_user_can('administrator') || current_user_can('pods_administrator') || is_super_admin())
        return true;

    // Store approved privs when using "AND"
    $approved_privs = array();

    // Loop through the user's roles
    if (is_array($pods_roles)) {
        foreach ($pods_roles as $role => $pods_privs) {
            if (current_user_can($role)) {
                foreach ($privs as $priv) {
                    if (false !== array_search($priv, $pods_privs) || current_user_can('pods_'.ltrim($priv,'pod_'))) {
                        if ('OR' == $method)
                            return true;
                        $approved_privs[$priv] = true;
                    }
                }
            }
        }
        if ('AND' == strtoupper($method)) {
            foreach ($privs as $priv) {
                if (isset($approved_privs[$priv]))
                    return false;
            }
            return true;
        }
    }
    return false;
}

/**
 * Shortcode support for use anywhere that support WP Shortcodes
 *
 * @param array $tags An associative array of shortcode properties
 * @since 1.6.7
 */
function pods_shortcode ($tags, $content = null) {
    $defaults = array('name' => null,
                   'id' => null,
                   'slug' => null,
                   'select' => null,
                   'order' => null,
                   'orderby' => null,
                   'limit' => null,
                   'where' => null,
                   'search' => true,
                   'page' => null,
                   'filters' => false,
                   'filters_label' => null,
                   'filters_location' => 'before',
                   'pagination' => false,
                   'pagination_label' => null,
                   'pagination_location' => 'after',
                   'col' => null,
                   'template' => null,
                   'helper' => null);
    $tags = shortcode_atts($defaults, $tags);
    $tags = apply_filters('pods_shortcode', $tags);

    if (empty($tags['name'])) {
        return '<e>Please provide a Pod name';
    }
    if (empty($tags['template']) && empty($tags['col'])) {
        return '<e>Please provide either a template or column name';
    }

    // id > slug (if both exist)
    $id = empty($tags['slug']) ? null : $tags['slug'];
    $id = empty($tags['id']) ? $id : absint($tags['id']);

    $pod = new Pod($tags['name'], $id);

    $found = 0;
    if (empty($id)) {
        $params = array();
        if (0 < strlen($tags['order']))
            $params['orderby'] = $tags['order'];
        if (0 < strlen($tags['orderby']))
            $params['orderby'] = $tags['orderby'];
        if (!empty($tags['limit']))
            $params['limit'] = $tags['limit'];
        if (0 < strlen($tags['where']))
            $params['where'] = $tags['where'];
        if (0 < strlen($tags['select']))
            $params['select'] = $tags['select'];
        if (empty($tags['search']))
            $params['search'] = false;
        if (0 < absint($tags['page']))
            $params['page'] = absint($tags['page']);
        $params = apply_filters('pods_shortcode_findrecords_params', $params);
        $pod->findRecords($params);
        $found = $pod->getTotalRows();
    }
    elseif (!empty($tags['col'])) {
        $val = $pod->get_field($tags['col']);
        return empty($tags['helper']) ? $val : $pod->pod_helper($tags['helper'], $val);
    }
    ob_start();
    if (empty($id) && false !== $tags['filters'] && 'before' == $tags['filters_location'])
        echo $pod->getFilters($tags['filters'], $tags['filters_label']);
    if (empty($id) && 0 < $found && false !== $tags['pagination'] && 'before' == $tags['pagination_location'])
        echo $pod->getPagination($tags['pagination_label']);
    echo $pod->showTemplate($tags['template']);
    if (empty($id) && 0 < $found && false !== $tags['pagination'] && 'after' == $tags['pagination_location'])
        echo $pod->getPagination($tags['pagination_label']);
    if (empty($id) && false !== $tags['filters'] && 'after' == $tags['filters_location'])
        echo $pod->getFilters($tags['filters'], $tags['filters_label']);
    return ob_get_clean();
}

/**
 * Generate form key - INTERNAL USE
 *
 * @since 1.2.0
 */
function pods_generate_key ($datatype, $uri_hash, $columns, $form_count = 1) {
    $token = wp_create_nonce('pods-form-' . $datatype . '-' . (int) $form_count . '-' . $uri_hash . '-' . json_encode($columns));
    $token = apply_filters('pods_generate_key', $token, $datatype, $uri_hash, $columns, (int) $form_count);
    $_SESSION['pods_form_' . $token] = $columns;
    return $token;
}

/**
 * Validate form key - INTERNAL USE
 *
 * @since 1.2.0
 */
function pods_validate_key ($token, $datatype, $uri_hash, $columns = null, $form_count = 1) {
    if (null === $columns && !empty($_SESSION) && isset($_SESSION['pods_form_' . $token]))
        $columns = $_SESSION['pods_form_' . $token];
    $success = false;
    if (false !== wp_verify_nonce($token, 'pods-form-' . $datatype . '-' . (int) $form_count . '-' . $uri_hash . '-' . json_encode($columns)))
        $success = $columns;
    return apply_filters('pods_validate_key', $success, $token, $datatype, $uri_hash, $columns, (int) $form_count);
}

/**
 * Output Pod Page Content
 */
function pods_content () {
    global $pod_page_exists;

    do_action('pods_content_pre', $pod_page_exists);
    $content = false;
    if (false !== $pod_page_exists) {
        $function_or_file = str_replace('*', 'w', $pod_page_exists['uri']);
        $check_function = false;
        $check_file = null;
        if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_PAGE_FILES') || !PODS_PAGE_FILES))
            $check_file = false;
        if (false !== $check_function && false !== $check_file)
            $function_or_file = pods_function_or_file($function_or_file, $check_function, 'page', $check_file);
        else
            $function_or_file = false;

        if (!$function_or_file && 0 < strlen(trim($pod_page_exists['phpcode'])))
            $content = $pod_page_exists['phpcode'];

        ob_start();
        if (false === $content && false !== $function_or_file && isset($function_or_file['file']))
            locate_template($function_or_file['file'], true, true);
        elseif (false !== $content) {
            if (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                eval("?>$content");
            else
                echo $content;
        }
        $content = ob_get_clean();
        echo apply_filters('pods_content', $content);
    }
    do_action('pods_content_post', $pod_page_exists, $content);
}

/**
 * Get a Point value from a Pods Version number
 *
 * @since 1.10.1
 */
function pods_point_to_version ($point) {
    $version_tmp = explode('.', $point);
    $version = '';
    for ($x = 0; $x < 3; $x++) { // 3 points max - MAJOR.MINOR.PATCH
        if (!isset($version_tmp[$x]) || strlen($version_tmp[$x]) < 1)
            $version_tmp[$x] = '000';
        $version_temp = str_split($version_tmp[$x]);
        if (3 == count($version_temp))
            $version .= $version_tmp[$x];
        elseif (2 == count($version_temp))
            $version .= '0' . $version_tmp[$x];
        elseif (1 == count($version_temp))
            $version .= '00' . $version_tmp[$x];
    }
    $version = (int) $version;
    return $version;
}

/**
 * Get a Point value from a Pods Version number
 *
 * @since 1.10
 */
function pods_version_to_point ($version) {
    $point_tmp = $version;
    if (strlen($point_tmp) < 9) {
        if (8 == strlen($point_tmp))
            $point_tmp = '0' . $point_tmp;
        if (7 == strlen($point_tmp))
            $point_tmp = '00' . $point_tmp;
        if (3 == strlen($version)) // older versions prior to 1.9.9
            return implode('.', str_split($version));
    }
    $point_tmp = str_split($point_tmp, 3);
    $point = array();
    foreach ($point_tmp as $the_point) {
        $point[] = (int) $the_point;
    }
    $point = implode('.', $point);
    return $point;
}

/**
 * Check if Pods is compatible with WP / PHP / MySQL or not
 *
 * @since 1.10
 */
function pods_compatible ($wp = null, $php = null, $mysql = null) {
    global $wp_version;
    if (null === $wp)
        $wp = $wp_version;
    if (null === $php)
        $php = phpversion();
    if (null === $mysql) {
        $mysql = pods_query("SELECT VERSION() AS `mysql_version`");
        $mysql = $mysql[0]->mysql_version;
    }
    $compatible = true;
    if (!version_compare($wp, PODS_WP_VERSION_MINIMUM, '>=')) {
        $compatible = false;
        add_action('admin_notices', 'pods_version_notice_wp');
        function pods_version_notice_wp () {
?>
    <div class="error fade">
        <p><strong>NOTICE:</strong> Pods <?php echo PODS_VERSION_FULL; ?> requires a minimum of <strong>WordPress <?php echo PODS_WP_VERSION_MINIMUM; ?>+</strong> to function. You are currently running <strong>WordPress <?php echo get_bloginfo("version"); ?></strong> - Please upgrade your WordPress to continue.</p>
    </div>
<?php
        }
    }
    if (!version_compare($php, PODS_PHP_VERSION_MINIMUM, '>=')) {
        $compatible = false;
        add_action('admin_notices', 'pods_version_notice_php');
        function pods_version_notice_php () {
?>
    <div class="error fade">
        <p><strong>NOTICE:</strong> Pods <?php echo PODS_VERSION_FULL; ?> requires a minimum of <strong>PHP <?php echo PODS_PHP_VERSION_MINIMUM; ?>+</strong> to function. You are currently running <strong>PHP <?php echo phpversion(); ?></strong> - Please upgrade (or have your Hosting Provider upgrade it for you) your PHP version to continue.</p>
    </div>
<?php
        }
    }
    if (!@version_compare($mysql, PODS_MYSQL_VERSION_MINIMUM, '>=')) {
        $compatible = false;
        add_action('admin_notices', 'pods_version_notice_mysql');
        function pods_version_notice_mysql () {
            $mysql = pods_query("SELECT VERSION() AS `mysql_version`");
            $mysql = $mysql[0]->mysql_version;
?>
    <div class="error fade">
        <p><strong>NOTICE:</strong> Pods <?php echo PODS_VERSION_FULL; ?> requires a minimum of <strong>MySQL <?php echo PODS_MYSQL_VERSION_MINIMUM; ?>+</strong> to function. You are currently running <strong>MySQL <?php echo $mysql; ?></strong> - Please upgrade (or have your Hosting Provider upgrade it for you) your MySQL version to continue.</p>
    </div>
<?php
        }
    }
    return $compatible;
}

/**
 * Check if a Function exists or File exists in Theme / Child Theme
 *
 * @since 1.12
 */
function pods_function_or_file ($function_or_file, $function_name = null, $file_dir = null, $file_name = null) {
    $found = false;
    $function_or_file = (string) $function_or_file;
    if (false !== $function_name) {
        if (null === $function_name)
            $function_name = $function_or_file;
        $function_name = str_replace(array('__', '__', '__'), '_', preg_replace('/[^a-z^A-Z^_][^a-z^A-Z^0-9^_]*/', '_', (string) $function_name));
        if (function_exists('pods_custom_' . $function_name))
            $found = array('function' => 'pods_custom_' . $function_name);
        elseif (function_exists($function_name))
            $found = array('function' => $function_name);
    }
    if (false !== $file_name && false === $found) {
        if (null === $file_name)
            $file_name = $function_or_file;
        $file_name = str_replace(array('__', '__', '__'), '_', preg_replace('/[^a-z^A-Z^0-9^_]*/', '_', (string) $file_name)) . '.php';
        $custom_location = apply_filters('pods_file_directory', null, $function_or_file, $function_name, $file_dir, $file_name);
        if (defined('PODS_FILE_DIRECTORY') && false !== PODS_FILE_DIRECTORY)
            $custom_location = PODS_FILE_DIRECTORY;
        if (!empty($custom_location) && locate_template(trim($custom_location, '/') . '/' . (!empty($file_dir) ? $file_dir . '/' : '') . $file_name))
            $found = array('file' => trim($custom_location, '/') . '/' . (!empty($file_dir) ? $file_dir . '/' : '') . $file_name);
        elseif (locate_template('pods/' . (!empty($file_dir) ? $file_dir . '/' : '') . $file_name))
            $found = array('file' => 'pods/' . (!empty($file_dir) ? $file_dir . '/' : '') . $file_name);
        elseif (locate_template('pods-' . (!empty($file_dir) ? $file_dir . '-' : '') . $file_name))
            $found = array('file' => 'pods-' . (!empty($file_dir) ? $file_dir . '-' : '') . $file_name);
        elseif (locate_template('pods/' . (!empty($file_dir) ? $file_dir . '-' : '') . $file_name))
            $found = array('file' => 'pods/' . (!empty($file_dir) ? $file_dir . '-' : '') . $file_name);
    }
    return apply_filters('pods_function_or_file', $found, $function_or_file, $function_name, $file_name);
}

/**
 * Include and Init the Pods class
 *
 * @since 2.0.0
 */
function pods_init () {
    require_once(PODS_DIR . 'classes/PodsInit.php');
    return new PodsInit();
}

/**
 * Include and Init the Pods class
 *
 * @since 2.0.0
 */
function pods ($type = null, $id = null) {
    require_once(PODS_DIR . 'classes/Pods.php');
    return new Pods($type, $id);
}

/**
 * Include and Init the PodsUI class
 *
 * @since 2.0.0
 */
function pods_ui ($obj = null) {
    require_once(PODS_DIR . 'classes/PodsUI.php');
    return new PodsUI($obj);
}

/**
 * Include and Init the PodsAPI class
 *
 * @since 2.0.0
 */
function pods_api () {
    require_once(PODS_DIR . 'classes/PodsAPI.php');
    return new PodsAPI();
}

/**
 * Include and Init the PodsData class
 *
 * @since 2.0.0
 */
function pods_data ($pod = null, $id = null) {
    require_once(PODS_DIR . 'classes/PodsData.php');
    return new PodsData($pod, $id);
}

/**
 * Include and Init the PodsFormUI class
 *
 * @since 2.0.0
 */
function pods_form () {
    require_once(PODS_DIR . 'classes/PodsForm.php');
    return PodsForm;
}

/**
 * Include and Init the PodsAdminUI class
 *
 * @since 2.0.0
 */
function pods_admin () {
    require_once(PODS_DIR . 'classes/PodsAdmin.php');
    return new PodsAdmin();
}

/**
 * Include and Init the PodsMigrate class
 *
 * @since 2.0.0
 */
function pods_migrate ($type = null, $delimiter = null, $data = null) {
    require_once(PODS_DIR . 'classes/PodsMigrate.php');
    return new PodsMigrate($type, $delimiter, $data);
}

/**
 * Include and Init the PodsArray class
 *
 * @since 2.0.0
 */
function pods_array ($container) {
    require_once(PODS_DIR . 'classes/PodsArray.php');
    return new PodsArray(&$container);
}