<?php
/**
 * Standardize queries and error reporting
 *
 * @param string $query The SQL query
 * @param string $error (optional) The failure message
 * @param string $results_error (optional) Throw an error if a records are found
 * @param string $no_results_error (optional) Throw an error if no records are found
 * @since 1.2.0
 */
function pod_query($sql, $error = 'SQL failed', $results_error = null, $no_results_error = null) {
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
 * Filter input. Escape output.
 *
 * @param mixed $input The string, array, or object to sanitize
 * @since 1.2.0
 */
function pods_sanitize($input) {
    $output = array();

    if (empty($input))
        $output = $input;
    elseif (is_object($input)) {
        foreach ((array) $input as $key => $val) {
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
 * Return a GET, POST, COOKIE, SESSION, or URI string segment
 *
 * @param mixed $key The variable name or URI segment position
 * @param string $type (optional) "uri", "get", "post", "request", "server", "session", or "cookie"
 * @return string The requested value, or null
 * @since 1.6.2
 */
function pods_url_variable($key = 'last', $type = 'url') {
    $output = apply_filters('pods_url_variable', pods_var($key, $type), $key, $type);
    return $output;
}

/**
 * Return a variable (if exists)
 *
 * @param mixed $key The variable key or URI segment position
 * @param string $type (optional) "url", "get", "post", "request", "server", "session", "cookie", "constant", or "user"
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param mixed $allowed (optional) The value(s) allowed
 * @param bool $strict (optional) Only allow values (must not be empty)
 * @return mixed The variable (if exists), or default value
 * @since 1.10.6
 */
function pods_var($key = 'last', $type = 'get', $default = null, $allowed = null, $strict = false) {
    $output = $default;
    if (is_array($type))
        $output = isset($type[$key]) ? $type[$key] : $output;
    elseif (is_object($type))
        $output = isset($type->$key) ? $type->$key : $output;
    else {
        $type = strtolower((string) $type);
        if ('get' == $type && isset($_GET[$key]))
            $output = stripslashes_deep($_GET[$key]);
        elseif (in_array($type, array('url', 'uri'))) {
            $url = parse_url(get_current_url());
            $uri = trim($url['path'], '/');
            $uri = array_filter(explode('/', $uri));

            if ('first' == $key)
                $key = 0;
            elseif ('last' == $key)
                $key = -1;

            if (is_numeric($key))
                $output = ($key < 0) ? $uri[count($uri) + $key] : $uri[$key];
        }
        elseif ('post' == $type && isset($_POST[$key]))
            $output = stripslashes_deep($_POST[$key]);
        elseif ('request' == $type && isset($_REQUEST[$key]))
            $output = stripslashes_deep($_REQUEST[$key]);
        elseif ('server' == $type && isset($_SERVER[$key]))
            $output = stripslashes_deep($_SERVER[$key]);
        elseif ('session' == $type && isset($_SESSION[$key]))
            $output = $_SESSION[$key];
        elseif ('cookie' == $type && isset($_COOKIE[$key]))
            $output = stripslashes_deep($_COOKIE[$key]);
        elseif ('constant' == $type && defined($key))
            $output = constant($key);
        elseif ('user' == $type && is_user_logged_in()) {
            global $user_ID;
            get_currentuserinfo();
            $value = get_user_meta($user_ID, $key, true);
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
    $output = apply_filters('pods_var', $output, $key, $type);
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
function pods_var_set($value, $key = 'last', $type = 'url') {
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
function pods_create_slug($str) {
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
function pods_clean_name($str) {
    $str = preg_replace("/([- ])/", "_", trim($str));
    $str = preg_replace("/([^0-9a-z_])/", "", strtolower($str));
    $str = preg_replace("/(_){2,}/", "_", $str);
    $str = apply_filters('pods_clean_name', $str);
    return $str;
}

/**
 * Build a unique slug
 *
 * @todo Simplify this function - get rid of the pod_id crap
 * @param string $value The slug value
 * @param string $column_name The column name
 * @param string $datatype The datatype name
 * @param int $datatype_id The datatype ID
 * @param int $pod_id The item's ID in the wp_pod table
 * @return string The unique slug name
 * @since 1.7.2
 */
function pods_unique_slug($value, $column_name, $datatype, $datatype_id = 0, $pod_id = 0) {
    $value = sanitize_title($value);
    $slug = pods_sanitize($value);
    $tbl_row_id = 0;
    if (is_object($datatype)) {
        if (isset($datatype->tbl_row_id))
            $tbl_row_id = $datatype->tbl_row_id;
        if (isset($datatype->pod_id))
            $pod_id = $datatype->pod_id;
        if (isset($datatype->datatype_id))
            $datatype_id = $datatype->datatype_id;
        if (isset($datatype->datatype))
            $datatype = $datatype->datatype;
        else
            $datatype = '';
    }
    $datatype_id = absint($datatype_id);
    $tbl_row_id = absint($tbl_row_id);
    $pod_id = absint($pod_id);
    $sql = "
    SELECT DISTINCT
        t.`{$column_name}` AS slug
    FROM
        `@wp_pod_tbl_{$datatype}` t
    WHERE
        t.`{$column_name}` = '{$value}'
    ";
    if (0 < $tbl_row_id) {
        $sql = "
        SELECT DISTINCT
            t.`{$column_name}` AS slug
        FROM
            `@wp_pod_tbl_{$datatype}` t
        WHERE
            t.`{$column_name}` = '{$value}' AND t.id != {$tbl_row_id}
        ";
    }
    elseif (0 < $pod_id) {
        $sql = "
        SELECT DISTINCT
            t.`{$column_name}` AS slug
        FROM
            @wp_pod p
        INNER JOIN
            `@wp_pod_tbl_{$datatype}` t ON t.id = p.tbl_row_id
        WHERE
            t.`{$column_name}` = '{$slug}' AND p.datatype = {$datatype_id} AND p.id != {$pod_id}
        ";
    }
    $result = pod_query($sql);
    if (0 < mysql_num_rows($result)) {
        $unique_num = 0;
        $unique_found = false;
        while (!$unique_found) {
            $unique_num++;
            $test_slug = pods_sanitize($value . '-' . $unique_num);
            $result = pod_query(str_replace("t.`{$column_name}` = '{$slug}'", "t.`{$column_name}` = '{$test_slug}'", $sql));
            if (0 < mysql_num_rows($result))
                continue;
            $value = $test_slug;
            $unique_found = true;
        }
    }
    $value = apply_filters('pods_unique_slug', $value, $column_name, $datatype, $datatype_id, $pod_id);
    return $value;
}

/**
 * Find out if the current page is a Pod Page
 *
 * @param string $uri The Pod Page URI to check if currently on
 * @return bool
 * @since 1.7.5
 */
function is_pod_page($uri = null) {
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
    function get_current_url() {
        $url = 'http';
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && 0 != $_SERVER['HTTPS'])
            $url = 'https';
        $url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return apply_filters('get_current_url', $url);
    }
}

/**
 * Check to see if Pod Page exists and return data
 *
 * $uri not required, if NULL then returns REQUEST_URI matching Pod Page
 *
 * @param string $uri The Pod Page URI to check if exists
 * @return array
 */
function pod_page_exists($uri = null) {
    if (null === $uri) {
        $uri = parse_url(get_current_url());
        $uri = $uri['path'];
        $home = parse_url(get_bloginfo('url'));
        if(!empty($home) && isset($home['path']) && '/' != $home['path'])
            $uri = substr($uri, strlen($home['path']));
    }
    $uri = trim($uri,'/');
    $uri = esc_sql($uri);
    $uri_depth = count(array_filter(explode('/',$uri)))-1;

    if (false !== strpos($uri, 'wp-admin') || false !== strpos($uri, 'wp-includes'))
        return false;

    // See if the custom template exists
    $result = pod_query("SELECT * FROM @wp_pod_pages WHERE uri = '$uri' LIMIT 1");
    if (1 > mysql_num_rows($result)) {
        // Find any wildcards
        $sql = "SELECT * FROM @wp_pod_pages
                WHERE
                    (LENGTH(uri)-LENGTH(REPLACE(uri,'/','')))=$uri_depth
                    AND '$uri' LIKE REPLACE(uri, '*', '%')
                ORDER BY LENGTH(uri) DESC, uri DESC
                LIMIT 1";
        $result = pod_query($sql);
    }

    if (0 < mysql_num_rows($result)) {
        $pod_page_data = mysql_fetch_assoc($result);
        $validate_pod_page = explode('/',$pod_page_data['uri']);
        $validate_uri = explode('/',$uri);
        if (count($validate_pod_page)==count($validate_uri)) {
            return $pod_page_data;
        }
    }
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
function pods_access($privs, $method = 'OR') {
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

    if (current_user_can('administrator') || current_user_can('pods_administrator') || (function_exists('is_super_admin') && is_super_admin()))
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
 * Shortcode support for WP Posts and Pages
 *
 * @param array $tags An associative array of shortcode properties
 * @since 1.6.7
 */
function pods_shortcode($tags) {
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
function pods_generate_key($datatype, $uri_hash, $columns, $form_count = 1) {
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
function pods_validate_key($token, $datatype, $uri_hash, $columns = null, $form_count = 1) {
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
function pods_content() {
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
function pods_point_to_version($point) {
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
function pods_version_to_point($version) {
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
function pods_compatible($wp = null, $php = null, $mysql = null) {
    if (null === $wp)
        $wp = get_bloginfo("version");
    if (null === $php)
        $php = phpversion();
    if (null === $mysql)
        $mysql = @mysql_result(pod_query("SELECT VERSION()"), 0);
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
?>
    <div class="error fade">
        <p><strong>NOTICE:</strong> Pods <?php echo PODS_VERSION_FULL; ?> requires a minimum of <strong>MySQL <?php echo PODS_MYSQL_VERSION_MINIMUM; ?>+</strong> to function. You are currently running <strong>MySQL <?php echo @mysql_result(pod_query("SELECT VERSION()"), 0); ?></strong> - Please upgrade (or have your Hosting Provider upgrade it for you) your MySQL version to continue.</p>
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