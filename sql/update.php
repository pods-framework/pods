<?php
if ($installed < 160) {
    // Add the "templates" table
    $sql = "
    CREATE TABLE @wp_pod_templates (
        id INT unsigned auto_increment primary key,
        name VARCHAR(32),
        code TEXT)";
    pod_query($sql);

    // Add list and detail template presets
    $tpl_list = '<p><a href="{@detail_url}">{@name}</a></p>';
    $tpl_detail = "<h2>{@name}</h2>\n{@body}";
    pod_query("INSERT INTO @wp_pod_templates (name, code) VALUES ('detail', '$tpl_detail'),('list', '$tpl_list')");

    // Try to route old templates as best as possible
    $result = pod_query("SELECT name, tpl_detail, tpl_list FROM @wp_pod_types");
    while ($row = mysql_fetch_assoc($result)) {
        // Create the new template, e.g. "dtname_list" or "dtname_detail"
        foreach ($row as $key => $val) {
            ${$key} = mysql_real_escape_string($val);
        }
        pod_query("INSERT INTO @wp_pod_templates (name, code) VALUES ('{$name}_detail', '$tpl_detail'),('{$name}_list', '$tpl_list')");
    }

    // Drop the "tpl_detail" and "tpl_list" columns
    pod_query("ALTER TABLE @wp_pod_types DROP COLUMN tpl_detail, DROP COLUMN tpl_list");

    // Add the "pick_filter" column
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN pick_filter VARCHAR(128) AFTER pickval");
}

if ($installed < 162) {
    // Remove all beginning and ending slashes from Pod Pages
    pod_query("UPDATE @wp_pod_pages SET uri = TRIM(BOTH '/' FROM uri)");
}

if ($installed < 164) {
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN pick_orderby TEXT AFTER pick_filter");
    pod_query("ALTER TABLE @wp_pod_fields CHANGE helper display_helper TEXT");
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN input_helper TEXT AFTER display_helper");
}

if ($installed < 167) {
    pod_query("ALTER TABLE @wp_pod_pages ADD COLUMN precode LONGTEXT AFTER phpcode");
}

if ($installed < 173) {
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN detail_page VARCHAR(128) AFTER is_toplevel");
}

if ($installed < 175) {
    if (is_array($pods_roles)) {
        foreach ($pods_roles as $role => $privs) {
            if (in_array('manage_podpages', $privs)) {
                $pods_roles[$role][] = 'manage_pod_pages';
                unset($pods_roles[$role]['manage_podpages']);
            }
        }
    }
    delete_option('pods_roles');
    add_option('pods_roles', serialize($pods_roles));
}

if ($installed < 176) {
    pod_query("ALTER TABLE @wp_pod_types CHANGE label label VARCHAR(128)");
    pod_query("ALTER TABLE @wp_pod_fields CHANGE label label VARCHAR(128)");
    pod_query("UPDATE @wp_pod_fields SET coltype = 'txt' WHERE coltype = 'file'");
}

if ($installed < 181) {
    pod_query("ALTER TABLE @wp_pod_rel ADD COLUMN weight SMALLINT unsigned AFTER tbl_row_id");
    pod_query("ALTER TABLE @wp_pod_types CHANGE before_helpers pre_save_helpers TEXT");
    pod_query("ALTER TABLE @wp_pod_types CHANGE after_helpers post_save_helpers TEXT");
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN pre_drop_helpers TEXT AFTER pre_save_helpers");
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN post_drop_helpers TEXT AFTER post_save_helpers");
    pod_query("UPDATE @wp_pod_helpers SET helper_type = 'pre_save' WHERE helper_type = 'before'");
    pod_query("UPDATE @wp_pod_helpers SET helper_type = 'post_save' WHERE helper_type = 'after'");
}

if ($installed < 182) {
    pod_query("ALTER TABLE @wp_pod ADD COLUMN author_id INT unsigned AFTER modified");
    pod_query("UPDATE @wp_pod_fields SET pickval = 'wp_taxonomy' WHERE pickval REGEXP '^[0-9]+$'");
    pod_query("UPDATE @wp_pod_menu SET uri = '<root>' WHERE uri = '/' LIMIT 1");

    // Remove beginning and trailing slashes
    $result = pod_query("SELECT id, uri FROM @wp_pod_menu");
    while ($row = mysql_fetch_assoc($result)) {
        $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $row['uri']);
        $uri = mysql_real_escape_string($uri);
        pod_query("UPDATE @wp_pod_menu SET uri = '$uri' WHERE id = {$row['id']} LIMIT 1");
    }
}

// Save this version
update_option('pods_version', PODS_VERSION);
