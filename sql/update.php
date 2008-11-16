<?php
$result = mysql_query("SHOW COLUMNS FROM wp_pod_fields LIKE 'required'");

if (1 > mysql_num_rows($result))
{
    mysql_query("ALTER TABLE wp_pod_fields ADD COLUMN required BOOL default 0 AFTER sister_field_id");
}

$result = mysql_query("SHOW COLUMNS FROM wp_pod_fields LIKE 'label'");

if (1 > mysql_num_rows($result))
{
    mysql_query("ALTER TABLE wp_pod_fields ADD COLUMN label VARCHAR(32) AFTER name");
}

