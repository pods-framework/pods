<?php
/**
 * ID: migrate-packages
 *
 * Name: Import/Export Packages
 *
 * Menu Name: Import/Export Packages
 *
 * Description: Import/Export your Pods, Fields, and other settings from any Pods site; Includes an API to Import/Export Packages via PHP
 *
 * Version: 2.0
 *
 * Category: Migration
 *
 * Plugin: pods-migrate-packages/pods-migrate-packages.php
 *
 * @package    Pods\Components
 * @subpackage Migrate-Packages
 */

if ( class_exists( 'Pods_Migrate_Packages' ) ) {
	return;
}

/**
 * Class Pods_Migrate_Packages
 */
class Pods_Migrate_Packages extends PodsComponent {

	/**
	 * The PodsAPI instance.
	 *
	 * @var PodsAPI
	 */
	private static $api;

	/**
	 * The package meta version.
	 *
	 * @var string
	 */
	private static $package_meta_version;

	/**
	 * Enqueue styles
	 *
	 * @since 2.0.0
	 */
	public function admin_assets() {
		wp_enqueue_script( 'pods-file-saver', PODS_URL . '/components/Migrate-Packages/js/FileSaver.min.js', '', [], '2.0.4', true );
		wp_enqueue_style( 'pods-wizard' );
	}

	/**
	 * Build admin area
	 *
	 * @param array  $options   Component options.
	 * @param string $component Component name.
	 *
	 * @since 2.0.0
	 */
	public function admin( $options, $component ) {
		$method = 'import_export';

		pods_view( PODS_DIR . 'components/Migrate-Packages/ui/wizard.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Handle the Import/Export AJAX
	 *
	 * @param $params
	 */
	public function ajax_import_export( $params ) {
		if ( 'import' === $params->import_export ) {
			$data = trim( $params->import_package );
			$file = null;

			$content = '<div class="pods-wizard-content">';

			/**
			 * Allow filtering whether to replace existing configurations when importing a package.
			 *
			 * @since 2.8.0
			 *
			 * @param bool $replace Whether to replace existing configurations when importing a package.
			 */
			$replace = apply_filters( 'pods_migrate_packages_import_replace', false );

			if ( ! empty( $_FILES['import_package_file'] ) ) {
				$data = null;
				$file = $_FILES['import_package_file'];

				if ( 0 !== (int) $file['error'] ) {
					$content .= '<p>' . esc_html__( 'Import Error: Package upload failed', 'pods' ) . '</p>';
				} elseif (
					! in_array( $file['type'], [ 'application/json', 'text/json' ], true )
					|| '.json' !== substr( $file['name'], -5, 5 )
				) {
					$content .= '<p>' . esc_html__( 'Import Error: Package upload is not a valid JSON file', 'pods' ) . '</p>';
				} elseif ( ! is_file( $file['tmp_name'] ) ) {
					$content .= '<p>' . esc_html__( 'Import Error: Package upload not completed', 'pods' ) . '</p>';
				} else {
					$data = file_get_contents( $file['tmp_name'] );
				}
			}

			if ( ! empty( $data ) ) {
				$imported = self::import( $data, $replace );

				if ( ! empty( $imported ) ) {
					$content .= '<p>Import Complete! The following items were imported:</p>';

					foreach ( $imported as $type => $import ) {
						$content .= '<h4>' . ucwords( $type ) . '</h4>';

						$content .= '<ul class="normal">';

						foreach ( $import as $name => $what ) {
							$content .= '<li>' . esc_html( $what ) . ( 'pods' === $type ? ' (' . esc_html( $name ) . ')' : '' ) . '</li>';
						}

						$content .= '</ul>';
					}
				}
			} elseif ( null === $file ) {
				$content .= '<p>' . esc_html__( 'Import Error: Invalid Package', 'pods' ) . '</p>';
			}//end if

			$content .= '</div>';

			echo $content;
		} elseif ( 'export' === $params->import_export ) {
			$params = get_object_vars( $params );
			foreach ( $params as $k => $v ) {
				if ( is_array( $v ) ) {
					$params[ $k ] = array_keys( array_filter( $v ) );
				}
			}

			$package = self::export( $params );

			echo '<div class="pods-field-option">';

			echo PodsForm::field( 'export_package', $package, 'paragraph', [
				'attributes'  => [
					'style' => 'width: 94%; max-width: 94%; height: 300px;',
				],
				'disable_dfv' => true,
			] );

			echo '</div>';
		}//end if
	}

	/**
	 * Import a Package
	 *
	 * @param string|array $data    a JSON array package string, or an array of Package Data
	 * @param bool         $replace Whether to replace existing pods entirely or just update them
	 *
	 * @return array|bool
	 *
	 * @static
	 * @since 2.0.5
	 */
	public static function import( $data, $replace = false ) {

		if ( ! defined( 'PODS_FIELD_STRICT' ) ) {
			define( 'PODS_FIELD_STRICT', false );
		}

		if ( ! is_array( $data ) ) {
			$json_data = @json_decode( $data, true );

			if ( ! is_array( $json_data ) ) {
				$json_data = @json_decode( pods_unslash( $data ), true );
			}

			$data = $json_data;
		}

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		self::$api = pods_api();

		$meta = self::get_meta_from_package( $data );

		if ( ! $meta ) {
			return false;
		}

		self::$package_meta_version = $meta['version'];

		$found = [];

		if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
			self::import_settings( $data['settings'] );

			$found['settings']['all'] = __( 'All Settings', 'pods' );
		}//end if

		if ( isset( $data['pods'] ) && is_array( $data['pods'] ) ) {
			foreach ( $data['pods'] as $pod_data ) {
				$pod = self::import_pod( $pod_data, $replace );

				if ( ! $pod ) {
					continue;
				}

				if ( ! isset( $found['pods'] ) ) {
					$found['pods'] = array();
				}

				$found['pods'][ $pod['name'] ] = $pod['label'];
			}//end foreach
		}//end if

		if ( isset( $data['templates'] ) && is_array( $data['templates'] ) ) {
			foreach ( $data['templates'] as $template_data ) {
				$template = self::import_pod_template( $template_data, $replace );

				if ( ! $template ) {
					continue;
				}

				if ( ! isset( $found['templates'] ) ) {
					$found['templates'] = array();
				}

				$found['templates'][ $template['name'] ] = $template['name'];
			}//end foreach
		}//end if

		// Backwards compatibility
		if ( isset( $data['pod_pages'] ) ) {
			$data['pages'] = $data['pod_pages'];

			unset( $data['pod_pages'] );
		}

		if ( isset( $data['pages'] ) && is_array( $data['pages'] ) ) {
			foreach ( $data['pages'] as $page_data ) {
				$page = self::import_pod_page( $page_data, $replace );

				if ( ! $page ) {
					continue;
				}

				if ( ! isset( $found['pages'] ) ) {
					$found['pages'] = array();
				}

				$found['pages'][ $page['name'] ] = $page['name'];
			}//end foreach
		}//end if

		if ( isset( $data['helpers'] ) && is_array( $data['helpers'] ) ) {
			foreach ( $data['helpers'] as $helper_data ) {
				$helper = self::import_pod_helper( $helper_data, $replace );

				if ( ! $helper ) {
					continue;
				}

				if ( ! isset( $found['helpers'] ) ) {
					$found['helpers'] = array();
				}

				$found['helpers'][ $helper['name'] ] = $helper['name'];
			}//end foreach
		}//end if

		$found = apply_filters( 'pods_packages_import', $found, $data, $replace );

		if ( ! empty( $found ) ) {
			self::$api->cache_flush_pods();

			return $found;
		}

		return false;
	}

	/**
	 * Get the metadata from the package data.
	 *
	 * @since 2.9.0
	 *
	 * @param array $data The package data.
	 *
	 * @return false|array The metadata or false if no metadata is found.
	 */
	public static function get_meta_from_package( array $data ) {
		// Get the meta property if set.
		if ( isset( $data['@meta'] ) ) {
			$meta = $data['@meta'];
		} elseif ( isset( $data['meta'] ) ) {
			$meta = $data['meta'];
		} else {
			return false;
		}

		if ( empty( $meta['version'] ) ) {
			return false;
		}

		// Attempt to adjust the version if needed for compatibility.
		$has_dot_versioning = false !== strpos( $meta['version'], '.' );

		if ( ! $has_dot_versioning ) {
			if ( (int) $meta['version'] < 1000 ) {
				// Pods 1.x < 1.10
				$meta['version'] = implode( '.', str_split( $meta['version'] ) );
			} elseif ( function_exists( 'pods_version_to_point' ) ) {
				// Pods 1.10 <= 2.0
				$meta['version'] = pods_version_to_point( $meta['version'] );
			} else {
				// Default to 1.10 if we can't convert it.
				$meta['version'] = '1.10';
			}
		}

		return $meta;
	}

	/**
	 * Handle importing of the settings.
	 *
	 * @since 2.9.0
	 *
	 * @param array $data The import data.
	 */
	public static function import_settings( $data ) {
		pods_update_settings( $data );
	}

	/**
	 * Handle importing of the pod.
	 *
	 * @since 2.8.0
	 *
	 * @param array $data    The import data.
	 * @param bool  $replace Whether to replace an existing configuration if found.
	 *
	 * @return array|\Pods\Whatsit|false The imported object or false if failed.
	 */
	public static function import_pod( $data, $replace = false ) {
		if ( isset( $data['id'] ) ) {
			unset( $data['id'] );
		}

		$pod_object = self::$api->load_pod( [ 'name' => $data['name'] ], false );

		$pod = [
			'groups' => [],
			'fields' => [],
		];

		$existing_groups = [];
		$existing_fields = [];

		if ( $pod_object ) {
			// Convert to an array.
			$pod = $pod_object->get_args();

			if ( ! $replace ) {
				// We will still be replacing any groups/fields that are sent.
				$replace = true;

				// Append to the groups and fields.
				$existing_groups = $pod_object['groups'];
				$existing_fields = $pod_object['fields'];
			}
		}

		$pod_data = $data;

		// Backwards compatibility
		if ( version_compare( self::$package_meta_version, '2.0', '<' ) ) {
			$pod_data = self::import_pod_prepare( $data, $pod );
		}

		$pod = pods_config_merge_data( $pod, $pod_data );

		$reserved_context = ( 'pod' === $pod['type'] || 'table' === $pod['type'] ) ? 'pods' : 'wp';

		if ( in_array( $pod['name'], pods_reserved_keywords( $reserved_context ), true ) ) {
			// Extending objects when using reserved keywords.
			// This will then accept `post`, `page` etc. as Pods object names.
			$pod['create_extend'] = 'extend';
		}

		if ( ! empty( $pod['groups'] ) ) {
			$pod['groups'] = self::import_pod_setup_objects( $pod['groups'], $existing_groups, $existing_fields );
		} elseif ( ! empty( $pod['fields'] ) ) {
			$pod['fields'] = self::import_pod_setup_objects( $pod['fields'], $existing_fields );
		}

		// Force 2.8 orphan field check.
		$pod['_migrated_28'] = false;

		$pod['overwrite'] = $replace;

		self::$api->save_pod( $pod );

		return $pod;
	}

	/**
	 * Handle setting up the objects for importing.
	 *
	 * @since 2.8.0
	 *
	 * @param array $objects          The objects to set up.
	 * @param array $existing_objects List of existing objects.
	 * @param array $existing_fields  List of existing fields (if the objects support fields).
	 *
	 * @return array The objects that were set up.
	 */
	private static function import_pod_setup_objects( array $objects, array $existing_objects, array $existing_fields = [] ) {
		$unset_args = [
			'id',
			'pod',
			'pod_data',
			'pod_id',
			'group',
			'group_id',
		];

		foreach ( $objects as $key => $object ) {
			foreach ( $unset_args as $unset_arg ) {
				if ( isset( $object[ $unset_arg ] ) ) {
					unset( $object[ $unset_arg ] );
				}
			}

			if ( isset( $existing_objects[ $object['name'] ] ) ) {
				// Set the ID as we need to be updating the existing object.
				$object['id'] = $existing_objects[ $object['name'] ]['id'];
			}

			if ( ! empty( $object['fields'] ) ) {
				$object['fields'] = self::import_pod_setup_objects( $object['fields'], $existing_fields );
			}

			$objects[ $key ] = $object;
		}

		return $objects;
	}

	/**
	 * Handle import preparation of the pod.
	 *
	 * @since 2.8.0
	 *
	 * @param array $data The import data.
	 * @param array $pod  The existing pod (if set).
	 *
	 * @return array|false The prepared pod or false if failed.
	 */
	public static function import_pod_prepare( $data, $pod ) {
		$core_fields = [
			[
				'name'    => 'created',
				'label'   => 'Date Created',
				'type'    => 'datetime',
				'options' => [
					'datetime_format'      => 'ymd_slash',
					'datetime_time_type'   => '12',
					'datetime_time_format' => 'h_mm_ss_A',
				],
				'weight'  => 1,
			],
			[
				'name'    => 'modified',
				'label'   => 'Date Modified',
				'type'    => 'datetime',
				'options' => [
					'datetime_format'      => 'ymd_slash',
					'datetime_time_type'   => '12',
					'datetime_time_format' => 'h_mm_ss_A',
				],
				'weight'  => 2,
			],
			[
				'name'        => 'author',
				'label'       => 'Author',
				'type'        => 'pick',
				'pick_object' => 'user',
				'options'     => [
					'pick_format_type'   => 'single',
					'pick_format_single' => 'autocomplete',
					'default_value'      => '{@user.ID}',
				],
				'weight'      => 3,
			],
		];

		$found_fields = [];

		if ( ! empty( $data['fields'] ) ) {
			foreach ( $data['fields'] as $k => $field ) {
				if ( in_array( $field['name'], $found_fields, true ) ) {
					return false;
				}

				$field = self::import_pod_field( $field );

				if ( ! $field ) {
					continue;
				}

				$found_fields[] = $field['name'];

				if ( isset( $pod['fields'][ $field['name'] ] ) ) {
					$field = pods_config_merge_data( $pod['fields'][ $field['name'] ], $field );
				}

				$data['fields'][ $k ] = $field;
			}//end foreach
		}//end if

		if ( (int) pods_v( 'id', $pod, 0 ) < 1 ) {
			$data['fields'] = array_merge( $core_fields, $data['fields'] );
		}

		if ( empty( $data['label'] ) ) {
			$data['label'] = ucwords( str_replace( '_', ' ', $data['name'] ) );
		}

		if ( isset( $data['is_toplevel'] ) ) {
			$data['show_in_menu'] = ( 1 === (int) $data['is_toplevel'] ? 1 : 0 );

			unset( $data['is_toplevel'] );
		}

		$mapped_deprecated_options = [
			'detail_page' => 'detail_url',
			'before_helpers' => 'pre_save_helpers',
			'after_helpers' => 'post_save_helpers',
			'pre_drop_helpers' => 'pre_delete_helpers',
			'post_drop_helpers' => 'post_delete_helpers',
		];

		foreach ( $mapped_deprecated_options as $mapped_deprecated_option => $new_option ) {
			if ( ! isset( $data[ $mapped_deprecated_option ] ) ) {
				continue;
			}

			$data[ $new_option ] = $data[ $mapped_deprecated_option ];

			unset( $data[ $mapped_deprecated_option ] );
		}

		$data['name'] = pods_clean_name( $data['name'] );

		// Set up basic pod configuration (ACT).
		return [
			'name'    => $data['name'],
			'label'   => $data['label'],
			'type'    => 'pod',
			'storage' => 'table',
			'fields'  => $data['fields'],
			'options' => [
				'pre_save_helpers'    => pods_v( 'pre_save_helpers', $data ),
				'post_save_helpers'   => pods_v( 'post_save_helpers', $data ),
				'pre_delete_helpers'  => pods_v( 'pre_delete_helpers', $data ),
				'post_delete_helpers' => pods_v( 'post_delete_helpers', $data ),
				'show_in_menu'        => ( 1 === (int) pods_v( 'show_in_menu', $data, 0 ) ? 1 : 0 ),
				'detail_url'          => pods_v( 'detail_url', $data ),
				'pod_index'           => 'name',
			],
		];
	}

	/**
	 * Handle import preparation of the field.
	 *
	 * @since 2.8.0
	 *
	 * @param array $data The import data.
	 *
	 * @return array|false The prepared field or false if failed.
	 */
	public static function import_pod_field_prepare( $data ) {
		// Easy import if we aren't dealing with deprecated mappings.
		if ( ! isset( $data['coltype'] ) ) {
			return $data;
		}

		// Handle deprecated field import preparation.
		$mapped_deprecated_types = [
			'txt'  => 'text',
			'desc' => 'wysiwyg',
			'code' => 'paragraph',
			'bool' => 'boolean',
			'num'  => 'number',
			'date' => 'datetime',
		];

		// Get field type but check for deprecated coltype.
		$field_type = pods_v( 'type', $data, pods_v( 'coltype', $data ) );

		if ( empty( $field_type ) ) {
			return false;
		}

		if ( isset( $mapped_deprecated_types[ $field_type ] ) ) {
			$field_type = $mapped_deprecated_types[ $field_type ];
		}

		$new_field = [
			'name'         => trim( (string) pods_v( 'name', $data, '' ) ),
			'label'        => trim( (string) pods_v( 'label', $data, '' ) ),
			'description'  => trim( (string) pods_v( 'description', $data, pods_v( 'comment', $data, '' ) ) ),
			'type'         => $field_type,
			'weight'       => (int) $data['weight'],
			'required'     => 1 === (int) $data['required'] ? 1 : 0,
			'unique'       => 1 === (int) $data['unique'] ? 1 : 0,
		];

		if ( isset( $data['input_helper'] ) ) {
			$new_field['input_helper'] = $data['input_helper'];
		}

		if ( 'pick' === $field_type ) {
			$mapped_deprecated_relationship_values = [
				'wp_user'     => 'user',
				'wp_post'     => 'post_type-post',
				'wp_page'     => 'post_type-page',
				'wp_taxonomy' => 'taxonomy-category',
			];

			if ( isset( $data['pickval'] ) ) {
				$new_field['pick_object'] = 'pod';
				$new_field['pick_val']    = $data['pickval'];

				if ( isset( $mapped_deprecated_relationship_values[ $data['pickval'] ] ) ) {
					$new_field['pick_object'] = $mapped_deprecated_relationship_values[ $data['pickval'] ];
				}
			}

			// @todo Add sister field ID mapping.

			// This won't work if the field doesn't exist
			// $new_field['sister_id'] = $data['sister_field_id'];
			$new_field['options']['pick_filter']  = $data['pick_filter'];
			$new_field['options']['pick_orderby'] = $data['pick_orderby'];
			$new_field['options']['pick_display'] = '';
			$new_field['options']['pick_size']    = 'medium';

			if ( 1 === (int) $data['multiple'] ) {
				$new_field['options']['pick_format_type']  = 'multi';
				$new_field['options']['pick_format_multi'] = 'checkbox';
				$new_field['options']['pick_limit']        = 0;
			} else {
				$new_field['options']['pick_format_type']   = 'single';
				$new_field['options']['pick_format_single'] = 'dropdown';
				$new_field['options']['pick_limit']         = 1;
			}
		} elseif ( 'file' === $field_type ) {
			$new_field['options']['file_format_type'] = 'multi';
			$new_field['options']['file_type']        = 'any';
		} elseif ( 'number' === $field_type ) {
			$new_field['options']['number_decimals'] = 2;
		} elseif ( isset( $data['coltype'] ) && 'desc' === $data['coltype'] ) {
			$new_field['options']['wysiwyg_editor'] = 'tinymce';
		} elseif ( 'text' === $field_type ) {
			$new_field['options']['text_max_length'] = 128;
		}//end if

		return $new_field;
	}

	/**
	 * Handle importing of the pod template.
	 *
	 * @since 2.8.0
	 *
	 * @param array $data    The import data.
	 * @param bool  $replace Whether to replace an existing configuration if found.
	 *
	 * @return array|\Pods\Whatsit|false The imported object or false if failed.
	 */
	public static function import_pod_template( $data, $replace = false ) {
		if ( isset( $data['id'] ) ) {
			unset( $data['id'] );
		}

		$template = self::$api->load_template( [
			'name' => $data['name'],
		] );

		if ( ! empty( $template ) ) {
			// Delete Template if it exists
			if ( $replace ) {
				self::$api->delete_template( [ 'id' => $template['id'] ] );

				$template = [];
			}
		} else {
			$template = [];
		}

		$template_id = (int) pods_v( 'id', $template );

		$template = pods_config_merge_data( $template, $data );

		if ( $template instanceof \Pods\Whatsit\Template ) {
			$template = $template->get_args();

			$excluded_args = [
				'object_type',
				'object_storage_type',
				'parent',
				'group',
				'label',
				'description',
				'slug',
				'weight',
			];

			foreach ( $excluded_args as $excluded_arg ) {
				if ( isset( $template[ $excluded_arg ] ) ) {
					unset( $template[ $excluded_arg ] );
				}
			}
		} elseif ( ! empty( $template['options'] ) ) {
			$template = pods_config_merge_data( $template, $template['options'] );

			unset( $template['options'] );
		}

		if ( 0 < $template_id ) {
			$template['id'] = $template_id;
		}

		self::$api->save_template( $template );

		return $template;
	}

	/**
	 * Handle importing of the pod page.
	 *
	 * @since 2.8.0
	 *
	 * @param array $data    The import data.
	 * @param bool  $replace Whether to replace an existing configuration if found.
	 *
	 * @return array|\Pods\Whatsit|false The imported object or false if failed.
	 */
	public static function import_pod_page( $data, $replace = false ) {
		if ( isset( $data['id'] ) ) {
			unset( $data['id'] );
		}

		$page = self::$api->load_page( [
			'name' => pods_v( 'name', $data, pods_v( 'uri', $data ), true ),
		] );

		if ( ! empty( $page ) ) {
			// Delete Page if it exists
			if ( $replace ) {
				self::$api->delete_page( [ 'id' => $page['id'] ] );

				$page = [];
			}
		} else {
			$page = [];
		}

		// Backwards compatibility
		if ( isset( $data['uri'] ) ) {
			$data['name'] = $data['uri'];

			unset( $data['uri'] );
		}

		if ( isset( $data['phpcode'] ) ) {
			$data['code'] = $data['phpcode'];

			unset( $data['phpcode'] );
		}

		$page_id = (int) pods_v( 'id', $page );

		$page = pods_config_merge_data( $page, $data );

		if ( $page instanceof \Pods\Whatsit\Page ) {
			$page = $page->get_args();

			$excluded_args = [
				'object_type',
				'object_storage_type',
				'parent',
				'group',
				'label',
				'description',
				'slug',
				'weight',
			];

			foreach ( $excluded_args as $excluded_arg ) {
				if ( isset( $template[ $excluded_arg ] ) ) {
					unset( $template[ $excluded_arg ] );
				}
			}
		} elseif ( ! empty( $page['options'] ) ) {
			$page = pods_config_merge_data( $page, $page['options'] );

			unset( $page['options'] );
		}

		if ( 0 < $page_id ) {
			$page['id'] = $page_id;
		}

		$page['name'] = trim( $page['name'], '/' );

		self::$api->save_page( $page );

		return $page;
	}

	/**
	 * Handle importing of the pod helper.
	 *
	 * @since 2.8.0
	 *
	 * @param array $data    The import data.
	 * @param bool  $replace Whether to replace an existing configuration if found.
	 *
	 * @return array|\Pods\Whatsit|false The imported object or false if failed.
	 */
	public static function import_pod_helper( $data, $replace = false ) {
		if ( isset( $data['id'] ) ) {
			unset( $data['id'] );
		}

		$helper = self::$api->load_helper( [ 'name' => $data['name'] ] );

		if ( ! empty( $helper ) ) {
			// Delete Helper if it exists
			if ( $replace ) {
				self::$api->delete_helper( [ 'id' => $helper['id'] ] );

				$helper = [];
			}
		} else {
			$helper = [];
		}

		// Backwards compatibility
		if ( isset( $data['phpcode'] ) ) {
			$data['code'] = $data['phpcode'];

			unset( $data['phpcode'] );
		}

		if ( isset( $data['type'] ) ) {
			if ( 'before' === $data['type'] ) {
				$data['type'] = 'pre_save';
			} elseif ( 'after' === $data['type'] ) {
				$data['type'] = 'post_save';
			}
		}

		$helper = pods_config_merge_data( $helper, $data );

		if ( isset( $helper['type'] ) ) {
			$helper['helper_type'] = $helper['type'];

			unset( $helper['helper_type'] );
		}

		self::$api->save_helper( $helper );

		return $helper;
	}

	/**
	 * Export a Package.
	 *
	 * $params['pods'] string|array|bool Pod IDs to export, or set to true to export all
	 * $params['templates'] string|array|bool Template IDs to export, or set to true to export all
	 * $params['pages'] string|array|bool Page IDs to export, or set to true to export all
	 * $params['helpers'] string|array|bool Helper IDs to export, or set to true to export all
	 *
	 * @since 2.0.5
	 *
	 * @param array $params The list of things to export.
	 *
	 * @return array|false The package export or false if there was a failure.
	 *
	 */
	public static function export( $params ) {
		$export = [
			'@meta' => [
				'version' => PODS_VERSION,
				'build'   => time(),
			],
		];

		if ( is_object( $params ) ) {
			$params = get_object_vars( $params );
		}

		self::$api = pods_api();

		$setting_keys = pods_v( 'settings', $params );
		$pod_ids      = pods_v( 'pods', $params );
		$template_ids = pods_v( 'templates', $params );
		$page_ids     = pods_v( 'pages', $params );
		$helper_ids   = pods_v( 'helpers', $params );

		if ( ! empty( $setting_keys ) ) {
			$export['settings'] = [];

			if ( in_array( 'all', $setting_keys, true ) ) {
				$export['settings'] = pods_get_settings();

				if ( isset( $export['settings']['wisdom_registered_setting'] ) ) {
					unset( $export['settings']['wisdom_registered_setting'] );
				}
			} else {
				foreach ( $setting_keys as $setting_key ) {
					$setting = pods_get_setting( $setting_key );

					if ( null !== $setting ) {
						$export['settings'][ $setting_key ] = $setting;
					}
				}
			}

			/**
			 * Allow filtering the list of settings being exported to prevent potentially sensitive third-party settings from being exposed.
			 *
			 * @since 2.9.0
			 *
			 * @param array $settings The list of settings being exported.
			 */
			$export['settings'] = apply_filters( 'pods_migrate_packages_export_settings', $export['settings'] );
		}

		if ( ! empty( $pod_ids ) ) {
			$api_params = [];

			if ( true !== $pod_ids ) {
				$api_params['ids'] = (array) $pod_ids;
			}

			$export['pods'] = self::$api->load_pods( $api_params );
			$export['pods'] = array_map( static function( $pod ) {
				return $pod->export( [
					'include_fields'      => false,
					'build_default_group' => true,
				] );
			}, $export['pods'] );

			$options_ignore = array(
				'pod_id',
				'parent',
				'old_name',
				'podType',
				'storageType',
				'object_storage_type',
				'object_type',
				'object_name',
				'object_hierarchical',
				'table',
				'meta_table',
				'pod_table',
				'field_id',
				'field_index',
				'field_slug',
				'field_type',
				'field_parent',
				'field_parent_select',
				'meta_field_id',
				'meta_field_index',
				'meta_field_value',
				'pod_field_id',
				'pod_field_index',
				'object_fields',
				'join',
				'where',
				'where_default',
				'orderby',
				'pod',
				'recurse',
				'table_info',
				'pod_data',
				'attributes',
				'group',
				'grouped',
				'developer_mode',
				'dependency',
				'depends-on',
				'excludes-on',
				'_locale',
			);

			$field_types = PodsForm::field_types();

			$field_type_options = array();

			foreach ( $field_types as $type => $field_type_data ) {
				$field_type_options[ $type ] = PodsForm::ui_options( $type );
			}

			foreach ( $export['pods'] as $pod_key => $pod ) {
				foreach ( $pod as $option => $option_value ) {
					if ( null === $option_value || in_array( $option, $options_ignore, true ) ) {
						unset( $pod[ $option ] );
					}
				}

				if ( ! empty( $pod['groups'] ) ) {
					foreach ( $pod['groups'] as $group_key => $group ) {
						foreach ( $group as $option => $option_value ) {
							if ( null === $option_value || in_array( $option, $options_ignore, true ) ) {
								unset( $group[ $option ] );
							}
						}

						if ( ! empty( $group['fields'] ) ) {
							foreach ( $group['fields'] as $field_key => $field ) {
								foreach ( $field as $option => $option_value ) {
									if ( null === $option_value || in_array( $option, $options_ignore, true ) ) {
										unset( $field[ $option ] );
									}
								}

								foreach ( $field_type_options as $type => $options ) {
									if ( $type === pods_v( 'type', $field ) ) {
										continue;
									}

									foreach ( $options as $option_data ) {
										if (
											isset( $option_data['group'] )
											&& is_array( $option_data['group'] )
											&& ! empty( $option_data['group'] )
										) {
											if ( isset( $field[ $option_data['name'] ] ) ) {
												unset( $field[ $option_data['name'] ] );
											}

											foreach ( $option_data['group'] as $group_option_data ) {
												if ( isset( $field[ $group_option_data['name'] ] ) ) {
													unset( $field[ $group_option_data['name'] ] );
												}
											}
										} elseif ( isset( $field[ $option_data['name'] ] ) ) {
											unset( $field[ $option_data['name'] ] );
										}
									}
								}//end foreach

								$group['fields'][ $field_key ] = $field;
							}//end foreach

							$group['fields'] = array_values( $group['fields'] );
						}//end if

						$pod['groups'][ $group_key ] = $group;
					}//end foreach

					$pod['groups'] = array_values( $pod['groups'] );
				}//end if

				$export['pods'][ $pod_key ] = $pod;
			}//end foreach

			$export['pods'] = array_values( $export['pods'] );
		}//end if

		$excluded_args = [
			'label',
			'description',
			'slug',
			'weight',
		];

		if ( ! empty( $template_ids ) ) {
			$api_params = array();

			if ( true !== $template_ids ) {
				$api_params['ids'] = (array) $template_ids;
			}

			$export['templates'] = array_values( self::$api->load_templates( $api_params ) );

			foreach ( $export['templates'] as $k => $template ) {
				$template = $template->get_clean_args();

				foreach ( $excluded_args as $excluded_arg ) {
					if ( isset( $template[ $excluded_arg ] ) ) {
						unset( $template[ $excluded_arg ] );
					}
				}

				$export['templates'][ $k ] = $template;
			}
		}

		if ( ! empty( $page_ids ) ) {
			$api_params = array();

			if ( true !== $page_ids ) {
				$api_params['ids'] = (array) $page_ids;
			}

			$export['pages'] = array_values( self::$api->load_pages( $api_params ) );

			foreach ( $export['pages'] as $k => $page ) {
				$page = $page->get_clean_args();

				foreach ( $excluded_args as $excluded_arg ) {
					if ( isset( $page[ $excluded_arg ] ) ) {
						unset( $page[ $excluded_arg ] );
					}
				}

				$export['pages'][ $k ] = $page;
			}
		}

		if ( ! empty( $helper_ids ) ) {
			$api_params = array();

			if ( true !== $helper_ids ) {
				$api_params['ids'] = (array) $helper_ids;
			}

			$export['helpers'] = array_values( self::$api->load_helpers( $api_params ) );

			foreach ( $export['helpers'] as $k => $helper ) {
				$helper = $helper->get_clean_args();

				foreach ( $excluded_args as $excluded_arg ) {
					if ( isset( $helper[ $excluded_arg ] ) ) {
						unset( $helper[ $excluded_arg ] );
					}
				}

				$export['helpers'][ $k ] = $helper;
			}
		}

		/**
		 * Allow filtering the package being exported.
		 *
		 * @since 2.0.5
		 *
		 * @param array $export The export package.
		 * @param array $params The list of things to export.
		 */
		$export = apply_filters( 'pods_packages_export', $export, $params );

		if ( 1 === count( $export ) ) {
			return false;
		}

		return wp_json_encode( $export, JSON_PRETTY_PRINT );
	}
}
