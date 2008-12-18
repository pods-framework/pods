<?php
if ($installed < $latest)
{
    if ($installed < 126)
    {
        // Add the "required" option
        mysql_query("ALTER TABLE {$table_prefix}pod_fields ADD COLUMN required BOOL default 0 AFTER sister_field_id");

        // Add the "label" option
        mysql_query("ALTER TABLE {$table_prefix}pod_fields ADD COLUMN label VARCHAR(32) AFTER name");

        // Fix table prefixes
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

        // Change the "post_type" of all pod items
        $result = mysql_query("SELECT id, name FROM {$table_prefix}pod_types");
        while ($row = mysql_fetch_assoc($result))
        {
            $datatypes[$row['id']] = $row['name'];
        }
        $result = mysql_query("SELECT post_id, datatype FROM {$table_prefix}pod");
        while ($row = mysql_fetch_array($result))
        {
            $datatype = $datatypes[$row['datatype']];
            mysql_query("UPDATE {$table_prefix}posts SET post_type = '$datatype' WHERE ID = $row[0] LIMIT 1");
        }
    }

    if ($installed < 127)
    {
        // Add the "comment" option
        mysql_query("ALTER TABLE {$table_prefix}pod_fields ADD COLUMN comment VARCHAR(128) AFTER label");
    }

    if ($installed < 131)
    {
        $result = mysql_query("SHOW TABLES LIKE '{$table_prefix}tbl_%'");

        if (0 < mysql_num_rows($result))
        {
            while ($row = mysql_fetch_array($result))
            {
                $rename = explode('tbl_', $row[0]);
                mysql_query("RENAME TABLE $row[0] TO {$table_prefix}pod_tbl_$rename[1]");
            }
        }
    }

    // Save this version
    mysql_query("INSERT INTO {$table_prefix}options (option_name, option_value) VALUES ('pods_version', '$latest')");
}
