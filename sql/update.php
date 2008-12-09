<?php
$result = mysql_query("SHOW COLUMNS FROM {$table_prefix}pod_fields LIKE 'required'");

if (1 > mysql_num_rows($result))
{
    mysql_query("ALTER TABLE {$table_prefix}pod_fields ADD COLUMN required BOOL default 0 AFTER sister_field_id");
}

$result = mysql_query("SHOW COLUMNS FROM {$table_prefix}pod_fields LIKE 'label'");

if (1 > mysql_num_rows($result))
{
    mysql_query("ALTER TABLE {$table_prefix}pod_fields ADD COLUMN label VARCHAR(32) AFTER name");
}

if (!empty($table_prefix))
{
    $result = mysql_query("SHOW TABLES LIKE 'tbl_%'");

    if (0 < mysql_num_rows($result))
    {
        while ($row = mysql_fetch_array($result))
        {
            mysql_query("RENAME TABLE $row[0] TO {$table_prefix}$row[0]");
        }
    }
}

