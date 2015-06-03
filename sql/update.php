<?php
/**
 * @package Pods
 * @category Upgrade
 */

/**
 * @var $wpdb WPDB
 * @var $pods_version string
 */
global $wpdb;

// Update 2.0.0 alpha / beta
if ( version_compare( $pods_version, '2.0.0-b-15', '<' ) ) {
	// Update to 2.0.0-a-31
	if ( version_compare( $pods_version, '2.0.0-a-31', '<' ) ) {
		$api = pods_api();

		$api->display_errors = true;

		$old_pods = pods_query( "SELECT * FROM `@wp_pods`", false );

		if ( ! empty( $old_pods ) ) {
			foreach ( $old_pods as $pod ) {
				$api->cache_flush_pods( array( 'name' => $pod->name ) );

				$pod_opts = json_decode( $pod->options, true );

				$field_rows = pods_query( "SELECT * FROM `@wp_pods_fields` where `pod_id` = {$pod->id}" );

				$fields = array();

				foreach ( $field_rows as $row ) {
					$field_opts = json_decode( $row->options, true );

					if ( 'permalink' == $row->type ) {
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
						'options'         => $field_opts
					);

					$fields[] = $field_params;
				}

				$pod_params = array(
					'name'    => $pod->name,
					'type'    => $pod->type,
					'storage' => $pod->storage,
					'fields'  => $fields,
					'options' => $pod_opts
				);

				$renamed = false;

				if ( $pod->storage == 'table' ) {
					try {
						pods_query( "RENAME TABLE `@wp_pods_tbl_{$pod->name}` TO `@wp_pods_tb_{$pod->name}`" );
						$renamed = true;
					}
					catch ( Exception $e ) {
						$renamed = false;
					}
				}

				$api->save_pod( $pod_params );

				if ( $pod->storage == 'table' && $renamed ) {
					pods_query( "DROP TABLE `@wp_pods_tbl_{$pod->name}`", false );
					pods_query( "RENAME TABLE `@wp_pods_tb_{$pod->name}` TO `@wp_pods_tbl_{$pod->name}`" );
				}
			}
		}

		$helper_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'helper'", false );

		if ( ! empty( $helper_rows ) ) {
			foreach ( $helper_rows as $row ) {
				$opts = json_decode( $row->options );

				$helper_params = array(
					'name'    => $row->name,
					'type'    => $opts->type,
					'phpcode' => $opts->phpcode,
				);

				$api->save_helper( $helper_params );
			}
		}

		$page_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page'", false );

		if ( ! empty( $page_rows ) ) {
			foreach ( $page_rows as $row ) {
				$opts = json_decode( $row->options );

				$page_params = array(
					'uri'     => $row->name,
					'phpcode' => $opts->phpcode,
				);

				$api->save_page( $page_params );
			}
		}

		$tpl_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'template'", false );

		if ( ! empty( $tpl_rows ) ) {
			foreach ( $tpl_rows as $row ) {
				$opts = json_decode( $row->options );

				$tpl_params = array(
					'name' => $row->name,
					'code' => $opts->code,
				);

				$api->save_template( $tpl_params );
			}
		}

		pods_query( "DROP TABLE @wp_pods", false );
		pods_query( "DROP TABLE @wp_pods_fields", false );
		pods_query( "DROP TABLE @wp_pods_objects", false );

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

		$_GET[ 'toggle' ] = 1;

		Pods_Init::$components->toggle( 'templates' );
		Pods_Init::$components->toggle( 'pages' );
		Pods_Init::$components->toggle( 'helpers' );

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
	}

	// Update to 2.0.0-b-14
	if ( version_compare( $pods_version, '2.0.0-b-14', '<' ) ) {
		$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}pods%'", ARRAY_N );

		$podsrel_found = false;

		if ( ! empty( $tables ) ) {
			foreach ( $tables as &$table ) {
				$table     = $table[ 0 ];
				$new_table = $table;

				if ( "{$wpdb->prefix}pods_rel" == $table ) {
					$new_table = "{$wpdb->prefix}podsrel";

					$podsrel_found = true;
				} elseif ( "{$wpdb->prefix}podsrel" == $table ) {
					$podsrel_found = true;
				} else {
					$new_table = str_replace( 'pods_tbl_', 'pods_', $table );
				}

				if ( $table != $new_table ) {
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

			if ( 'DEFAULT CHARSET utf8' != $charset_collate ) {
				$sql = str_replace( 'DEFAULT CHARSET utf8', $charset_collate, $sql );
			}

			$sql = explode( ";\n", str_replace( array( "\r", 'wp_' ), array( "\n", $wpdb->prefix ), $sql ) );

			for ( $i = 0, $z = count( $sql ); $i < $z; $i ++ ) {
				$query = trim( $sql[ $i ] );

				if ( empty( $query ) ) {
					continue;
				}

				pods_query( $query, 'Cannot setup SQL tables' );
			}
		}

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

				if ( 'datetime' == $new_type ) {
					$new = array(
						'date_format'      => 'datetime_format',
						'date_time_type'   => 'datetime_time_type',
						'date_time_format' => 'datetime_time_format',
						'date_html5'       => 'datetime_html5'
					);

					update_post_meta( $field->ID, 'type', $new_type );
				} elseif ( 'time' == $new_type ) {
					$new = array(
						'date_time_type'   => 'time_type',
						'date_time_format' => 'time_format',
						'date_html5'       => 'time_html5'
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
                AND `pm`.`meta_value` = 'number'
        " );

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$new_type = get_post_meta( $field->ID, 'number_format_type', true );

				if ( 'currency' == $new_type ) {
					$new = array(
						'number_format_currency_sign'      => 'currency_format_sign',
						'number_format_currency_placement' => 'currency_format_placement',
						'number_format'                    => 'currency_format',
						'number_decimals'                  => 'currency_decimals',
						'number_max_length'                => 'currency_max_length',
						'number_size'                      => 'currency_size'
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

				if ( 'plain' != $new_type ) {
					$new_type = 'wysiwyg';

					$new = array(
						'paragraph_format_type'       => 'wysiwyg_editor',
						'paragraph_allow_shortcode'   => 'wysiwyg_allow_shortcode',
						'paragraph_allowed_html_tags' => 'wysiwyg_allowed_html_tags',
						'paragraph_max_length'        => 'wysiwyg_max_length',
						'paragraph_size'              => 'wysiwyg_size'
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

				if ( 'website' == $new_type ) {
					$new = array(
						'text_format_website' => 'website_format',
						'text_max_length'     => 'website_max_length',
						'text_html5'          => 'website_html5',
						'text_size'           => 'website_size'
					);

					update_post_meta( $field->ID, 'type', $new_type );
				} elseif ( 'phone' == $new_type ) {
					$new = array(
						'text_format_phone' => 'phone_format',
						'text_max_length'   => 'phone_max_length',
						'text_html5'        => 'phone_html5',
						'text_size'         => 'phone_size'
					);

					update_post_meta( $field->ID, 'type', $new_type );
				} elseif ( 'email' == $new_type ) {
					$new = array(
						'text_max_length' => 'email_max_length',
						'text_html5'      => 'email_html5',
						'text_size'       => 'email_size'
					);

					update_post_meta( $field->ID, 'type', $new_type );
				} elseif ( 'password' == $new_type ) {
					$new = array(
						'text_max_length' => 'password_max_length',
						'text_size'       => 'password_size'
					);

					update_post_meta( $field->ID, 'type', $new_type );
				}
			}
		}

		pods_no_conflict_off( 'post' );

		update_option( 'pods_framework_version', '2.0.0-b-14' );
	}

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
}

// Update to 2.0.3
if ( version_compare( $pods_version, '2.0.3', '<' ) ) {
    // Rename sister_field_id to sister_id
    pods_query( "DELETE FROM `@wp_postmeta` WHERE `meta_key` = 'sister_field_id'", false );

    update_option( 'pods_framework_version', '2.0.3' );
}

// Update to 2.3
if ( version_compare( $pods_version, '2.3', '<' ) ) {
    // Auto activate Advanced Content Types component
    $oldget = $_GET;

    $_GET[ 'toggle' ] = 1;

    Pods_Init::$components->toggle( 'advanced-content-types' );
    Pods_Init::$components->toggle( 'table-storage' );

    $_GET = $oldget;

    update_option( 'pods_framework_version', '2.3' );
}

// Update to 2.3.4
if ( version_compare( $pods_version, '2.3.4', '<' ) ) {
    if ( function_exists( 'pods_page_flush_rewrites' ) )
        pods_page_flush_rewrites();

    update_option( 'pods_framework_version', '2.3.4' );
}

// Update to 2.3.5
if ( version_compare( $pods_version, '2.3.5', '<' ) ) {
    global $wpdb;

    $wpdb->query( "UPDATE `{$wpdb->postmeta}` SET `meta_value` = 'dMy' WHERE `meta_key` IN ( 'date_format', 'datetime_format' ) AND `meta_value` = 'dMd'" );
    $wpdb->query( "UPDATE `{$wpdb->postmeta}` SET `meta_value` = 'dMy_dash' WHERE `meta_key` IN ( 'date_format', 'datetime_format' ) AND `meta_value` = 'dMd_dash'" );

    $pods_object_ids = $wpdb->get_col( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` IN ( '_pods_pod', '_pods_field', '_pods_page', '_pods_template', '_pods_helper' )" );

    if ( !empty( $pods_object_ids ) ) {
        array_walk( $pods_object_ids, 'absint' );

        $wpdb->query( "DELETE FROM `{$wpdb->postmeta}` WHERE `post_id` IN ( " . implode( ', ', $pods_object_ids ) . " ) AND `meta_value` = ''" );
    }

    update_option( 'pods_framework_version', '2.3.5' );
}

// Update to 2.3.9
if ( version_compare( $pods_version, '2.3.9-a-1', '<' ) ) {
    // Set autoload on all necessary options to avoid extra queries
    $autoload_options = array(
        'pods_framework_version' => '',
        'pods_framework_version_last' => '',
        'pods_framework_db_version' => '',
        'pods_framework_upgraded_1_x' => '0',
        'pods_version' => '',
        'pods_component_settings' => '',
        'pods_disable_file_browser' => '0',
        'pods_files_require_login' => '1',
        'pods_files_require_login_cap' => '',
        'pods_disable_file_upload' => '0',
        'pods_upload_require_login' => '1',
        'pods_upload_require_login_cap' => ''
    );

    foreach ( $autoload_options as $option_name => $default ) {
        $option_value = get_option( $option_name, $default );

        delete_option( $option_name );
        add_option( $option_name, $option_value, '', 'yes' );
    }

    update_option( 'pods_framework_version', '2.3.9-a-1' );
}