<?php
// Include the MySQL connection
require_once(realpath('../../../../wp-config.php'));

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

$result = pod_query("SELECT id, uri, title from @wp_pod_pages WHERE uri LIKE '%$q%' ORDER BY uri ASC");
if (0 < mysql_num_rows($result))
{
    while ($row = mysql_fetch_assoc($result))
    {
        $matches[] = $row['uri'] . '<span class="hidden">||' . $row['id'] . '||' . $row['title'] . '</span>';
    }
    echo implode("\n", $matches);
}
