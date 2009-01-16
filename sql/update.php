<?php
if ($installed < 126)
{
    // Add the "required" option
    pod_query("ALTER TABLE {$table_prefix}pod_fields ADD COLUMN required BOOL default 0 AFTER sister_field_id");

    // Add the "label" option
    pod_query("ALTER TABLE {$table_prefix}pod_fields ADD COLUMN label VARCHAR(32) AFTER name");

    // Fix table prefixes
    if (!empty($table_prefix))
    {
        $result = pod_query("SHOW TABLES LIKE 'tbl_%'");

        if (0 < mysql_num_rows($result))
        {
            while ($row = mysql_fetch_array($result))
            {
                pod_query("RENAME TABLE $row[0] TO {$table_prefix}$row[0]");
            }
        }
    }

    // Change the "post_type" of all pod items
    $result = pod_query("SELECT id, name FROM {$table_prefix}pod_types");
    while ($row = mysql_fetch_assoc($result))
    {
        $datatypes[$row['id']] = $row['name'];
    }
    $result = pod_query("SELECT post_id, datatype FROM {$table_prefix}pod");
    while ($row = mysql_fetch_array($result))
    {
        $datatype = $datatypes[$row['datatype']];
        pod_query("UPDATE {$table_prefix}posts SET post_type = '$datatype' WHERE ID = $row[0] LIMIT 1");
    }
}

if ($installed < 127)
{
    // Add the "comment" option
    pod_query("ALTER TABLE {$table_prefix}pod_fields ADD COLUMN comment VARCHAR(128) AFTER label");
}

if ($installed < 131)
{
    $result = pod_query("SHOW TABLES LIKE '{$table_prefix}tbl_%'");

    if (0 < mysql_num_rows($result))
    {
        while ($row = mysql_fetch_array($result))
        {
            $rename = explode('tbl_', $row[0]);
            pod_query("RENAME TABLE $row[0] TO {$table_prefix}pod_tbl_$rename[1]");
        }
    }
}

if ($installed < 132)
{
    pod_query("UPDATE {$table_prefix}pod_pages SET phpcode = CONCAT('<?php\n', phpcode) WHERE phpcode NOT LIKE '?>%'");
    pod_query("UPDATE {$table_prefix}pod_pages SET phpcode = SUBSTR(phpcode, 3) WHERE phpcode LIKE '?>%'");
    pod_query("UPDATE {$table_prefix}pod_widgets SET phpcode = CONCAT('<?php\n', phpcode) WHERE phpcode NOT LIKE '?>%'");
    pod_query("UPDATE {$table_prefix}pod_widgets SET phpcode = SUBSTR(phpcode, 3) WHERE phpcode LIKE '?>%'");
}

// Save this version
update_option('pods_version', $pods_latest);

