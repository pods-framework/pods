<?php
// Include the MySQL connection
require_once(realpath('../../../../wp-config.php'));

$rows_per_page = 10000;
$search = mysql_real_escape_string(trim($_POST['search']));
$search = empty($search) ? '' : "AND guid LIKE '%$search%'";
$page = empty($_POST['pg']) ? 1 : (int) $_POST['pg'];
$limit = ($rows_per_page * ($page - 1)) . ', ' . $rows_per_page;

$sql = "
SELECT
    SQL_CALC_FOUND_ROWS id, guid
FROM
    @wp_posts
WHERE
    post_type = 'attachment'
    $search
ORDER BY
    guid ASC
LIMIT
    $limit
";

$result = pod_query($sql);
$total_rows = pod_query("SELECT FOUND_ROWS()");
$total_rows = mysql_result($total_rows, 0);
$total_pages = ceil($total_rows / $rows_per_page);

if (0 < mysql_num_rows($result))
{
    while ($row = mysql_fetch_assoc($result))
    {
        $guid = substr($row['guid'], strrpos($row['guid'], '/') + 1);
?>
    <div class="file_match" rel="<?php echo $row['id']; ?>"><?php echo $guid; ?></div>
<?php
    }
}
else
{
    echo 'Nothing found.';
}
