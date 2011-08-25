<?php
if (version_compare($installed, '1.6', '<')) {
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
        $row = pods_sanitize($row);
        pod_query("INSERT INTO @wp_pod_templates (name, code) VALUES ('{$row['name']}_detail', '{$row['tpl_detail']}'),('{$row['name']}_list', '{$row['tpl_list']}')");
    }

    // Drop the "tpl_detail" and "tpl_list" columns
    pod_query("ALTER TABLE @wp_pod_types DROP COLUMN tpl_detail, DROP COLUMN tpl_list");

    // Add the "pick_filter" column
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN pick_filter VARCHAR(128) AFTER pickval");
    update_option('pods_version', '160');
}

if (version_compare($installed, '1.6.2', '<')) {
    // Remove all beginning and ending slashes from Pod Pages
    pod_query("UPDATE @wp_pod_pages SET uri = TRIM(BOTH '/' FROM uri)");
    update_option('pods_version', '162');
}

if (version_compare($installed, '1.6.4', '<')) {
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN pick_orderby TEXT AFTER pick_filter");
    pod_query("ALTER TABLE @wp_pod_fields CHANGE helper display_helper TEXT");
    pod_query("ALTER TABLE @wp_pod_fields ADD COLUMN input_helper TEXT AFTER display_helper");
    update_option('pods_version', '164');
}

if (version_compare($installed, '1.6.7', '<')) {
    pod_query("ALTER TABLE @wp_pod_pages ADD COLUMN precode LONGTEXT AFTER phpcode");
    update_option('pods_version', '167');
}

if (version_compare($installed, '1.7.3', '<')) {
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN detail_page VARCHAR(128) AFTER is_toplevel");
    update_option('pods_version', '173');
}

if (version_compare($installed, '1.7.5', '<')) {
    if (empty($pods_roles) && !is_array($pods_roles)) {
        $pods_roles = @unserialize(get_option('pods_roles'));
        if (!is_array($pods_roles))
            $pods_roles = array();
    }
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
    update_option('pods_version', '175');
}

if (version_compare($installed, '1.7.6', '<')) {
    pod_query("ALTER TABLE @wp_pod_types CHANGE label label VARCHAR(128)");
    pod_query("ALTER TABLE @wp_pod_fields CHANGE label label VARCHAR(128)");

    $result = pod_query("SELECT f.id AS field_id, f.name AS field_name, f.datatype AS datatype_id, dt.name AS datatype FROM @wp_pod_fields AS f LEFT JOIN @wp_pod_types AS dt ON dt.id = f.datatype WHERE f.coltype='file'");
    while ($row = mysql_fetch_assoc($result)) {
        $items = pod_query("SELECT t.id AS tbl_row_id, t.{$row['field_name']} AS file, p.id AS pod_id FROM @wp_pod_tbl_{$row['datatype']} AS t LEFT JOIN @wp_pod AS p ON p.tbl_row_id = t.id AND p.datatype = {$row['datatype_id']} WHERE t.{$row['field_name']} != '' AND t.{$row['field_name']} IS NOT NULL");
        $success = false;
        $rels = array();
        while ($item = mysql_fetch_assoc($items)) {
            $filename = $item['file'];
            if(strpos($filename,get_bloginfo('wpurl'))!==false&&strpos($filename,get_bloginfo('wpurl'))==0) {
                $filename = ltrim($filename,get_bloginfo('wpurl'));
            }
            $upload_dir = wp_upload_dir();
            if(strpos($filename,str_replace(get_bloginfo('wpurl'),'',$upload_dir['baseurl']))===false) {
                $success = false;
                break;
            }
            $file = str_replace('//','/',(ABSPATH.$filename));
            $wp_filetype = wp_check_filetype(basename($file), null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($file)),
                'guid' => str_replace('//wp-content','/wp-content',get_bloginfo('wpurl').$filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $file, 0 );
            if($attach_id>0) {
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                wp_update_attachment_metadata( $attach_id,  $attach_data );
                $sizes = array('thumb','medium','large');
                foreach($sizes as $size) {
                    image_downsize( $attach_id, $size );
                }
                $rels[] = array('pod_id'=>$item['pod_id'],'tbl_row_id'=>$item['tbl_row_id'],'attach_id'=>$attach_id,'field_id'=>$row['field_id']);
                $success = true;
            }
        }
        if(false!==$success) {
            foreach($rels as $rel) {
                pod_query("INSERT INTO @wp_pod_rel (pod_id, field_id, tbl_row_id) VALUES({$rel['pod_id']}, {$rel['field_id']}, {$rel['attach_id']})");
            }
            pod_query("ALTER TABLE @wp_pod_tbl_{$row['datatype']} DROP COLUMN {$row['field_name']}");
        }
        else {
            pod_query("UPDATE @wp_pod_fields SET coltype = 'txt' WHERE id = {$row['field_id']}");
        }
    }
    update_option('pods_version', '176');
}

if (version_compare($installed, '1.8.1', '<')) {
    pod_query("ALTER TABLE @wp_pod_rel ADD COLUMN weight SMALLINT unsigned AFTER tbl_row_id");
    pod_query("ALTER TABLE @wp_pod_types CHANGE before_helpers pre_save_helpers TEXT");
    pod_query("ALTER TABLE @wp_pod_types CHANGE after_helpers post_save_helpers TEXT");
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN pre_drop_helpers TEXT AFTER pre_save_helpers");
    pod_query("ALTER TABLE @wp_pod_types ADD COLUMN post_drop_helpers TEXT AFTER post_save_helpers");
    pod_query("UPDATE @wp_pod_helpers SET helper_type = 'pre_save' WHERE helper_type = 'before'");
    pod_query("UPDATE @wp_pod_helpers SET helper_type = 'post_save' WHERE helper_type = 'after'");
    update_option('pods_version', '181');
}

if (version_compare($installed, '1.8.2', '<')) {
    pod_query("ALTER TABLE @wp_pod ADD COLUMN author_id INT unsigned AFTER modified");
    pod_query("UPDATE @wp_pod_fields SET pickval = 'wp_taxonomy' WHERE pickval REGEXP '^[0-9]+$'");
    pod_query("UPDATE @wp_pod_menu SET uri = '<root>' WHERE uri = '/' LIMIT 1");

    // Remove beginning and trailing slashes
    $result = pod_query("SELECT id, uri FROM @wp_pod_menu");
    while ($row = mysql_fetch_assoc($result)) {
        $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $row['uri']);
        $uri = pods_sanitize($uri);
        pod_query("UPDATE @wp_pod_menu SET uri = '$uri' WHERE id = {$row['id']} LIMIT 1");
    }
    update_option('pods_version', '182');
}

if (version_compare($installed, '1.9.0', '<')) {
    pod_query("ALTER TABLE @wp_pod_templates CHANGE `name` `name` VARCHAR(255)");
    pod_query("ALTER TABLE @wp_pod_helpers CHANGE `name` `name` VARCHAR(255)");
    pod_query("ALTER TABLE @wp_pod_fields CHANGE `comment` `comment` VARCHAR(255)");

    // Remove beginning and trailing slashes
    $result = pod_query("SELECT id, uri FROM @wp_pod_pages");
    while ($row = mysql_fetch_assoc($result)) {
        $uri = trim($row['uri'],'/');
        $uri = pods_sanitize($uri);
        pod_query("UPDATE @wp_pod_pages SET uri = '$uri' WHERE id = {$row['id']} LIMIT 1");
    }
    update_option('pods_version', '190');
}

if (version_compare($installed, '1.9.6', '<')) {
    add_option('pods_disable_file_browser', 0);
    add_option('pods_files_require_login', 0);
    add_option('pods_files_require_login_cap', 'upload_files');
    add_option('pods_disable_file_upload', 0);
    add_option('pods_upload_require_login', 0);
    add_option('pods_upload_require_login_cap', 'upload_files');
    update_option('pods_version', '196');
}

if (version_compare($installed, '1.9.7', '<')) {
    pod_query("ALTER TABLE `@wp_pod` CHANGE `id` `id` BIGINT(15) UNSIGNED NOT NULL AUTO_INCREMENT");
    pod_query("ALTER TABLE `@wp_pod` CHANGE `tbl_row_id` `tbl_row_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL");
    pod_query("ALTER TABLE `@wp_pod` CHANGE `author_id` `author_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL");
    pod_query("ALTER TABLE `@wp_pod_rel` CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
    pod_query("ALTER TABLE `@wp_pod_rel` CHANGE `pod_id` `pod_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL");
    pod_query("ALTER TABLE `@wp_pod_rel` CHANGE `sister_pod_id` `sister_pod_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL");
    pod_query("ALTER TABLE `@wp_pod_rel` CHANGE `tbl_row_id` `tbl_row_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL");
    pod_query("ALTER TABLE `@wp_pod_rel` CHANGE `weight` `weight` INT(10) UNSIGNED NULL DEFAULT '0'");
    update_option('pods_version', '197');
}

if (version_compare($installed, '1.11', '<')) {
    pod_query("ALTER TABLE `@wp_pod` CHANGE `datatype` `datatype` INT(10) UNSIGNED NULL DEFAULT NULL");
    pod_query("ALTER TABLE `@wp_pod` DROP INDEX `datatype_idx`", false);
    pod_query("ALTER TABLE `@wp_pod` ADD INDEX `datatype_row_idx` (`datatype`, `tbl_row_id`)", false);
    pod_query("ALTER TABLE `@wp_pod_rel` DROP INDEX `field_id_idx`", false);
    pod_query("ALTER TABLE `@wp_pod_rel` ADD INDEX `field_pod_idx` (`field_id`, `pod_id`)", false);
    pod_query("ALTER TABLE `@wp_pod_fields` CHANGE `datatype` `datatype` INT(10) UNSIGNED NULL DEFAULT NULL");
    $result = pod_query("SELECT id, name FROM @wp_pod_types");
    while ($row = mysql_fetch_assoc($result)) {
        $pod = pods_sanitize($row['name']);
        pod_query("ALTER TABLE `@wp_pod_tbl_{$pod}` CHANGE `id` `id` BIGINT(15) UNSIGNED NOT NULL AUTO_INCREMENT");
    }
    update_option('pods_version', '001011000');
}

// Save this version
update_option('pods_version', PODS_VERSION);