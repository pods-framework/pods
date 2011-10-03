<?php
/**
 * Build the navigation array
 *
 * @param string $uri (optional) The base URL
 * @param int $depth (optional) The maximum recursion depth
 * @since 1.2.0
 */
function pods_nav_array($uri = '<root>', $max_depth = 1) {
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
    if (0 < mysql_num_rows($result)) {
        while ($row = mysql_fetch_assoc($result)) {
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
function pods_navigation($uri = '<root>', $max_depth = 1) {
    $last_depth = -1;

    if ($menu = pods_nav_array($uri, $max_depth)) {
        foreach ($menu as $key => $val) {
            $uri = get_bloginfo('url') . '/' . $val['uri'];
            $title = $val['title'];
            $depth = $val['depth'];
            $diff = ($depth - $last_depth);
            $last_depth = $depth;

            if (0 < $diff) {
                echo '<ul><li>';
            }
            elseif (0 > $diff) {
                for ($i = $diff; $i < 0; $i++) {
                    echo '</li></ul></li>';
                }
                echo '<li>';
            }
            else {
                echo '</li><li>';
            }
            echo "<a href='$uri'>$title</a>";
        }

        for ($i = 0; $i <= $depth; $i++) {
            echo '</li></ul>';
        }
    }
}

if (!function_exists('get_content')) {
    function get_content() {
        return pods_content();
    }
}

function build_nav_array($uri = '<root>', $max_depth = 1) {
    return pods_nav_array($uri, $max_depth);
}