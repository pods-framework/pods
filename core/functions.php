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
function pod_query($sql, $error = 'SQL failed', $results_error = null, $no_results_error = null)
{
    global $wpdb;

    $sql = str_replace('@wp_users', $wpdb->users, $sql);
    $sql = str_replace('@wp_', $wpdb->prefix, $sql);
    $sql = str_replace('{prefix}', '@wp_', $sql);

    // Returned cached resultset
    if ('SELECT' == substr(trim($sql), 0, 6))
    {
        $cache = PodCache::instance();
        if ($cache->cache_enabled && isset($cache->results[$sql]))
        {
            $result = $cache->results[$sql];
            if (0 < mysql_num_rows($result))
            {
                mysql_data_seek($result, 0);
            }
            return $result;
        }
    }

    $result = mysql_query($sql, $wpdb->dbh) or die("Error: $error; SQL: $sql; Response: " . mysql_error($wpdb->dbh));

    if (0 < @mysql_num_rows($result))
    {
        if (!empty($results_error))
        {
            die("Error: $results_error");
        }
    }
    elseif (!empty($no_results_error))
    {
        die("Error: $no_results_error");
    }

    if ('INSERT' == substr(trim($sql), 0, 6))
    {
        $result = mysql_insert_id($wpdb->dbh);
    }
    elseif ('SELECT' == substr(trim($sql), 0, 6))
    {
        if ('SELECT FOUND_ROWS()' != $sql)
        {
            $cache->results[$sql] = $result;
        }
    }
    return $result;
}

/**
 * Return a lowercase alphanumeric name (with underscores)
 *
 * @param string $name Input string to clean
 * @since 1.2.0
 */
function pods_clean_name($name)
{
    $name = preg_replace("/([- ])/", "_", $name);
    $name = preg_replace("/(_){2,}/", "_", $name);
    $name = preg_replace("/([^0-9a-z_])/", "", strtolower($name));
    return $name;
}

/**
 * Return a GET, POST, COOKIE, SESSION, or URI string segment
 *
 * @param mixed $key The variable name or URI segment position
 * @param string $type (optional) "uri", "get", "post", "cookie", or "session"
 * @return string The requested value, or null
 * @since 1.6.2
 */
function pods_url_variable($key = 'last', $type = 'uri')
{
    $output = false;
    if ('uri' == strtolower($type))
    {
        $uri = explode('?', $_SERVER['REQUEST_URI']);
        $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
        $uri = explode('/', $uri);

        if ('first' == $key)
        {
            $key = 0;
        }
        elseif ('last' == $key)
        {
            $key = -1;
        }

        if (is_numeric($key))
        {
            $output = (0 > $key) ? $uri[count($uri)+$key] : $uri[$key];
        }
    }
    elseif ('get' == strtolower($type))
    {
        $output = isset($_GET[$key]) ? $_GET[$key] : null;
    }
    elseif ('post' == strtolower($type))
    {
        $output = isset($_POST[$key]) ? $_POST[$key] : null;
    }
    elseif ('session' == strtolower($type))
    {
        $output = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    return pods_sanitize($output);
}

/**
 * Filter input. Escape output.
 *
 * @param mixed $input The string, array, or object to sanitize
 * @since 1.2.0
 */
function pods_sanitize($input)
{
    $output = array();

    if (is_object($input))
    {
        $input = (array) $input;
        foreach ($input as $key => $val)
        {
            $output[$key] = pods_sanitize($val);
        }
        $output = (object) $output;
    }
    elseif (is_array($input))
    {
        foreach ($input as $key => $val)
        {
            $output[$key] = pods_sanitize($val);
        }
    }
    elseif (empty($input))
    {
        $output = $input;
    }
    else
    {
        // Rename @wp_ to prevent pod_query() from replacing it
        $input = str_replace('@wp_', '{prefix}', $input);
        $output = mysql_real_escape_string(trim($input));
    }
    return $output;
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
function pods_unique_slug($value, $column_name, $datatype, $datatype_id, $pod_id = 0)
{
    $value = sanitize_title($value);
    $sql = "
    SELECT DISTINCT
        t.`$column_name` AS slug
    FROM
        @wp_pod p
    INNER JOIN
        `@wp_pod_tbl_{$datatype}` t ON t.id = p.tbl_row_id
    WHERE
        p.datatype = '$datatype_id' AND p.id != '$pod_id'
    ";
    $result = pod_query($sql);
    if (0 < mysql_num_rows($result))
    {
        $unique_num = 0;
        $unique_found = false;
        while ($row = mysql_fetch_assoc($result))
        {
            $taken_slugs[] = $row['slug'];
        }
        if (in_array($value, $taken_slugs))
        {
            while (!$unique_found)
            {
                $unique_num++;
                $test_slug = $value . '-' . $unique_num;
                if (!in_array($test_slug, $taken_slugs))
                {
                    $value = $test_slug;
                    $unique_found = true;
                }
            }
        }
    }
    return $value;
}

/**
 * See if the current user has a certain privilege
 *
 * @param string $priv The privilege name
 * @return bool
 * @since 1.2.0
 */
function pods_access($priv)
{
    global $pods_roles, $current_user;

    // Usage: add_filter('pods_access', array('manage_roles' => false));
    if ($hook_privs = apply_filters('pods_access', false))
    {
        if (isset($hook_privs[$priv]))
        {
            // The string "false" should equal boolean false
            return ('false' == $hooks[$priv]) ? false : (bool) $hooks[$priv];
        }
    }

    if (in_array('administrator', $current_user->roles))
    {
        return true;
    }

    // Loop through the user's roles
    if (is_array($pods_roles))
    {
        foreach ($pods_roles as $role => $privs)
        {
            if (in_array($role, $current_user->roles) && false !== array_search($priv, $privs))
            {
                return true;
            }
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
function pods_shortcode($tags)
{
    $pairs = array('name' => null, 'id' => null, 'slug' => null, 'order' => 'id DESC', 'limit' => 15, 'where' => null, 'col' => null, 'template' => null, 'helper' => null);
    $tags = shortcode_atts($pairs, $tags);

    if (empty($tags['name']))
    {
        return 'Error: Please provide a Pod name';
    }
    if (empty($tags['template']) && empty($tags['col']))
    {
        return 'Error: Please provide either a template or column name';
    }

    // id > slug (if both exist)
    $id = empty($tags['slug']) ? null : $tags['slug'];
    $id = empty($tags['id']) ? $id : $tags['id'];

    $order = empty($tags['order']) ? 'id DESC' : $tags['order'];
    $limit = empty($tags['limit']) ? 15 : $tags['limit'];
    $where = empty($tags['where']) ? null : $tags['where'];

    $Record = new Pod($tags['name'], $id);

    if (empty($id))
    {
        $Record->findRecords($order, $limit, $where);
    }
    if (!empty($tags['col']) && !empty($id))
    {
        $val = $Record->get_field($tags['col']);
        return empty($tags['helper']) ? $val : $Record->pod_helper($tags['helper'], $val);
    }
    return $Record->showTemplate($tags['template']);
}

/**
 * Generate form key - INTERNAL USE
 *
 * @since 1.2.0
 */
function pods_generate_key($datatype, $uri_hash, $public_columns, $form_count = 1)
{
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
function pods_validate_key($key, $uri_hash, $datatype, $form_count = 1)
{
    if (!empty($_SESSION[$uri_hash]))
    {
        $session_dt = $_SESSION[$uri_hash][$form_count]['dt'];
        $session_token = $_SESSION[$uri_hash][$form_count]['token'];
        if (!empty($session_token) && $key == $session_token && $datatype == $session_dt)
        {
            return true;
        }
    }
    return false;
}

/**
 * Translation support
 *
 * @param string $string The string to translate
 * @return string The translated string, or the original string
 * @since 1.7.0
 */
function pods_i18n($string)
{
    global $lang;

    if (isset($lang[$string]))
    {
        $string = $lang[$string];
    }
    return $string;
}

/**
 * Find out if the current page is a Pod Page
 *
 * @return bool
 * @since 1.7.5
 */
function is_pod_page()
{
    global $pod_page_exists;

    return (false !== $pod_page_exists) ? true : false;
}

/**
 * Build the navigation array
 *
 * @param string $uri (optional) The base URL
 * @param int $depth (optional) The maximum recursion depth
 * @since 1.2.0
 */
function pods_nav_array($uri = '<root>', $max_depth = 1)
{
    $having = (0 < $max_depth) ? "HAVING depth <= $max_depth" : '';

    $sql = "
    SELECT
        node.id, node.uri, node.title, (COUNT(parent.title) - (sub_tree.depth + 1)) AS depth
    FROM
        @wp_pod_menu AS node,
        @wp_pod_menu AS parent,
        @wp_pod_menu AS sub_parent,
        (
            SELECT
                node.uri, (COUNT(parent.uri) - 1) AS depth
            FROM
                @wp_pod_menu AS node,
                @wp_pod_menu AS parent
            WHERE
                node.lft BETWEEN parent.lft AND parent.rgt AND
                node.uri = '$uri'
            GROUP BY
                node.uri
            ORDER BY
                node.lft
        ) AS sub_tree
    WHERE
        node.lft BETWEEN parent.lft AND parent.rgt AND
        node.lft BETWEEN sub_parent.lft AND sub_parent.rgt AND
        sub_parent.uri = sub_tree.uri
    GROUP BY
        node.uri
    $having
    ORDER BY
        node.weight, node.lft
    ";
    $result = pod_query($sql);
    if (0 < mysql_num_rows($result))
    {
        while ($row = mysql_fetch_assoc($result))
        {
            $menu[] = $row;
        }
        return $menu;
    }
    return false;
}

/**
 * Build the HTML navigation
 *
 * @param string $uri (optional) The base URL
 * @param int $depth (optional) The maximum recursion depth
 * @since 1.2.0
 */
function pods_navigation($uri = '<root>', $max_depth = 1)
{
    $last_depth = -1;

    if ($menu = pods_nav_array($uri, $max_depth))
    {
        foreach ($menu as $key => $val)
        {
            $uri = get_bloginfo('url') . '/' . $val['uri'];
            $title = $val['title'];
            $depth = $val['depth'];
            $diff = ($depth - $last_depth);
            $last_depth = $depth;

            if (0 < $diff)
            {
                echo '<ul><li>';
            }
            elseif (0 > $diff)
            {
                for ($i = $diff; $i < 0; $i++)
                {
                    echo '</li></ul></li>';
                }
                echo '<li>';
            }
            else
            {
                echo '</li><li>';
            }
            echo "<a href='$uri'>$title</a>";
        }

        for ($i = 0; $i <= $depth; $i++)
        {
            echo '</li></ul>';
        }
    }
}

// Deprecated
if (!function_exists('get_content'))
{
    function get_content()
    {
        return pods_content();
    }
}

function build_nav_array($uri = '<root>', $max_depth = 1)
{
    return pods_nav_array($uri, $max_depth);
}
