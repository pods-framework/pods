<?php
if ($installed < 126)
{
    // Add the "required" option
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN required BOOL default 0 AFTER sister_field_id");

    // Add the "label" option
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN label VARCHAR(32) AFTER name");

    // Fix table prefixes
    if (!empty($table_prefix))
    {
        $result = pod_query("SHOW TABLES LIKE 'tbl_%'");

        if (0 < mysql_num_rows($result))
        {
            while ($row = mysql_fetch_array($result))
            {
                pod_query("RENAME TABLE $row[0] TO @wp_$row[0]");
            }
        }
    }

    // Change the "post_type" of all pod items
    $result = pod_query("SELECT id, name FROM @wp_pod_types");
    while ($row = mysql_fetch_assoc($result))
    {
        $datatypes[$row['id']] = $row['name'];
    }
    $result = pod_query("SELECT post_id, datatype FROM @wp_pod");
    while ($row = mysql_fetch_array($result))
    {
        $datatype = $datatypes[$row['datatype']];
        pod_query("UPDATE @wp_posts SET post_type = '$datatype' WHERE ID = $row[0] LIMIT 1");
    }
}

if ($installed < 127)
{
    // Add the "comment" option
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN comment VARCHAR(128) AFTER label");
}

if ($installed < 131)
{
    $result = pod_query("SHOW TABLES LIKE '@wp_tbl_%'");

    if (0 < mysql_num_rows($result))
    {
        while ($row = mysql_fetch_array($result))
        {
            $rename = explode('tbl_', $row[0]);
            pod_query("RENAME TABLE $row[0] TO @wp_pod_tbl_$rename[1]");
        }
    }
}

if ($installed < 132)
{
    pod_query("UPDATE @wp_pod_pages SET phpcode = CONCAT('<?php\n', phpcode) WHERE phpcode NOT LIKE '?>%'");
    pod_query("UPDATE @wp_pod_pages SET phpcode = SUBSTR(phpcode, 3) WHERE phpcode LIKE '?>%'");
    pod_query("UPDATE @wp_pod_widgets SET phpcode = CONCAT('<?php\n', phpcode) WHERE phpcode NOT LIKE '?>%'");
    pod_query("UPDATE @wp_pod_widgets SET phpcode = SUBSTR(phpcode, 3) WHERE phpcode LIKE '?>%'");
}

if ($installed < 143)
{
    $result = pod_query("SHOW COLUMNS FROM @wp_pod_types LIKE 'description'");
    if (0 < mysql_num_rows($result))
    {
        pod_query("ALTER TABLE @wp_pod_types CHANGE description label VARCHAR(32)");
    }
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN is_toplevel BOOL default 0 AFTER label");
}

if ($installed < 145)
{
    pod_query("ALTER TABLE @wp_pod_pages ADD COLUMN title VARCHAR(128) AFTER uri");
    $sql = "
    CREATE TABLE @wp_pod_menu (
        id INT unsigned auto_increment primary key,
        uri VARCHAR(128),
        title VARCHAR(128),
        lft INT unsigned,
        rgt INT unsigned,
        weight TINYINT unsigned default 0)";
    pod_query($sql);
    pod_query("INSERT INTO @wp_pod_menu (uri, title, lft, rgt) VALUES ('/', 'Home', 1, 2)");
}

if ($installed < 148)
{
    add_option('pods_roles');
}

if ($installed < 149)
{
    pod_query("RENAME TABLE @wp_pod_widgets TO @wp_pod_helpers");
}

if ($installed < 150)
{
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN `unique` BOOL default 0 AFTER required");
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN `multiple` BOOL default 0 AFTER `unique`");
    pod_query("ALTER TABLE @wp_pod_pages ADD COLUMN page_template VARCHAR(128) AFTER phpcode");
    pod_query("ALTER TABLE @wp_pod_helpers ADD COLUMN helper_type VARCHAR(16) AFTER name");
    pod_query("ALTER TABLE @wp_pod ADD COLUMN name VARCHAR(128) AFTER datatype");
    pod_query("ALTER TABLE @wp_pod ADD COLUMN created VARCHAR(128) AFTER name");
    pod_query("ALTER TABLE @wp_pod ADD COLUMN modified VARCHAR(128) AFTER created");
    pod_query("ALTER TABLE @wp_pod CHANGE row_id tbl_row_id INT unsigned");
    pod_query("ALTER TABLE @wp_pod_rel CHANGE term_id tbl_row_id INT unsigned");
    pod_query("ALTER TABLE @wp_pod_rel CHANGE post_id pod_id INT unsigned");
    pod_query("ALTER TABLE @wp_pod_rel CHANGE sister_post_id sister_pod_id INT unsigned");

    // Make all pick columns "multiple" for consistency
    pod_query("UPDATE @wp_pod_fields SET `multiple` = 1 WHERE coltype = 'pick'");

    // Use "display" as the default helper type
    pod_query("UPDATE @wp_pod_helpers SET helper_type = 'display'");

    // Replace all post_ids with its associated pod_id
    $sql = "
    SELECT
        p.id, p.post_id, r.post_title AS name, r.post_date AS created, r.post_modified AS modified
    FROM
        @wp_pod p
    INNER JOIN
        @wp_posts r ON r.ID = p.post_id
    ";
    $result = pod_query($sql);
    while ($row = mysql_fetch_assoc($result))
    {
        foreach ($row as $key => $val)
        {
            ${$key} = mysql_real_escape_string(trim($val));
        }
        $posts_to_delete[] = $post_id;
        $all_pod_ids[$post_id] = $id;
        pod_query("UPDATE @wp_pod SET name = '$name', created = '$created', modified = '$modified' WHERE id = $id LIMIT 1");
    }

    // Replace post_id with pod_id
    $result = pod_query("SELECT id, pod_id, sister_pod_id FROM @wp_pod_rel");
    while ($row = mysql_fetch_assoc($result))
    {
        $id = $row['id'];
        $new_pod_id = $all_pod_ids[$row['pod_id']];
        $new_sister_pod_id = $all_pod_ids[$row['sister_pod_id']];
        pod_query("UPDATE @wp_pod_rel SET pod_id = '$new_pod_id', sister_pod_id = '$new_sister_pod_id' WHERE id = '$id' LIMIT 1");
    }

    $posts_to_delete = implode(',', $posts_to_delete);

    // Remove all traces from wp_posts
    pod_query("ALTER TABLE @wp_pod DROP COLUMN post_id");
    pod_query("DELETE FROM @wp_posts WHERE ID IN ($posts_to_delete)");
}

if ($installed < 151)
{
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN helper VARCHAR(32) AFTER label");
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN before_helpers TEXT AFTER tpl_list");
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN after_helpers TEXT AFTER before_helpers");
}

// Save this version
update_option('pods_version', $pods_latest);

