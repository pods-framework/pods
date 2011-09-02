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
function pods_error ($error, $obj = null) {
    $display_errors = false;
    if (is_object($obj) && isset($obj->display_errors) && true === $obj->display_errors)
        $display_errors = true;
    elseif (is_bool($obj) && true === $obj)
        $display_errors = true;
    // log error in WP
    $log_error = new WP_Error('pods-error-'.md5($error),$error);
    // throw error as Exception and return false if silent
    if (false !== $display_errors) {
        throw new Exception($error);
        return false;
    }
    // die with error
    die("<e>$error");
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
function pods_deprecated($function, $version, $replacement = null) {

	do_action('deprecated_function_run', $function, $replacement, $version);

	// Allow plugin to filter the output error trigger
	if (WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true)) {
		if (!is_null($replacement))
			trigger_error(sprintf(__('%1$s is <strong>deprecated</strong> since Pods version %2$s! Use %3$s instead.'), $function, $version, $replacement));
		else
			trigger_error(sprintf(__('%1$s is <strong>deprecated</strong> since Pods version %2$s with no alternative available.'), $function, $version));
	}
}

/**
 * Output content from Pod Page
 *
 * @param string $uri The Pod Page URI to output
 */
function pods_content ($uri = null, $output = true) {
    global $pod_page_exists;
    $phpcode = $pod_page_exists['phpcode'];

    if (null != $uri) {
        if (is_array($uri)) {
            $pairs = array('uri' => null);
            $tags = shortcode_atts($pairs, $uri);
            $uri = null;
            if(null != $tags['uri'])
                $uri = $tags['uri'];
        }
        elseif (!is_string($uri))
            $uri = null;
        if (null != $uri) {
            $pod_page = pod_page_exists($uri);
            $phpcode = $pod_page['phpcode'];
        }
    }

    ob_start();
    eval('?>' . $phpcode);
    $output = apply_filters('pods_content', ob_get_clean());
    if (true === $output)
        echo $output;
    else
        return $output;
}

/**
 * Filter input and return sanitized output
 *
 * @param mixed $input The string, array, or object to sanitize
 * @since 1.2.0
 */
function pods_sanitize ($input) {
    global $wpdb;
    $output = array();
    if (is_object($input)) {
        $input = get_object_vars($input);
        foreach ($input as $key => $val) {
            $output[$key] = pods_sanitize($val);
        }
        $output = (object) $output;
    }
    elseif (is_array($input)) {
        foreach ($input as $key => $val) {
            $output[$key] = pods_sanitize($val);
        }
    }
    elseif (empty($input))
        $output = $input;
    else
        $output = $wpdb->_real_escape(trim($input));
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
    if (is_object($input)) {
        $input = get_object_vars($input);
        foreach ($input as $key => $val) {
            $output[$key] = pods_unsanitize($val);
        }
        $output = (object) $output;
    }
    elseif (is_array($input)) {
        foreach ($input as $key => $val) {
            $output[$key] = pods_unsanitize($val);
        }
    }
    elseif (empty($input))
        $output = $input;
    else
        $output = stripslashes($input);
    return $output;
}

/**
 * Return a URI string segment, GET, POST, SESSION, COOKIE, or USER variable
 *
 * @param mixed $var The variable name or URI segment position
 * @param string $method (optional) "uri", "get", "post", "session", "cookie", or "user"
 * @param mixed $default (optional) The default value to return if not found
 * @return mixed The requested value, or null
 * @since 1.6.2
 */
function pods_var ($var = 'last', $method = 'uri', $default = null) {
    if (is_array($var)) {
        foreach ($var as $key) {
            if (false !== pods_ui_var($key, $method, $default))
                return true;
        }
        return false;
    }
    
    $ret = false;
    if (is_array($method)) {
        if (isset($method[$var]))
            $ret = $method[$var];
    }
    else
        $method = strtolower($method);
    
    if ('uri' == $method) {
        $uri = explode('?', $_SERVER['REQUEST_URI']);
        $uri = explode('#', $uri[0]);
        $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
        $uri = explode('/', $uri);

        if ('first' == $var || 0 == $var)
            $var = 0;
        elseif ('last' == $var || -1 == $var)
            $var = -1;
        else
            $var = intval($var);
        
        if ($var < 0)
            $var = count($uri) + $var;
        if (isset($uri[$var]))
            $ret = $uri[$var];
    }
    elseif ('get' == $method && isset($_GET[$var])) {
        $ret = $_GET[$var];
        if (empty($ret))
            $ret = false;
    }
    elseif ('post' == $method && isset($_POST[$var])) {
        $ret = $_POST[$var];
        if (empty($ret))
            $ret = false;
    }
    elseif ('session' == $method && isset($_SESSION[$var])) {
        $ret = $_SESSION[$var];
        if (empty($ret))
            $ret = false;
    }
    elseif ('cookie' == $method && isset($_COOKIE[$var])) {
        $ret = $_COOKIE[$var];
        if (empty($ret))
            $ret = false;
    }
    elseif ('user' == $method) {
        if (!is_user_logged_in())
            return false;
        global $user_ID;
        get_currentuserinfo();
        $ret = get_user_meta($user_ID, $var, true);
        if (!is_array($ret) && strlen($ret) < 1)
            $ret = false;
    }
    
    if (false === $ret && null !== $default)
        $ret = $default;
    
    return pods_sanitize($ret);
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
    return $str;
}

/**
 * Build a unique slug
 *
 * @todo Simplify this function - get rid of the pod_id crap
 * @param string $value The slug value
 * @param string $column The column name
 * @param string $pod The Pod name
 * @param int $id The item's ID
 * @return string The unique slug name
 * @since 1.7.2
 */
function pods_unique_slug ($value, $column, $pod, $id = 0) {
    $value = sanitize_title($value, '', 'save');
    $column = pods_sanitize($column);
    $pod = pods_sanitize($pod);
    $id = pods_absint($id);
    $sql = "
    SELECT DISTINCT
        `{$column}`
    FROM
        `@wp_pod_tbl_{$pod}`
    WHERE
        `id` != '{$id}' AND `{$column}` = '{$value}'
    ";
    $taken = pods_query($sql);
    if (!empty($taken)) {
        $unique_num = 1;
        while (0 < $unique_found) {
            $unique_num++;
            $slug = $value . '-' . $unique_num;
            $sql = "
            SELECT DISTINCT
                `{$column}`
            FROM
                `@wp_pod_tbl_{$pod}`
            WHERE
                `id` != '{$id}' AND `{$column}` = '{$slug}'
            LIMIT 1
            ";
            $taken = pods_query($sql);
            if (empty($taken))
                return $slug;
        }
    }
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
        if (null == $uri || $uri == $pod_page_exists['uri']) {
            return true;
        }
    }
    return false;
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
    if (null==$uri) {
        $uri = explode('?', $_SERVER['REQUEST_URI']);
        $uri = explode('#', $uri[0]);
        $uri = $uri[0];
        $site_url = parse_url(get_bloginfo('url'));
        if (isset($site_url['path']) && !empty($site_url['path']) && '/' != $site_url['path'])
            $uri = str_replace($site_url['path'], '', $uri);
    }
    $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri);
    $uri = pods_sanitize($uri);

    if (false !== strpos($uri, 'wp-admin'))
        return false;

    // See if the custom template exists
    $result = pods_query("SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page' AND `name` = '{$uri}' LIMIT 1");
    if (empty($result)) {
        // Find any wildcards
        $sql = "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page' AND '{$uri}' LIKE REPLACE(`name`, '*', '%') ORDER BY LENGTH(`name`) DESC, `name` DESC LIMIT 1";
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
    if (!is_user_logged_in())
        return false;
    if (current_user_can('administrator') || current_user_can('pods_administrator') || (function_exists('is_super_admin') && is_super_admin()))
        return true;

    // Convert $method to uppercase
    $method = strtoupper($method);

    // Convert $privs to an array
    $privs = (array) $privs;

    // Loop through the user's roles
    if (!empty($privs)) {
        foreach ($privs as $priv) {
            if (current_user_can('pods_'.ltrim($priv,'pod_'))) {
                if('OR' == $method)
                    return true;
            }
            elseif('AND' == $method)
                return false;
        }
    }
    if('AND' == $method)
        return true;
    return false;
}

/**
 * Shortcode support for use anywhere that support WP Shortcodes
 *
 * @param array $tags An associative array of shortcode properties
 * @since 1.6.7
 */
function pods_shortcode ($tags, $content = null) {
    $pairs = array('name' => null, 'id' => null, 'slug' => null, 'col' => null, 'template' => null, 'helper' => null, 'limit' => 15, 'where' => null, 'orderby' => null, 'order' => null);
    $tags = shortcode_atts($pairs, $tags);

    if (strlen($tags['name']) < 1) {
        return pods_content(null,false);
    }
    if (strlen($tags['template']) < 1 && strlen($tags['col']) < 1) {
        return '<e>Please provide either a template or column name';
    }

    if (0 < strlen($tags['slug']) && strlen($tags['id']) < 1)
        $tags['id'] = $tags['slug'];

    if (isset($tags['order']) && 0 < strlen($tags['order'])) {
        $tags['orderby'] = $tags['order'];
        unset($tags['order']);
    }

    $shortcode = new Pod($tags['name'], $tags['id']);

    if ($shortcode->get_field('id') < 1)
        $shortcode->findRecords($tags);
    if (!empty($tags['col'])) {
        if (!empty($id)) {
            if (0 < strlen($tags['helper']))
                $val = $shortcode->pod_helper($tags['helper'], $shortcode->get_field($tags['col']));
            else
                $val = $shortcode->get_field($tags['col']);
        }
        else {
            $val = "<ul>\n";
            while ($shortcode->fetchRecord()) {
                $val .= "\t<li>";
                if (0 < strlen($tags['helper']))
                    $val .= $shortcode->pod_helper($tags['helper'], $shortcode->get_field($tags['col']));
                else
                    $val .= $shortcode->get_field($tags['col']);
                $val .= "</li>\n";
            }
            $val .= "</ul>\n";
        }
        return empty($tags['helper']) ? $val : $shortcode->pod_helper($tags['helper'], $val);
    }
    return $shortcode->showTemplate($tags['template'],$content);
}

/**
 * Translation support
 *
 * @param string $string The string to translate
 * @return string The translated string, or the original string
 * @since 1.7.0
 */
function pods_i18n ($string) {
    global $lang;

    if (isset($lang[$string])) {
        $string = $lang[$string];
    }
    return $string;
}

/**
 * Generate form key - INTERNAL USE
 *
 * @since 1.2.0
 */
function pods_generate_key ($datatype, $uri_hash, $public_columns, $form_count = 1) {
    $token = md5(mt_rand());
    $_SESSION[$uri_hash][$form_count]['dt'] = $datatype;
    $_SESSION[$uri_hash][$form_count]['token'] = $token;
    $_SESSION[$uri_hash][$form_count]['columns'] = $public_columns;
    return $token;
}

/**
 * Validate form key - INTERNAL USE
 *
 * @since 1.2.0
 */
function pods_validate_key ($key, $uri_hash, $datatype, $form_count = 1) {
    if (!empty($_SESSION[$uri_hash])) {
        $session_dt = $_SESSION[$uri_hash][$form_count]['dt'];
        $session_token = $_SESSION[$uri_hash][$form_count]['token'];
        if (!empty($session_token) && $key == $session_token && $datatype == $session_dt) {
            return true;
        }
    }
    return false;
}

/**
 * Include and Init the Pods class
 *
 * @since 2.0.0
 */
function pods_init () {
    require_once(PODS_DIR . '/classes/PodsInit.php');
    return new PodsInit();
}

/**
 * Include and Init the Pods class
 *
 * @since 2.0.0
 */
function pods ($type = null, $id = null) {
    require_once(PODS_DIR . '/classes/Pods.php');
    return new Pods($type, $id);
}

/**
 * Include and Init the PodsUI class
 *
 * @since 2.0.0
 */
function pods_ui ($obj = null) {
    require_once(PODS_DIR . '/classes/PodsUI.php');
    return new PodsUI($obj);
}

/**
 * Include and Init the PodsAPI class
 *
 * @since 2.0.0
 */
function pods_api () {
    require_once(PODS_DIR . '/classes/PodsAPI.php');
    return new PodsAPI();
}

/**
 * Include and Init the PodsData class
 *
 * @since 2.0.0
 */
function pods_data ($pod = null, $id = null) {
    require_once(PODS_DIR . '/classes/PodsData.php');
    return new PodsData($pod, $id);
}

/**
 * Include and Init the PodsFormUI class
 *
 * @since 2.0.0
 */
function pods_form_ui () {
    require_once(PODS_DIR . '/classes/PodsFormUI.php');
    return new PodsFormUI();
}

/**
 * Include and Init the PodsAdminUI class
 *
 * @since 2.0.0
 */
function pods_admin_ui () {
    require_once(PODS_DIR . '/classes/PodsAdminUI.php');
    return new PodsAdminUI();
}

/**
 * Include and Init the PodsMigrate class
 *
 * @since 2.0.0
 */
function pods_migrate ($type = null, $delimiter = null, $data = null) {
    require_once(PODS_DIR . '/classes/PodsMigrate.php');
    return new PodsMigrate($type, $delimiter, $data);
}

/**
 * Include and Init the PodsMigrateUI class
 *
 * @since 2.0.0
 */
function pods_migrate_ui () {
    require_once(PODS_DIR . '/classes/PodsMigrateUI.php');
    return new PodsMigrateUI();
}

/**
 * Include and Init the PodsArray class
 *
 * @since 2.0.0
 */
function pods_array ($container) {
    require_once(PODS_DIR . '/classes/PodsArray.php');
    return new PodsArray(&$container);
}