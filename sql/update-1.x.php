<?php
/**
 * @package Pods\Upgrade
 */
global $wpdb;

if ( version_compare( $old_version, '1.2.6', '<' ) ) {
	// Add the "required" option
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN required BOOL default 0 AFTER sister_field_id' );

	// Add the "label" option
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN label VARCHAR(32) AFTER name' );

	// Fix table prefixes
	if ( ! empty( $table_prefix ) ) {
		$result = $wpdb->get_results( "SHOW TABLES LIKE 'tbl_%'", ARRAY_N );

		foreach ( $result as $row ) {
			pods_query( "RENAME TABLE `{$row[0]}` TO `@wp_{$row[0]}`" );
		}
	}

	// Change the "post_type" of all pod items
	$result = pods_query( 'SELECT id, name FROM @wp_pod_types' );

	foreach ( $result as $row ) {
		$datatypes[ $row->id ] = $row->name;
	}

	$result = pods_query( 'SELECT post_id, datatype FROM @wp_pod' );

	foreach ( $result as $row ) {
		$datatype = $datatypes[ $row->datatype ];

		pods_query( "UPDATE @wp_posts SET post_type = '$datatype' WHERE ID = $row[0] LIMIT 1" );
	}

	update_option( 'pods_version', '126' );
}//end if

if ( version_compare( $old_version, '1.2.7', '<' ) ) {
	// Add the "comment" option
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN comment VARCHAR(128) AFTER label' );

	update_option( 'pods_version', '127' );
}

if ( version_compare( $old_version, '1.3.1', '<' ) ) {
	$result = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}tbl_%'", ARRAY_N );

	foreach ( $result as $row ) {
		$rename = explode( 'tbl_', $row[0] );
		pods_query( "RENAME TABLE `{$row[0]}` TO `@wp_pod_tbl_{$rename[1]}`" );
	}

	update_option( 'pods_version', '131' );
}

if ( version_compare( $old_version, '1.3.2', '<' ) ) {
	pods_query( "UPDATE @wp_pod_pages SET phpcode = CONCAT('<" . '?' . "php\n', phpcode) WHERE phpcode NOT LIKE '" . '?' . ">%'" );
	pods_query( "UPDATE @wp_pod_pages SET phpcode = SUBSTR(phpcode, 3) WHERE phpcode LIKE '" . '?' . ">%'" );
	pods_query( "UPDATE @wp_pod_widgets SET phpcode = CONCAT('<" . '?' . "php\n', phpcode) WHERE phpcode NOT LIKE '" . '?' . ">%'" );
	pods_query( "UPDATE @wp_pod_widgets SET phpcode = SUBSTR(phpcode, 3) WHERE phpcode LIKE '" . '?' . ">%'" );

	update_option( 'pods_version', '132' );
}

if ( version_compare( $old_version, '1.4.3', '<' ) ) {
	$result = pods_query( "SHOW COLUMNS FROM @wp_pod_types LIKE 'description'" );

	if ( 0 < count( $result ) ) {
		pods_query( 'ALTER TABLE @wp_pod_types CHANGE description label VARCHAR(32)' );
	}

	pods_query( 'ALTER TABLE @wp_pod_types ADD COLUMN is_toplevel BOOL default 0 AFTER label' );

	update_option( 'pods_version', '143' );
}

if ( version_compare( $old_version, '1.4.5', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_pages ADD COLUMN title VARCHAR(128) AFTER uri' );

	$sql = '
        CREATE TABLE @wp_pod_menu (
            id INT unsigned auto_increment primary key,
            uri VARCHAR(128),
            title VARCHAR(128),
            lft INT unsigned,
            rgt INT unsigned,
            weight TINYINT unsigned default 0)';
	pods_query( $sql );

	pods_query( "INSERT INTO @wp_pod_menu (uri, title, lft, rgt) VALUES ('/', 'Home', 1, 2)" );

	update_option( 'pods_version', '145' );
}

if ( version_compare( $old_version, '1.4.8', '<' ) ) {
	add_option( 'pods_roles' );

	update_option( 'pods_version', '148' );
}

if ( version_compare( $old_version, '1.4.9', '<' ) ) {
	pods_query( 'RENAME TABLE @wp_pod_widgets TO @wp_pod_helpers' );

	update_option( 'pods_version', '149' );
}

if ( version_compare( $old_version, '1.5', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN `unique` BOOL default 0 AFTER required' );
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN `multiple` BOOL default 0 AFTER `unique`' );
	pods_query( 'ALTER TABLE @wp_pod_pages ADD COLUMN page_template VARCHAR(128) AFTER phpcode' );
	pods_query( 'ALTER TABLE @wp_pod_helpers ADD COLUMN helper_type VARCHAR(16) AFTER name' );
	pods_query( 'ALTER TABLE @wp_pod ADD COLUMN name VARCHAR(128) AFTER datatype' );
	pods_query( 'ALTER TABLE @wp_pod ADD COLUMN created VARCHAR(128) AFTER name' );
	pods_query( 'ALTER TABLE @wp_pod ADD COLUMN modified VARCHAR(128) AFTER created' );
	pods_query( 'ALTER TABLE @wp_pod CHANGE row_id tbl_row_id INT unsigned' );
	pods_query( 'ALTER TABLE @wp_pod_rel CHANGE term_id tbl_row_id INT unsigned' );
	pods_query( 'ALTER TABLE @wp_pod_rel CHANGE post_id pod_id INT unsigned' );
	pods_query( 'ALTER TABLE @wp_pod_rel CHANGE sister_post_id sister_pod_id INT unsigned' );

	// Make all pick columns "multiple" for consistency
	pods_query( "UPDATE @wp_pod_fields SET `multiple` = 1 WHERE coltype = 'pick'" );

	// Use "display" as the default helper type
	pods_query( "UPDATE @wp_pod_helpers SET helper_type = 'display'" );

	// Replace all post_ids with its associated pod_id
	$sql = '
    SELECT
        p.id, p.post_id, r.post_title AS name, r.post_date AS created, r.post_modified AS modified
    FROM
        @wp_pod p
    INNER JOIN
        @wp_posts r ON r.ID = p.post_id
    ';

	$result = pods_query( $sql );

	foreach ( $result as $row ) {
		$row = get_object_vars( $row );

		foreach ( $row as $key => $val ) {
			${$key} = pods_sanitize( trim( $val ) );
		}

		$posts_to_delete[]       = $post_id;
		$all_pod_ids[ $post_id ] = $id;

		pods_query( "UPDATE @wp_pod SET name = '$name', created = '$created', modified = '$modified' WHERE id = $id LIMIT 1" );
	}

	// Replace post_id with pod_id
	$result = pods_query( 'SELECT id, pod_id, sister_pod_id FROM @wp_pod_rel' );

	foreach ( $result as $row ) {
		$id                = $row->id;
		$new_pod_id        = $all_pod_ids[ $row->pod_id ];
		$new_sister_pod_id = $all_pod_ids[ $row->sister_pod_id ];

		pods_query( "UPDATE @wp_pod_rel SET pod_id = '$new_pod_id', sister_pod_id = '$new_sister_pod_id' WHERE id = '$id' LIMIT 1" );
	}

	$posts_to_delete = implode( ',', $posts_to_delete );

	// Remove all traces from wp_posts
	pods_query( 'ALTER TABLE @wp_pod DROP COLUMN post_id' );
	pods_query( "DELETE FROM @wp_posts WHERE ID IN ($posts_to_delete)" );

	update_option( 'pods_version', '150' );
}//end if

if ( version_compare( $old_version, '1.5.1', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN helper VARCHAR(32) AFTER label' );
	pods_query( 'ALTER TABLE @wp_pod_types ADD COLUMN before_helpers TEXT AFTER tpl_list' );
	pods_query( 'ALTER TABLE @wp_pod_types ADD COLUMN after_helpers TEXT AFTER before_helpers' );

	update_option( 'pods_version', '151' );
}

if ( version_compare( $old_version, '1.6.0', '<' ) ) {
	// Add the "templates" table
	$sql = '
    CREATE TABLE IF NOT EXISTS @wp_pod_templates (
        id INT unsigned auto_increment primary key,
        name VARCHAR(32),
        code TEXT)';
	pods_query( $sql );

	// Add list and detail template presets
	$tpl_list   = '<p><a href="{@detail_url}">{@name}</a></p>';
	$tpl_detail = "<h2>{@name}</h2>\n{@body}";
	pods_query( "INSERT INTO @wp_pod_templates (name, code) VALUES ('detail', '$tpl_detail'),('list', '$tpl_list')" );

	// Try to route old templates as best as possible
	$result = pods_query( 'SELECT name, tpl_detail, tpl_list FROM @wp_pod_types' );

	foreach ( $result as $row ) {
		// Create the new template, e.g. "dtname_list" or "dtname_detail"
		$row = pods_sanitize( $row );

		pods_query( "INSERT INTO @wp_pod_templates (name, code) VALUES ('{$row->name}_detail', '{$row->tpl_detail}'),('{$row->name}_list', '{$row->tpl_list}')" );
	}

	// Drop the "tpl_detail" and "tpl_list" columns
	pods_query( 'ALTER TABLE @wp_pod_types DROP COLUMN tpl_detail, DROP COLUMN tpl_list' );

	// Add the "pick_filter" column
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN pick_filter VARCHAR(128) AFTER pickval' );

	update_option( 'pods_version', '160' );
}//end if

if ( version_compare( $old_version, '1.6.2', '<' ) ) {
	// Remove all beginning and ending slashes from Pod Pages
	pods_query( "UPDATE @wp_pod_pages SET uri = TRIM(BOTH '/' FROM uri)" );

	update_option( 'pods_version', '162' );
}

if ( version_compare( $old_version, '1.6.4', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN pick_orderby TEXT AFTER pick_filter' );
	pods_query( 'ALTER TABLE @wp_pod_fields CHANGE helper display_helper TEXT' );
	pods_query( 'ALTER TABLE @wp_pod_fields ADD COLUMN input_helper TEXT AFTER display_helper' );

	update_option( 'pods_version', '164' );
}

if ( version_compare( $old_version, '1.6.7', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_pages ADD COLUMN precode LONGTEXT AFTER phpcode' );

	update_option( 'pods_version', '167' );
}

if ( version_compare( $old_version, '1.7.3', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_types ADD COLUMN detail_page VARCHAR(128) AFTER is_toplevel' );

	update_option( 'pods_version', '173' );
}

if ( version_compare( $old_version, '1.7.5', '<' ) ) {
	if ( empty( $pods_roles ) && ! is_array( $pods_roles ) ) {
		$pods_roles = @unserialize( get_option( 'pods_roles' ) );

		if ( ! is_array( $pods_roles ) ) {
			$pods_roles = array();
		}
	}

	if ( is_array( $pods_roles ) ) {
		foreach ( $pods_roles as $role => $privs ) {
			if ( in_array( 'manage_podpages', $privs, true ) ) {
				$pods_roles[ $role ][] = 'manage_pod_pages';

				unset( $pods_roles[ $role ]['manage_podpages'] );
			}
		}
	}

	delete_option( 'pods_roles' );
	add_option( 'pods_roles', serialize( $pods_roles ) );

	update_option( 'pods_version', '175' );
}//end if

if ( version_compare( $old_version, '1.7.6', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_types CHANGE label label VARCHAR(128)' );
	pods_query( 'ALTER TABLE @wp_pod_fields CHANGE label label VARCHAR(128)' );

	$result = pods_query( "SELECT f.id AS field_id, f.name AS field_name, f.datatype AS datatype_id, dt.name AS datatype FROM @wp_pod_fields AS f LEFT JOIN @wp_pod_types AS dt ON dt.id = f.datatype WHERE f.coltype='file'" );

	foreach ( $result as $row ) {
		$items   = pods_query( "SELECT t.id AS tbl_row_id, t.{$row->field_name} AS file, p.id AS pod_id FROM @wp_pod_tbl_{$row->datatype} AS t LEFT JOIN @wp_pod AS p ON p.tbl_row_id = t.id AND p.datatype = {$row->datatype_id} WHERE t.{$row->field_name} != '' AND t.{$row->field_name} IS NOT NULL" );
		$success = false;
		$rels    = array();

		foreach ( (array) $items as $item ) {
			$filename = $item->file;

			if ( strpos( $filename, get_site_url() ) !== false && 0 === strpos( $filename, get_site_url() ) ) {
				$filename = ltrim( $filename, get_site_url() );
			}

			$upload_dir = wp_upload_dir();

			if ( strpos( $filename, str_replace( get_site_url(), '', $upload_dir['baseurl'] ) ) === false ) {
				$success = false;

				break;
			}

			$file = str_replace( '//', '/', ( ABSPATH . $filename ) );

			$wp_filetype = wp_check_filetype( basename( $file ), null );

			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file ) ),
				'guid'           => str_replace( '//wp-content/', '/wp-content/', get_site_url() . $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attach_id = wp_insert_attachment( $attachment, $file, 0 );

			if ( $attach_id > 0 ) {
				require_once ABSPATH . 'wp-admin' . '/includes/image.php';

				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

				wp_update_attachment_metadata( $attach_id, $attach_data );

				$sizes = array( 'thumb', 'medium', 'large' );

				foreach ( $sizes as $size ) {
					image_downsize( $attach_id, $size );
				}

				$rels[] = array(
					'pod_id'     => $item->pod_id,
					'tbl_row_id' => $item->tbl_row_id,
					'attach_id'  => $attach_id,
					'field_id'   => $row->field_id,
				);

				$success = true;
			}//end if
		}//end foreach
		if ( false !== $success ) {
			foreach ( $rels as $rel ) {
				pods_query( "INSERT INTO @wp_pod_rel (pod_id, field_id, tbl_row_id) VALUES({$rel['pod_id']}, {$rel['field_id']}, {$rel['attach_id']})" );
			}

			pods_query( "ALTER TABLE @wp_pod_tbl_{$row->datatype} DROP COLUMN {$row->field_name}" );
		} else {
			pods_query( "UPDATE @wp_pod_fields SET coltype = 'txt' WHERE id = {$row->field_id}" );
		}
	}//end foreach

	update_option( 'pods_version', '176' );
}//end if

if ( version_compare( $old_version, '1.8.1', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_rel ADD COLUMN weight SMALLINT unsigned AFTER tbl_row_id' );
	pods_query( 'ALTER TABLE @wp_pod_types CHANGE before_helpers pre_save_helpers TEXT' );
	pods_query( 'ALTER TABLE @wp_pod_types CHANGE after_helpers post_save_helpers TEXT' );
	pods_query( 'ALTER TABLE @wp_pod_types ADD COLUMN pre_drop_helpers TEXT AFTER pre_save_helpers' );
	pods_query( 'ALTER TABLE @wp_pod_types ADD COLUMN post_drop_helpers TEXT AFTER post_save_helpers' );
	pods_query( "UPDATE @wp_pod_helpers SET helper_type = 'pre_save' WHERE helper_type = 'before'" );
	pods_query( "UPDATE @wp_pod_helpers SET helper_type = 'post_save' WHERE helper_type = 'after'" );

	update_option( 'pods_version', '181' );
}

if ( version_compare( $old_version, '1.8.2', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod ADD COLUMN author_id INT unsigned AFTER modified' );
	pods_query( "UPDATE @wp_pod_fields SET pickval = 'wp_taxonomy' WHERE pickval REGEXP '^[0-9]+$'" );
	pods_query( "UPDATE @wp_pod_menu SET uri = '<root>' WHERE uri = '/' LIMIT 1" );

	// Remove beginning and trailing slashes
	$result = pods_query( 'SELECT id, uri FROM @wp_pod_menu' );

	foreach ( $result as $row ) {
		$uri = preg_replace( '@^([/]?)(.*?)([/]?)$@', '$2', $row->uri );
		$uri = pods_sanitize( $uri );
		pods_query( "UPDATE @wp_pod_menu SET uri = '$uri' WHERE id = {$row->id} LIMIT 1" );
	}

	update_option( 'pods_version', '182' );
}

if ( version_compare( $old_version, '1.9.0', '<' ) ) {
	pods_query( 'ALTER TABLE @wp_pod_templates CHANGE `name` `name` VARCHAR(255)' );
	pods_query( 'ALTER TABLE @wp_pod_helpers CHANGE `name` `name` VARCHAR(255)' );
	pods_query( 'ALTER TABLE @wp_pod_fields CHANGE `comment` `comment` VARCHAR(255)' );

	// Remove beginning and trailing slashes
	$result = pods_query( 'SELECT id, uri FROM @wp_pod_pages' );

	foreach ( $result as $row ) {
		$uri = trim( $row->uri, '/' );
		$uri = pods_sanitize( $uri );
		pods_query( "UPDATE @wp_pod_pages SET uri = '$uri' WHERE id = {$row->id} LIMIT 1" );
	}

	update_option( 'pods_version', '190' );
}

if ( version_compare( $old_version, '1.9.6', '<' ) ) {
	add_option( 'pods_disable_file_browser', 0 );
	add_option( 'pods_files_require_login', 0 );
	add_option( 'pods_files_require_login_cap', 'upload_files' );
	add_option( 'pods_disable_file_upload', 0 );
	add_option( 'pods_upload_require_login', 0 );
	add_option( 'pods_upload_require_login_cap', 'upload_files' );

	update_option( 'pods_version', '196' );
}

if ( version_compare( $old_version, '1.9.7', '<' ) ) {
	pods_query( 'ALTER TABLE `@wp_pod` CHANGE `id` `id` BIGINT(15) UNSIGNED NOT NULL AUTO_INCREMENT' );
	pods_query( 'ALTER TABLE `@wp_pod` CHANGE `tbl_row_id` `tbl_row_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL' );
	pods_query( 'ALTER TABLE `@wp_pod` CHANGE `author_id` `author_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL' );
	pods_query( 'ALTER TABLE `@wp_pod_rel` CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT' );
	pods_query( 'ALTER TABLE `@wp_pod_rel` CHANGE `pod_id` `pod_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL' );
	pods_query( 'ALTER TABLE `@wp_pod_rel` CHANGE `sister_pod_id` `sister_pod_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL' );
	pods_query( 'ALTER TABLE `@wp_pod_rel` CHANGE `tbl_row_id` `tbl_row_id` BIGINT(15) UNSIGNED NULL DEFAULT NULL' );
	pods_query( "ALTER TABLE `@wp_pod_rel` CHANGE `weight` `weight` INT(10) UNSIGNED NULL DEFAULT '0'" );

	update_option( 'pods_version', '197' );
}

if ( version_compare( $old_version, '1.11', '<' ) ) {
	pods_query( 'ALTER TABLE `@wp_pod` CHANGE `datatype` `datatype` INT(10) UNSIGNED NULL DEFAULT NULL' );
	pods_query( 'ALTER TABLE `@wp_pod` DROP INDEX `datatype_idx`', false );
	pods_query( 'ALTER TABLE `@wp_pod` ADD INDEX `datatype_row_idx` (`datatype`, `tbl_row_id`)', false );
	pods_query( 'ALTER TABLE `@wp_pod_rel` DROP INDEX `field_id_idx`', false );
	pods_query( 'ALTER TABLE `@wp_pod_rel` ADD INDEX `field_pod_idx` (`field_id`, `pod_id`)', false );
	pods_query( 'ALTER TABLE `@wp_pod_fields` CHANGE `datatype` `datatype` INT(10) UNSIGNED NULL DEFAULT NULL' );
	$result = pods_query( 'SELECT id, name FROM @wp_pod_types' );
	foreach ( $result as $row ) {
		$pod = pods_sanitize( $row->name );
		pods_query( "ALTER TABLE `@wp_pod_tbl_{$pod}` CHANGE `id` `id` BIGINT(15) UNSIGNED NOT NULL AUTO_INCREMENT" );
	}
	update_option( 'pods_version', '001011000' );
}
