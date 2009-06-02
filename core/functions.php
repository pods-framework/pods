<?php
/*
==================================================
Standardize queries and error reporting

$sql                SQL query
$error              SQL failure message
$results_error      Triggered when results > 0
$no_results_error   Triggered when results = 0
==================================================
*/
function pod_query($sql, $error = 'SQL failed', $results_error = null, $no_results_error = null)
{
    global $table_prefix;

    $sql = str_replace('@wp_', $table_prefix, $sql);

    $result = mysql_query($sql) or die("Error: $error; SQL: $sql; Response: " . mysql_error());
    if (0 < @mysql_num_rows($result))
    {
        if (!empty($results_error))
        {
            die("Error: $results_error");
        }
    }
    else
    {
        if (!empty($no_results_error))
        {
            die("Error: $no_results_error");
        }
    }

    if ('INSERT' == substr(trim($sql), 0, 6))
    {
        $result = mysql_insert_id();
    }
    return $result;
}

/*
==================================================
Return a lowercase alphanumeric name (with underscores)
==================================================
*/
function pods_clean_name($name)
{
    $name = preg_replace("/([- ])/", "_", $name);
    $name = preg_replace("/([^0-9a-z_])/", "", strtolower($name));
    return $name;
}

/*
==================================================
Return either a GET var or URI string segment
==================================================
*/
function pods_url_variable($key = 'last', $type = 'uri')
{
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
            if (0 > $key)
            {
                return $uri[count($uri)+$key];
            }
            else
            {
                return $uri[$key];
            }
        }
    }
    elseif ('get' == strtolower($type))
    {
        return $_GET[$key];
    }
    return false;
}

/*
==================================================
Filter input. Escape output.
==================================================
*/
function pods_sanitize($input)
{
    $output = array();

    if (is_object($input))
    {
        $input = get_object_vars($input);
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
    else
    {
        $output = mysql_real_escape_string(trim($input));
    }
    return $output;
}

/*
==================================================
Access control
==================================================
*/
function pods_access($priv)
{
    global $pods_roles, $current_user;

    if (in_array('administrator', $current_user->roles))
    {
        return true;
    }

    // Loop through the user's roles
    foreach ($pods_roles as $role => $privs)
    {
        if (false !== array_search($priv, $privs))
        {
            return true;
        }
    }
    return false;
}

/*
==================================================
Build the navigation array
==================================================
*/
function build_nav_array($uri = '/', $max_depth = 1)
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

/*
==================================================
Build the HTML navigation
==================================================
*/
function pods_navigation($uri = '/', $max_depth = 1)
{
    $last_depth = -1;

    if ($menu = build_nav_array($uri, $max_depth))
    {
        foreach ($menu as $key => $val)
        {
            $uri = $val['uri'];
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

