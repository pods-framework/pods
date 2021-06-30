<?php
/**
 * @package Pods\Upgrade
 */

/**
 * @var $wpdb WPDB
 */
global $wpdb;

// Update to 2.0.0-a-31
if ( version_compare( $pods_version, '2.0.0-a-31', '<' ) ) {
	$pages     = pods_2_alpha_migrate_pages();
	$helpers   = pods_2_alpha_migrate_helpers();
	$templates = pods_2_alpha_migrate_templates();
	$pod_ids   = pods_2_alpha_migrate_pods();

	pods_query( 'DROP TABLE @wp_pods', false );
	pods_query( 'DROP TABLE @wp_pods_fields', false );
	pods_query( 'DROP TABLE @wp_pods_objects', false );

	update_option( 'pods_framework_version', '2.0.0-a-31' );
}

// Update to 2.0.0-b-10
if ( version_compare( $pods_version, '2.0.0-b-10', '<' ) ) {
	$author_fields = $wpdb->get_results( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'author' AND `post_type` = '_pods_field'" );

	if ( ! empty( $author_fields ) ) {
		foreach ( $author_fields as $author ) {
			update_post_meta( $author->ID, 'pick_format_type', 'single' );
			update_post_meta( $author->ID, 'pick_format_single', 'autocomplete' );
			update_post_meta( $author->ID, 'default_value', '{@user.ID}' );
		}
	}

	update_option( 'pods_framework_version', '2.0.0-b-10' );
}

// Update to 2.0.0-b-11
if ( version_compare( $pods_version, '2.0.0-b-11', '<' ) ) {
	$date_fields = $wpdb->get_results( "
            SELECT `ID`
            FROM `{$wpdb->posts}`
            WHERE ( `post_name` = 'created' OR `post_name` = 'modified' ) AND `post_type` = '_pods_field'
        " );

	if ( ! empty( $date_fields ) ) {
		foreach ( $date_fields as $date ) {
			update_post_meta( $date->ID, 'date_format_type', 'datetime' );
			update_post_meta( $date->ID, 'date_format', 'ymd_slash' );
			update_post_meta( $date->ID, 'date_time_type', '12' );
			update_post_meta( $date->ID, 'date_time_format', 'h_mm_ss_A' );
		}
	}

	update_option( 'pods_framework_version', '2.0.0-b-11' );
}

// Update to 2.0.0-b-12
if ( version_compare( $pods_version, '2.0.0-b-12', '<' ) ) {
	$oldget = $_GET;

	$_GET['toggle'] = 1;

	PodsInit::$components->toggle( 'templates' );
	PodsInit::$components->toggle( 'pages' );
	PodsInit::$components->toggle( 'helpers' );

	$_GET = $oldget;

	$number_fields = $wpdb->get_results( "
            SELECT `p`.`ID`
            FROM `{$wpdb->posts}` AS `p`
            LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
            WHERE
                `p`.`post_type` = '_pods_field'
                AND `pm`.`meta_key` = 'type'
                AND `pm`.`meta_value` = 'number'
        " );

	if ( ! empty( $number_fields ) ) {
		foreach ( $number_fields as $number ) {
			update_post_meta( $number->ID, 'number_max_length', '12' );
		}
	}

	update_option( 'pods_framework_version', '2.0.0-b-12' );
}//end if

// Update to 2.0.0-b-14
if ( version_compare( $pods_version, '2.0.0-b-14', '<' ) ) {
	$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}pods%'", ARRAY_N );

	$podsrel_found = false;

	if ( ! empty( $tables ) ) {
		foreach ( $tables as &$table ) {
			$table     = $table[0];
			$new_table = $table;

			if ( "{$wpdb->prefix}pods_rel" === $table ) {
				$new_table = "{$wpdb->prefix}podsrel";

				$podsrel_found = true;
			} elseif ( "{$wpdb->prefix}podsrel" === $table ) {
				$podsrel_found = true;
			} else {
				$new_table = str_replace( 'pods_tbl_', 'pods_', $table );
			}

			if ( $table !== $new_table ) {
				$wpdb->query( "ALTER TABLE `{$table}` RENAME `{$new_table}`" );
			}
		}
	}

	if ( ! $podsrel_found ) {
		// rerun install for any bugged versions
		$sql = file_get_contents( PODS_DIR . 'sql/dump.sql' );
		$sql = apply_filters( 'pods_install_sql', $sql, PODS_VERSION, $pods_version, $_blog_id );

		$charset_collate = 'DEFAULT CHARSET utf8';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARSET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		if ( 'DEFAULT CHARSET utf8' !== $charset_collate ) {
			$sql = str_replace( 'DEFAULT CHARSET utf8', $charset_collate, $sql );
		}

		$sql = explode( ";\n", str_replace( array( "\r", 'wp_' ), array( "\n", $wpdb->prefix ), $sql ) );

		$z = count( $sql );
		for ( $i = 0; $i < $z; $i ++ ) {
			$query = trim( $sql[ $i ] );

			if ( empty( $query ) ) {
				continue;
			}

			pods_query( $query, 'Cannot setup SQL tables' );
		}
	}//end if

	pods_no_conflict_on( 'post' );

	// convert field types based on options set
	$fields = $wpdb->get_results( "
            SELECT `p`.`ID`
            FROM `{$wpdb->posts}` AS `p`
            LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
            WHERE
                `p`.`post_type` = '_pods_field'
                AND `pm`.`meta_key` = 'type'
                AND `pm`.`meta_value` = 'date'
        " );

	if ( ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			$new_type = get_post_meta( $field->ID, 'date_format_type', true );

			if ( 'datetime' === $new_type ) {
				$new = array(
					'date_format'      => 'datetime_format',
					'date_time_type'   => 'datetime_time_type',
					'date_time_format' => 'datetime_time_format',
					'date_html5'       => 'datetime_html5',
				);

				update_post_meta( $field->ID, 'type', $new_type );
			} elseif ( 'time' === $new_type ) {
				$new = array(
					'date_time_type'   => 'time_type',
					'date_time_format' => 'time_format',
					'date_html5'       => 'time_html5',
				);

				update_post_meta( $field->ID, 'type', $new_type );
			}
		}//end foreach
	}//end if

	$fields = $wpdb->get_results( "
            SELECT `p`.`ID`
            FROM `{$wpdb->posts}` AS `p`
            LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
            WHERE
                `p`.`post_type` = '_pods_field'
                AND `pm`.`meta_key` = 'type'
                AND `pm`.`meta_value` = 'number'
        " );

	if ( ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			$new_type = get_post_meta( $field->ID, 'number_format_type', true );

			if ( 'currency' === $new_type ) {
				$new = array(
					'number_format_currency_sign'      => 'currency_format_sign',
					'number_format_currency_placement' => 'currency_format_placement',
					'number_format'                    => 'currency_format',
					'number_decimals'                  => 'currency_decimals',
					'number_max_length'                => 'currency_max_length',
					'number_size'                      => 'currency_size',
				);

				update_post_meta( $field->ID, 'type', $new_type );
			}
		}
	}

	$fields = $wpdb->get_results( "
            SELECT `p`.`ID`
            FROM `{$wpdb->posts}` AS `p`
            LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
            WHERE
                `p`.`post_type` = '_pods_field'
                AND `pm`.`meta_key` = 'type'
                AND `pm`.`meta_value` = 'paragraph'
        " );

	if ( ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			$new_type = get_post_meta( $field->ID, 'paragraph_format_type', true );

			if ( 'plain' !== $new_type ) {
				$new_type = 'wysiwyg';

				$new = array(
					'paragraph_format_type'       => 'wysiwyg_editor',
					'paragraph_allow_shortcode'   => 'wysiwyg_allow_shortcode',
					'paragraph_allowed_html_tags' => 'wysiwyg_allowed_html_tags',
					'paragraph_max_length'        => 'wysiwyg_max_length',
					'paragraph_size'              => 'wysiwyg_size',
				);

				update_post_meta( $field->ID, 'type', $new_type );
			}
		}
	}

	$fields = $wpdb->get_results( "
            SELECT `p`.`ID`
            FROM `{$wpdb->posts}` AS `p`
            LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
            WHERE
                `p`.`post_type` = '_pods_field'
                AND `pm`.`meta_key` = 'type'
                AND `pm`.`meta_value` = 'text'
        " );

	if ( ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			$new_type = get_post_meta( $field->ID, 'text_format_type', true );

			if ( 'website' === $new_type ) {
				$new = array(
					'text_format_website' => 'website_format',
					'text_max_length'     => 'website_max_length',
					'text_html5'          => 'website_html5',
					'text_size'           => 'website_size',
				);

				update_post_meta( $field->ID, 'type', $new_type );
			} elseif ( 'phone' === $new_type ) {
				$new = array(
					'text_format_phone' => 'phone_format',
					'text_max_length'   => 'phone_max_length',
					'text_html5'        => 'phone_html5',
					'text_size'         => 'phone_size',
				);

				update_post_meta( $field->ID, 'type', $new_type );
			} elseif ( 'email' === $new_type ) {
				$new = array(
					'text_max_length' => 'email_max_length',
					'text_html5'      => 'email_html5',
					'text_size'       => 'email_size',
				);

				update_post_meta( $field->ID, 'type', $new_type );
			} elseif ( 'password' === $new_type ) {
				$new = array(
					'text_max_length' => 'password_max_length',
					'text_size'       => 'password_size',
				);

				update_post_meta( $field->ID, 'type', $new_type );
			}//end if
		}//end foreach
	}//end if

	pods_no_conflict_off( 'post' );

	update_option( 'pods_framework_version', '2.0.0-b-14' );
}//end if

// Update to 2.0.0-b-15
if ( version_compare( $pods_version, '2.0.0-b-15', '<' ) ) {
	$helpers = $wpdb->get_results( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` = '_pods_helper'" );

	if ( ! empty( $helpers ) ) {
		foreach ( $helpers as $helper ) {
			$wpdb->query( "UPDATE `{$wpdb->postmeta}` SET `meta_key` = 'helper_type' WHERE `meta_key` = 'type' AND `post_id` = {$helper->ID}" );
		}
	}

	update_option( 'pods_framework_version', '2.0.0-b-15' );
}

/*
===================================

Old upgrade code from Alpha to Beta

===================================
*/
/**
 * @param $id
 * @param $options
 */
function pods_2_beta_migrate_type( $id, $options ) {
	global $wpdb;

	foreach ( $options as $old => $new ) {
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->postmeta}` SET `meta_key` = %s WHERE `meta_key` = %s", array(
				$new,
				$old,
			) ) );
	}
}

/**
 * @return array
 */
function pods_2_alpha_migrate_pods() {
	$api = pods_api();

	$api->display_errors = true;

	$old_pods = pods_query( 'SELECT * FROM `@wp_pods`', false );

	$pod_ids = array();

	if ( empty( $old_pods ) ) {
		return $pod_ids;
	}

	foreach ( $old_pods as $pod ) {
		$api->cache_flush_pods( array( 'name' => $pod->name ) );

		$pod_opts = json_decode( $pod->options, true );

		$field_rows = pods_query( "SELECT * FROM `@wp_pods_fields` where `pod_id` = {$pod->id}" );

		$fields = array();

		foreach ( $field_rows as $row ) {
			$field_opts = json_decode( $row->options, true );

			if ( 'permalink' === $row->type ) {
				$row->type = 'slug';
			}

			$field_params = array(
				'name'            => $row->name,
				'label'           => $row->label,
				'type'            => $row->type,
				'pick_object'     => $row->pick_object,
				'pick_val'        => $row->pick_val,
				'sister_field_id' => $row->sister_field_id,
				'weight'          => $row->weight,
				'options'         => $field_opts,
			);

			$fields[] = $field_params;
		}

		$pod_params = array(
			'name'    => $pod->name,
			'type'    => $pod->type,
			'storage' => $pod->storage,
			'fields'  => $fields,
			'options' => $pod_opts,
		);

		$renamed = false;

		if ( 'table' === $pod->storage ) {
			try {
				pods_query( "RENAME TABLE `@wp_pods_tbl_{$pod->name}` TO `@wp_pods_tb_{$pod->name}`" );
				$renamed = true;
			} catch ( Exception $e ) {
				$renamed = false;
			}
		}

		$pod_id = $api->save_pod( $pod_params );

		if ( 'table' === $pod->storage && $renamed ) {
			pods_query( "DROP TABLE `@wp_pods_tbl_{$pod->name}`", false );
			pods_query( "RENAME TABLE `@wp_pods_tb_{$pod->name}` TO `@wp_pods_tbl_{$pod->name}`" );
		}

		$pod_ids[] = $pod_id;
	}//end foreach

	return $pod_ids;
}

/**
 * @return array
 */
function pods_2_alpha_migrate_helpers() {
	$api = pods_api();

	$helper_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'helper'", false );

	$helper_ids = array();

	if ( empty( $helper_rows ) ) {
		return $helper_ids;
	}

	foreach ( $helper_rows as $row ) {
		$opts = json_decode( $row->options );

		$helper_params = array(
			'name'    => $row->name,
			'type'    => $opts->type,
			'phpcode' => $opts->phpcode,
		);

		$helper_ids[] = $api->save_helper( $helper_params );
	}

	return $helper_ids;
}

/**
 * @return array
 */
function pods_2_alpha_migrate_pages() {
	$api = pods_api();

	$page_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page'", false );

	$page_ids = array();

	if ( empty( $page_rows ) ) {
		return $page_ids;
	}

	foreach ( $page_rows as $row ) {
		$opts = json_decode( $row->options );

		$page_params = array(
			'uri'     => $row->name,
			'phpcode' => $opts->phpcode,
		);

		$page_ids[] = $api->save_page( $page_params );
	}

	return $page_ids;
}

/**
 * @return array
 */
function pods_2_alpha_migrate_templates() {
	$api = pods_api();

	$tpl_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'template'", false );

	$tpl_ids = array();

	if ( empty( $tpl_rows ) ) {
		return $tpl_ids;
	}

	foreach ( $tpl_rows as $row ) {
		$opts = json_decode( $row->options );

		$tpl_params = array(
			'name' => $row->name,
			'code' => $opts->code,
		);

		$tpl_ids[] = $api->save_template( $tpl_params );
	}

	return $tpl_ids;
}
