<?php

use Pods\Static_Cache;
use Pods\Whatsit\Pod;
use Pods\Whatsit\Field;
use Pods\Whatsit\Object_Field;

/**
 * @package Pods
 */
class PodsMeta {

	/**
	 * @var PodsAPI
	 */
	private $api;

	/**
	 * @var Pods
	 */
	private static $current_pod;

	/**
	 * @var array
	 */
	private static $current_pod_data;

	/**
	 * @var Pods
	 */
	private static $current_field_pod;

	/**
	 * @var int
	 */
	public static $object_identifier = - 1;

	/**
	 * @var array
	 */
	public static $advanced_content_types = array();

	/**
	 * @var array
	 */
	public static $post_types = array();

	/**
	 * @var array
	 */
	public static $taxonomies = array();

	/**
	 * @var array
	 */
	public static $media = array();

	/**
	 * @var array
	 */
	public static $user = array();

	/**
	 * @var array
	 */
	public static $comment = array();

	/**
	 * @var array
	 */
	public static $settings = array();

	/**
	 * @var array
	 */
	public static $queue = array();

	/**
	 * @var array
	 */
	public static $groups = array();

	/**
	 * @var array
	 */
	public static $old_post_status = array();

	/**
	 * Backwards compatible handling for a pods_meta() call.
	 *
	 * @return \PodsMeta
	 *
	 * @since 2.3.5
	 * @deprecated 2.9.14 Use pods_meta().
	 */
	public static function init() {
		return pods_meta();
	}

	/**
	 * @return \PodsMeta
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

	}

	/**
	 * @return \PodsMeta
	 */
	public function core() {
		// @todo Abstract the static vars into a getter that gets/caches only when the call is needed.
		// @todo Update all usages of self::${$pod_type} to use the new getter.
		// @todo Update all usages of PodsMeta::${$pod_type} to use the new getter.
		$this->cache_pods( false );

		$core_loader_objects = pods_transient_get( 'pods_core_loader_objects' );

		$original_loader_objects = $core_loader_objects;

		if ( ! is_array( $core_loader_objects ) ) {
			$core_loader_objects = [];
		}

		if ( ! isset( $core_loader_objects['taxonomies'] ) ) {
			$core_loader_objects['taxonomies'] = [];

			if ( ! empty( self::$taxonomies ) ) {
				foreach ( self::$taxonomies as $taxonomy ) {
					if ( $taxonomy instanceof Pod ) {
						if ( ! $taxonomy->has_fields() ) {
							continue;
						}
					} elseif ( empty( $taxonomy['fields'] ) ) {
						continue;
					}

					$taxonomy_name = $taxonomy['name'];

					if ( ! empty( $taxonomy['object'] ) ) {
						$taxonomy_name = $taxonomy['object'];
					}

					$core_loader_objects['taxonomies'][] = $taxonomy_name;
				}
			}
		}

		if ( ! isset( $core_loader_objects['media'] ) ) {
			$core_loader_objects['media'] = ! empty( self::$media );
		}

		if ( ! isset( $core_loader_objects['user'] ) ) {
			$core_loader_objects['user'] = ! empty( self::$user );
		}

		if ( ! isset( $core_loader_objects['comment'] ) ) {
			$core_loader_objects['comment'] = ! empty( self::$comment );
		}

		if ( ! isset( $core_loader_objects['settings'] ) ) {
			$core_loader_objects['settings'] = [];

			if ( ! empty( self::$settings ) ) {
				foreach ( self::$settings as $setting_pod ) {
					if ( $setting_pod instanceof Pod ) {
						if ( ! $setting_pod->has_fields() ) {
							continue;
						}
					} elseif ( empty( $setting_pod['fields'] ) ) {
						continue;
					}

					$core_loader_objects['settings'][] = $setting_pod['name'];
				}
			}
		}

		if ( $original_loader_objects !== $core_loader_objects ) {
			pods_transient_set( 'pods_core_loader_objects', $core_loader_objects, WEEK_IN_SECONDS );
		}

		// Handle Post Type Editor (needed for Pods core).
		pods_no_conflict_off( 'post', null, true );

		// Handle Taxonomies.
		if ( ! empty( $core_loader_objects['taxonomies'] ) ) {
			foreach ( $core_loader_objects['taxonomies'] as $taxonomy_name ) {
				pods_no_conflict_off( 'taxonomy', $taxonomy_name, true );
			}
		} else {
			// At least add the hook to delete.
			add_action( 'delete_term_taxonomy', [ $this, 'delete_taxonomy' ], 10, 1 );
		}

		if ( $core_loader_objects['media'] ) {
			pods_no_conflict_off( 'media', null, true );
		} else {
			// At least add the hook to delete.
			add_action( 'delete_attachment', [ $this, 'delete_media' ], 10, 1 );
		}

		if ( $core_loader_objects['user'] ) {
			pods_no_conflict_off( 'user', null, true );
		} else {
			// At least add the hook to delete.
			add_action( 'delete_user', [ $this, 'delete_user' ], 10, 1 );
		}

		if ( $core_loader_objects['comment'] ) {
			pods_no_conflict_off( 'comment', null, true );
		} else {
			// At least add the hook to delete.
			add_action( 'delete_comment', [ $this, 'delete_comment' ], 10, 1 );
		}

		if ( ! empty( $core_loader_objects['settings'] ) ) {
			foreach ( $core_loader_objects['settings'] as $setting_pod_name ) {
				pods_no_conflict_off( 'settings', $setting_pod_name, true );
			}
		}

		if ( is_admin() ) {
			$this->integrations();
		}

		add_action( 'init', array( $this, 'enqueue' ), 9 );

		do_action( 'pods_meta_init', $this );

		return $this;
	}

	/**
	 *
	 */
	public static function enqueue() {
		$type_map = [
			'post_type' => 'post_types',
			'taxonomy'  => 'taxonomies',
			'setting'   => 'settings',
		];

		foreach ( self::$queue as $type => $objects ) {
			if ( isset( $type_map[ $type ] ) ) {
				$type = $type_map[ $type ];
			}

			foreach ( $objects as $name => $object ) {
				if ( isset( self::${$type} ) ) {
					self::${$type}[ $name ] = $object;
				}
			}

			unset( self::$queue[ $type ] );
		}
	}

	/**
	 * Cache the Pods list.
	 *
	 * This is helpful to run to cache the Pods after Polylang has loaded.
	 */
	public function cache_pods( $refresh = true ) {
		$api = pods_api();

		self::$advanced_content_types = $api->load_pods( [
			'type'    => 'pod',
			'refresh' => $refresh,
		] );

		self::$post_types = $api->load_pods( [
			'type'    => 'post_type',
			'refresh' => $refresh,
		] );

		self::$taxonomies = $api->load_pods( [
			'type'    => 'taxonomy',
			'refresh' => $refresh,
		] );

		self::$media = $api->load_pods( [
			'type'    => 'media',
			'refresh' => $refresh,
		] );

		self::$user = $api->load_pods( [
			'type'    => 'user',
			'refresh' => $refresh,
		] );

		self::$comment = $api->load_pods( [
			'type'    => 'comment',
			'refresh' => $refresh,
		] );

		self::$settings = $api->load_pods( [
			'type'    => 'settings',
			'refresh' => $refresh,
		] );
	}

	/**
	 * @param $type
	 * @param $pod
	 *
	 * @return array|bool|int
	 */
	public function register( $type, $pod ) {

		$pod_type = $type;

		if ( 'post_type' === $type ) {
			$type = 'post_types';
		} elseif ( 'taxonomy' === $type ) {
			$type = 'taxonomies';
		} elseif ( 'pod' === $type ) {
			$type = 'advanced_content_types';
		}

		if ( ! isset( self::$queue[ $type ] ) ) {
			self::$queue[ $type ] = array();
		}

		if ( is_array( $pod ) && ! empty( $pod ) && ! isset( $pod['name'] ) ) {
			$data = array();

			foreach ( $pod as $p ) {
				$data[] = $this->register( $type, $p );
			}

			return $data;
		}

		$pod['type'] = $pod_type;
		$pod         = pods_api()->save_pod( $pod, false, false );

		if ( ! empty( $pod ) ) {
			self::$object_identifier --;

			self::$queue[ $type ][ $pod['name'] ] = $pod;

			return $pod;
		}

		return false;
	}

	/**
	 * @param $pod
	 * @param $field
	 *
	 * @return array|bool|int
	 */
	public function register_field( $pod, $field ) {

		if ( is_array( $pod ) && ! empty( $pod ) && ! isset( $pod['name'] ) ) {
			$data = array();

			foreach ( $pod as $p ) {
				$data[] = $this->register_field( $p, $field );
			}

			return $data;
		}

		if ( empty( self::$current_pod_data ) || ! is_object( self::$current_pod_data ) || self::$current_pod_data['name'] != $pod ) {
			self::$current_pod_data = pods_api()->load_pod( array( 'name' => $pod ), false );
		}

		$pod = self::$current_pod_data;

		if ( ! empty( $pod ) ) {
			$type = $pod['type'];

			if ( 'post_type' === $type ) {
				$type = 'post_types';
			} elseif ( 'taxonomy' === $type ) {
				$type = 'taxonomies';
			} elseif ( 'pod' === $type ) {
				$type = 'advanced_content_types';
			}

			if ( ! isset( self::$queue[ $type ] ) ) {
				self::$queue[ $type ] = array();
			}

			$field = pods_api()->save_field( $field, false, false, $pod['id'] );

			if ( ! empty( $field ) ) {
				$pod['fields'][ $field['name'] ] = $field;

				self::$queue[ $type ][ $pod['name'] ] = $pod;

				return $field;
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function integrations() {

		// `AC()` is AC 3.0+, && `AC_FILE` is AC 3.2+
		if ( ! function_exists( 'AC' ) && ! defined( 'AC_FILE' ) ) {
			// Codepress Admin Columns < 2.x
			add_filter( 'cac/storage_model/meta_keys', array( $this, 'cpac_meta_keys' ), 10, 2 );
			add_filter( 'cac/post_types', array( $this, 'cpac_post_types' ), 10, 1 );
			add_filter( 'cac/column/meta/value', array( $this, 'cpac_meta_value' ), 10, 3 );
		} else {
			// Codepress Admin Columns 3.x +
			add_filter( 'ac/column/custom_field/meta_keys', array( $this, 'cpac_meta_keys' ), 10, 2 );
			add_filter( 'ac/post_types', array( $this, 'cpac_post_types' ), 10, 1 );
			add_filter( 'ac/column/value', array( $this, 'cpac_meta_value' ), 10, 3 );
		}
	}

	/**
	 * Admin Columns: Remove internal meta keys + add existing (public) Pod field keys.
	 *
	 * @param array                           $meta_fields
	 * @param \AC_Settings_Column_CustomField $storage_model
	 *
	 * @return array
	 */
	public function cpac_meta_keys( $meta_fields, $storage_model ) {

		$object_type = 'post_type';
		$object      = null;
		$obj         = null;

		if ( ! method_exists( $storage_model, 'get_column' ) ) {
			// Codepress Admin Columns < 2.x
			$object = $storage_model->key;
			$type   = $storage_model->type;
		} else {
			// Codepress Admin Columns 3.x +
			$obj    = $storage_model->get_column();
			$object = $obj->get_list_screen()->get_key();
			$type   = $obj->get_list_screen()->get_meta_type();
		}

		if ( in_array( $type, array( 'wp-links', 'link' ), true ) ) {
			$object_type = 'link';
			$object      = 'link';
		} elseif ( in_array( $type, array( 'wp-media', 'media' ), true ) ) {
			$object_type = 'media';
			$object      = 'media';
		} elseif ( in_array( $type, array( 'wp-users', 'user' ), true ) ) {
			$object_type = 'user';
			$object      = 'user';
		} elseif ( in_array( $type, array( 'wp-comments', 'comment' ), true ) ) {
			$object_type = 'comment';
			$object      = 'comment';
		} elseif ( 'taxonomy' === $type ) {
			$object_type = 'taxonomy';
			if ( ! $obj ) {
				// Codepress Admin Columns < 2.x
				$object = $storage_model->taxonomy;
			} else {
				// Codepress Admin Columns 3.x +
				$object = $obj->get_taxonomy();
			}
		}

		if ( empty( self::$current_pod_data ) || ! is_object( self::$current_pod_data ) || self::$current_pod_data['name'] != $object ) {
			self::$current_pod_data = pods_api()->load_pod( array( 'name' => $object ), false );
		}

		$pod = self::$current_pod_data;

		// Add Pods fields
		if ( ! empty( $pod ) && $object_type == $pod['type'] ) {
			foreach ( $pod['fields'] as $field => $field_data ) {
				if ( ! is_array( $meta_fields ) ) {
					$meta_fields = array();
				}

				if ( ! in_array( $field, $meta_fields ) ) {
					$meta_fields[] = $field;
				}
			}
		}

		// Remove internal Pods fields
		if ( is_array( $meta_fields ) ) {
			foreach ( $meta_fields as $k => $meta_field ) {
				if ( 0 === strpos( $meta_field, '_pods_' ) ) {
					unset( $meta_fields[ $k ] );
				}
			}
		}

		return $meta_fields;
	}

	/**
	 * Admin Columns: Remove internal Pods post types.
	 *
	 * @param  array $post_types
	 *
	 * @return array
	 */
	public function cpac_post_types( $post_types ) {

		foreach ( $post_types as $post_type => $post_type_name ) {
			if ( 0 === strpos( $post_type, '_pods_' ) || 0 === strpos( $post_type_name, '_pods_' ) ) {
				unset( $post_types[ $post_type ] );
			}
		}

		return $post_types;
	}

	/**
	 * Admin Columns: For custom field column types.
	 *
	 * @param mixed      $meta
	 * @param int        $id
	 * @param \AC_Column $obj
	 *
	 * @return string
	 */
	public function cpac_meta_value( $meta, $id, $obj ) {

		$tableless_field_types = PodsForm::tableless_field_types();

		$object_type = 'post_type';
		$object      = null;
		$type        = null;

		if ( ! method_exists( $obj, 'get_list_screen' ) ) {
			// Codepress Admin Columns < 2.x
			$object = $obj->storage_model->key;
			$type   = $obj->storage_model->type;
		} else {
			// Codepress Admin Columns 3.x +
			$object = $obj->get_list_screen()->get_key();
			$type   = $obj->get_list_screen()->get_meta_type();
		}

		if ( in_array( $type, array( 'wp-links', 'link' ), true ) ) {
			$object_type = 'link';
			$object      = 'link';
		} elseif ( in_array( $type, array( 'wp-media', 'media' ), true ) ) {
			$object_type = 'media';
			$object      = 'media';
		} elseif ( in_array( $type, array( 'wp-users', 'user' ), true ) ) {
			$object_type = 'user';
			$object      = 'user';
		} elseif ( in_array( $type, array( 'wp-comments', 'comment' ), true ) ) {
			$object_type = 'comment';
			$object      = 'comment';
		} elseif ( 'taxonomy' === $type ) {
			$object_type = 'taxonomy';
			if ( ! method_exists( $obj, 'get_taxonomy' ) ) {
				// Codepress Admin Columns < 2.x
				$object = $obj->storage_model->taxonomy;
			} else {
				// Codepress Admin Columns 3.x +
				$object = $obj->get_taxonomy();
			}
		}

		$field = $obj->get_option( 'field' );

		if ( $field && 'cpachidden' === substr( $field, 0, 10 ) ) {
			$field = str_replace( 'cpachidden', '', $field );
		}

		$field_type = $obj->get_option( 'field_type' );

		if ( empty( self::$current_pod_data ) || ! is_object( self::$current_pod_data ) || self::$current_pod_data['name'] !== $object ) {
			self::$current_pod_data = pods_api()->load_pod( array( 'name' => $object ), false );
		}

		$pod = self::$current_pod_data;

		// Add Pods fields
		if ( ! empty( $pod ) && isset( $pod['fields'][ $field ] ) ) {
			if ( in_array( $pod['type'], array(
					'post_type',
					'media',
					'taxonomy',
					'user',
					'comment',
					'media'
				), true ) && ( ! empty( $field_type ) || in_array( $pod['fields'][ $field ]['type'], $tableless_field_types, true ) ) ) {
				$metadata_type = $pod['type'];

				if ( in_array( $metadata_type, array( 'post_type', 'media' ), true ) ) {
					$metadata_type = 'post';
				} elseif ( 'taxonomy' === $metadata_type ) {
					$metadata_type = 'term';
				}

				if ( 'term' === $metadata_type && ! function_exists( 'get_term_meta' ) ) {
					$podterms = pods_get_instance( $pod['name'], $id );

					$meta = $podterms->field( $field );
				} else {
					$meta = get_metadata( $metadata_type, $id, $field, ( 'array' !== $field_type ) );
				}
			} elseif ( 'taxonomy' === $pod['type'] ) {
				$podterms = pods_get_instance( $pod['name'], $id );

				$meta = $podterms->field( $field );
			}

			$meta = PodsForm::field_method( $pod['fields'][ $field ]['type'], 'ui', $id, $meta, $field, $pod['fields'][ $field ], $pod['fields'], $pod );
		}

		// Always return a string version.
		if ( is_array( $meta ) && isset( $meta[0] ) ) {
			$meta = pods_serial_comma( $meta, $pod->get_field( $field ) );
		} elseif ( ! is_string( $meta ) ) {
			$meta = '';
		}

		return $meta;
	}

	/**
	 * Add a meta group of fields to add/edit forms
	 *
	 * @param string|array $pod      The pod or type of element to attach the group to.
	 * @param string       $label    Title of the edit screen section, visible to user.
	 * @param string|array $fields   Either a comma separated list of text fields or an associative array containing
	 *                               field infomration.
	 * @param string       $context  (optional) The part of the page where the edit screen section should be shown
	 *                               ('normal', 'advanced', or 'side').
	 * @param string       $priority (optional) The priority within the context where the boxes should show ('high',
	 *                               'core', 'default' or 'low').
	 *
	 * @since 2.0.0
	 *
	 * @return mixed|void
	 */
	public function group_add( $pod, $label, $fields, $context = 'normal', $priority = 'default' ) {
		if ( is_array( $pod ) && ! empty( $pod ) && ! isset( $pod['name'] ) ) {
			foreach ( $pod as $p ) {
				$this->group_add( $p, $label, $fields, $context, $priority );
			}

			return true;
		}

		if ( ! is_array( $pod ) && ! $pod instanceof Pod ) {
			if ( empty( self::$current_pod_data ) || ! is_object( self::$current_pod_data ) || self::$current_pod_data['name'] != $pod ) {
				self::$current_pod_data = pods_api()->load_pod( array( 'name' => $pod ), false );
			}

			if ( ! empty( self::$current_pod_data ) ) {
				$pod = self::$current_pod_data;
			} else {
				$type = 'post_type';

				if ( in_array( $pod, array( 'media', 'user', 'comment' ) ) ) {
					$type = $pod;
				}

				$pod = array(
					'name' => $pod,
					'type' => $type
				);
			}
		}

		if ( is_array( $pod ) && ! isset( $pod['id'] ) ) {
			$defaults = array(
				'name' => '',
				'type' => 'post_type'
			);

			$pod = array_merge( $defaults, $pod );
		}

		if ( 'post' === $pod['type'] ) {
			$pod['type'] = 'post_type';
		}

		if ( empty( $pod['name'] ) && isset( $pod['object'] ) && ! empty( $pod['object'] ) ) {
			$pod['name'] = $pod['object'];
		} elseif ( ! isset( $pod['object'] ) || empty( $pod['object'] ) ) {
			$pod['object'] = $pod['name'];
		}

		$object_name = ! empty( $pod['object'] ) ? $pod['object'] : $pod['name'];

		if ( 'pod' === $pod['type'] ) {
			$object_name = $pod['name'];
		}

		if ( ! isset( self::$groups[ $pod['type'] ] ) ) {
			self::$groups[ $pod['type'] ] = array();
		}

		if ( ! isset( self::$groups[ $pod['type'] ][ $object_name ] ) ) {
			self::$groups[ $pod['type'] ][ $object_name ] = array();
		}

		$_fields = array();

		if ( ! is_array( $fields ) ) {
			$fields = explode( ',', $fields );
		}

		foreach ( $fields as $k => $field ) {
			$name = $k;

			$defaults = array(
				'name' => $name,
			);

			$is_field_object = $field instanceof Field;

			if ( ! is_array( $field ) && ! $is_field_object ) {
				$name = trim( $field );

				$field = array(
					'name' => $name,
				);
			}

			$field = pods_config_merge_data( $defaults, $field );

			$field['name'] = trim( $field['name'] );

			if ( isset( $pod['fields'] ) && isset( $pod['fields'][ $field['name'] ] ) ) {
				$is_field_hidden = 1 === (int) pods_v( 'hidden', $field, 0 );

				$field = pods_config_merge_data( $pod['fields'][ $field['name'] ], $field );

				if ( $field instanceof Field ) {
                    $field = $field->export();
                }

				// If we are adding a field that is hidden, we should override that as no longer hidden now.
				if ( ! $is_field_hidden && isset( $pod['fields'][ $field['name'] ]['hidden'] ) && 1 === (int) $pod['fields'][ $field['name'] ]['hidden'] ) {
                    $field['hidden'] = 0;
                }
			}

			// Set the default type.
			if ( empty( $field['type'] ) ) {
				$field['type'] = 'text';
			}

			if ( empty( $field['label'] ) ) {
				$field['label'] = $field['name'];
			}

			if ( is_array( $field ) ) {
				$field = PodsForm::fields_setup( $field, null, true );
			}

			$_fields[ $k ] = $field;
		}

		$fields = $_fields;

		$group = array(
			'pod'      => $pod,
			'label'    => $label,
			'fields'   => $fields,
			'context'  => $context,
			'priority' => $priority
		);

		$pod_type = $pod['type'];

		// Filter group data, pass vars separately for reference down the line (in case array changed by other filter)
		$group = apply_filters( "pods_meta_group_add_{$pod_type}_{$object_name}", $group, $pod, $label, $fields );
		$group = apply_filters( "pods_meta_group_add_{$pod_type}", $group, $pod, $label, $fields );
		$group = apply_filters( 'pods_meta_group_add', $group, $pod, $label, $fields );

		self::$groups[ $pod['type'] ][ $object_name ][] = $group;

		$this->setup_hooks( $pod );
	}

	/**
	 * Handle setting up hooks for a Pod.
	 *
	 * @since 2.9.14
	 *
	 * @param array|Pod $pod The Pod object.
	 */
	public function setup_hooks( $pod ) {
		$type = pods_v( 'type', $pod );
		$object = pods_v( 'object', $pod, pods_v( 'name', $pod ) );

		// Hook it up!
		if ( 'post_type' === $type ) {
			if ( ! has_action( 'add_meta_boxes', [ $this, 'meta_post_add' ] ) ) {
				pods_no_conflict_off( $type, $object, true );
			}
		} elseif ( 'taxonomy' === $type ) {
			if ( ! has_action( $object . '_edit_form_fields', [ $this, 'meta_taxonomy' ] ) ) {
				pods_no_conflict_off( $type, $object, true );
			}
		} elseif ( 'media' === $type ) {
			if ( ! has_filter( 'wp_update_attachment_metadata', [ $this, 'save_media' ] ) ) {
				pods_no_conflict_off( $type, null, true );
			}
		} elseif ( 'user' === $type ) {
			if ( ! has_action( 'show_user_profile', [ $this, 'meta_user' ] ) ) {
				pods_no_conflict_off( $type, null, true );
			}
		} elseif ( 'comment' === $type ) {
			if ( ! has_filter( 'comment_form_submit_field', [ $this, 'meta_comment_new' ] ) ) {
				pods_no_conflict_off( $type, null, true );
			}
		}
	}

	/**
	 * @param $type
	 * @param $name
	 *
	 * @return array|bool|mixed|void
	 */
	public function object_get( $type, $name ) {

		$object = self::$post_types;

		if ( 'term' === $type ) {
			$type = 'taxonomy';
		}

		if ( 'taxonomy' === $type ) {
			$object = self::$taxonomies;
		} elseif ( 'media' === $type ) {
			$object = self::$media;
		} elseif ( 'user' === $type ) {
			$object = self::$user;
		} elseif ( 'comment' === $type ) {
			$object = self::$comment;
		} elseif ( 'pod' === $type ) {
			$object = self::$advanced_content_types;
		} elseif ( 'settings' === $type ) {
			$object = self::$settings;
		}

		if ( 'pod' !== $type && ! empty( $object ) && is_array( $object ) && isset( $object[ $name ] ) ) {
			$pod = $object[ $name ];
		} else {
			if ( empty( self::$current_pod_data ) || ! is_object( self::$current_pod_data ) || self::$current_pod_data['name'] != $name ) {
				self::$current_pod_data = pods_api()->load_pod( array( 'name' => $name ), false );
			}

			$pod = self::$current_pod_data;
		}

		if ( empty( $pod ) ) {
			return array();
		}

		$defaults = array(
			'name'   => 'post',
			'object' => 'post',
			'type'   => 'post_type'
		);

		$pod = pods_config_merge_data( $defaults, $pod );

		if ( empty( $pod['name'] ) ) {
			$pod['name'] = $pod['object'];
		} elseif ( empty( $pod['object'] ) ) {
			$pod['object'] = $pod['name'];
		}

		if ( $pod['type'] != $type ) {
			return array();
		}

		return $pod;
	}

	/**
	 * Get groups of fields for the content type.
	 *
	 * @param string     $type           Content type.
	 * @param string     $name           Content name.
	 * @param null|array $default_fields List of default fields to include.
	 * @param bool       $full_objects   Whether to return full objects.
	 *
	 * @return array List of groups and their fields.
	 */
	public function groups_get( $type, $name, $default_fields = null, $full_objects = false ) {
		$cache_key = $type . '/' . $name;

		if ( $full_objects ) {
			$cache_key .= '/full';
		}

		$cached = pods_static_cache_get( $cache_key, __CLASS__ . '/groups_get' );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		if ( 'post_type' === $type && 'attachment' === $name ) {
			$type = 'media';
			$name = 'media';
		} elseif ( 'term' === $type ) {
			$type = 'taxonomy';
		}

		do_action( 'pods_meta_groups', $type, $name );

		$pod    = [];
		$fields = [];

		$objects = self::$post_types;

		if ( 'taxonomy' === $type ) {
			$objects = self::$taxonomies;
		} elseif ( 'media' === $type ) {
			$objects = self::$media;
		} elseif ( 'user' === $type ) {
			$objects = self::$user;
		} elseif ( 'comment' === $type ) {
			$objects = self::$comment;
		} elseif ( 'pod' === $type ) {
			$objects = self::$advanced_content_types;
		} elseif ( 'settings' === $type ) {
			$objects = self::$settings;
		}

		if ( ! empty( $objects ) && is_array( $objects ) && isset( $objects[ $name ] ) ) {
			$pod    = $objects[ $name ];
			$fields = $pod['fields'];
		} else {
			if ( empty( self::$current_pod_data ) || ! is_object( self::$current_pod_data ) || self::$current_pod_data['name'] !== $name ) {
				self::$current_pod_data = pods_api()->load_pod( [ 'name' => $name ], false );
			}

			$pod = self::$current_pod_data;

			if ( ! empty( $pod ) && empty( $pod['groups'] ) ) {
				$fields = $pod['fields'];
			}
		}

		if ( null !== $default_fields ) {
			$fields = $default_fields;
		}

		$defaults = [
			'name'   => $name,
			'object' => 'post',
			'type'   => 'post_type',
		];

		if ( is_array( $pod ) ) {
			$pod = array_merge( $defaults, $pod );

			if ( empty( $pod['name'] ) ) {
				$pod['name'] = $pod['object'];
			} elseif ( empty( $pod['object'] ) ) {
				$pod['object'] = $pod['name'];
			}
		}

		if ( $pod && $pod['type'] !== $type ) {
			pods_static_cache_set( $cache_key, [], __CLASS__ . '/groups_get' );

			return [];
		}

		/**
		 * Filter the title of the Pods Metabox used in the post editor.
		 *
		 * @since unknown
		 *
		 * @param string  $title  The title to use, default is 'More Fields'.
		 * @param obj|Pod $pod    Current Pods Object.
		 * @param array   $fields Array of fields that will go in the metabox.
		 * @param string  $type   The type of Pod.
		 * @param string  $name   Name of the Pod.
		 */
		$title = apply_filters( 'pods_meta_default_box_title', __( 'More Fields', 'pods' ), $pod, $fields, $type, $name );

		$groups = [];

		$has_custom_groups = ! empty( self::$groups[ $type ][ $name ] );

		if ( ! empty( $pod['groups'] ) ) {
			$pod_groups = $pod['groups'];

			foreach ( $pod_groups as $group ) {
				if ( empty( $group['fields'] ) ) {
					continue;
				}

				// Maybe provide the full group objects.
				if ( $full_objects ) {
					$groups[ $group['name'] ] = $group;

					continue;
				}

				$groups[] = [
					'pod'                 => $pod,
					'label'               => $group['label'],
					'fields'              => $group['fields'],
					'context'             => pods_v( 'meta_box_context', $group, 'normal', true ),
					'priority'            => pods_v( 'meta_box_priority', $group, 'default', true ),
					'logged_in'           => (int) pods_v( 'logged_in', $group, 0 ),
					'admin_only'          => (int) pods_v( 'admin_only', $group, 0 ),
					'restrict_role'       => (int) pods_v( 'restrict_role', $group, 0 ),
					'restrict_capability' => (int) pods_v( 'restrict_capability', $group, 0 ),
					'roles_allowed'       => pods_v( 'roles_allowed', $group, 'administrator' ),
					'capability_allowed'  => pods_v( 'capability_allowed', $group, '' ),
				];
			}

			if ( $has_custom_groups ) {
				// Clean up the dynamic groups to prevent duplicate fields showing.
				$this->groups_cleanup( $groups, self::$groups[ $type ][ $name ] );

				// Now add our custom groups to those dynamic groups.
				$groups = array_merge( $groups, self::$groups[ $type ][ $name ] );
			}
		} elseif ( $has_custom_groups ) {
			$groups = self::$groups[ $type ][ $name ];
		}

		if ( empty( $groups ) && ! empty( $fields ) ) {
			$groups[] = [
				'pod'                 => $pod,
				'label'               => $title,
				'fields'              => $fields,
				'context'             => 'normal',
				'priority'            => 'default',
				'logged_in'           => 0,
				'admin_only'          => 0,
				'restrict_role'       => 0,
				'restrict_capability' => 0,
				'roles_allowed'       => 'administrator',
				'capability_allowed'  => '',
			];
		}

		/**
		 * Filter the array of field groups
		 *
		 * @since 2.6.6
		 *
		 * @param string $type   The type of Pod
		 * @param string $name   Name of the Pod
		 *
		 * @param array  $groups Array of groups
		 */
		$groups = apply_filters( 'pods_meta_groups_get', $groups, $type, $name );

		pods_static_cache_set( $cache_key, $groups, __CLASS__ . '/groups_get' );

		return $groups;
	}

	/**
	 * Clean up the groups to prevent duplicate fields from showing based on reference groups.
	 *
	 * @since 3.0.7
	 *
	 * @param array $groups           The groups to clean up.
	 * @param array $reference_groups The groups to reference for fields that take precedence.
	 */
	public function groups_cleanup( array &$groups, array &$reference_groups ) {
		$found_fields = [];

		// Remove duplicates fields from reference groups.
		foreach ( $reference_groups as $group_key => $reference_group ) {
			$group_fields = wp_list_pluck( $reference_group['fields'], 'name' );

			foreach ( $group_fields as $field_key => $field_name ) {
				// Remove duplicate fields.
				if ( isset( $found_fields[ $field_name ] ) ) {
					unset( $reference_groups[ $group_key ]['fields'][ $field_key ] );

					continue;
				}

				$found_fields[ $field_name ] = true;
			}
		}

		// Remove duplicates fields from groups.
		foreach ( $groups as $group_key => $group ) {
			$group_fields = wp_list_pluck( $group['fields'], 'name' );

			foreach ( $group_fields as $field_key => $field_name ) {
				// Remove duplicate fields.
				if ( isset( $found_fields[ $field_name ] ) ) {
					unset( $groups[ $group_key ]['fields'][ $field_key ] );

					continue;
				}

				$found_fields[ $field_name ] = true;
			}
		}
	}

	/**
	 * @param      $post_type
	 * @param null $post
	 */
	public function meta_post_add( $post_type, $post = null ) {

		if ( 'comment' === $post_type ) {
			return;
		}

		if ( is_object( $post ) ) {
			$post_type = $post->post_type;
		}

		$groups           = $this->groups_get( 'post_type', $post_type );
		$pods_field_found = false;

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			$field_found  = false;
			$group_hidden = true;

			foreach ( $group['fields'] as $field ) {
				if ( pods_permission( $field ) ) {
					$field_found = true;
				}
				if ( ! isset( $field['hidden'] ) || 1 !== (int) $field['hidden'] ) {
					$group_hidden = false;
				}
			}

			if ( $group_hidden ) {
				continue;
			}

			if ( empty( $group['label'] ) ) {
				$group['label'] = get_post_type_object( $post_type )->labels->label;
			}

			if ( $field_found ) {
				$pods_field_found = true;
				add_meta_box( 'pods-meta-' . sanitize_title( $group['label'] ), wp_kses_post( $group['label'] ), array(
						$this,
						'meta_post'
					), $post_type, $group['context'], $group['priority'], array( 'group' => $group ) );

			}
		}

		if ( $pods_field_found ) {
			// Only add the classes to forms that actually have pods fields
			add_action( 'post_edit_form_tag', array( $this, 'add_class_submittable' ) );
		}
	}

	/**
	 * Handle overriding the "Add title" placeholder.
	 *
	 * @since 2.8.0
	 *
	 * @param string  $placeholder The "Add title" placeholder.
	 * @param WP_Post $post        The post object.
	 *
	 * @return string The "Add title" placeholder.
	 */
	public function meta_post_enter_title_here( $placeholder, $post ) {
		$pod = $this->maybe_set_up_pod( $post->post_type, null, 'post_type' );

		// Check if we have a valid pod.
		if ( ! $pod ) {
			return $placeholder;
		}

		return pods_v( 'placeholder_enter_title_here', $pod->pod_data, $placeholder, true );
	}

	/**
	 * Handle overriding the number of revisions to keep.
	 *
	 * @since 2.8.0
	 *
	 * @param int     $num  Number of revisions to store.
	 * @param WP_Post $post The post object.
	 *
	 * @return int The number of revisions to keep.
	 */
	public function meta_post_revisions_to_keep( $num, $post ) {
		$pod = $this->maybe_set_up_pod( $post->post_type, null, 'post_type' );

		// Check if we have a valid pod.
		if ( ! $pod ) {
			return $num;
		}

		$revisions_to_keep_limit = pods_v( 'revisions_to_keep_limit', $pod->pod_data );

		// Check if we have a valid limit.
		if ( ! is_numeric( $revisions_to_keep_limit ) || 0 === (int) $revisions_to_keep_limit ) {
			return $num;
		}

		return (int) $revisions_to_keep_limit;
	}

	/**
	 *
	 * Called by 'post_edit_form_tag' action to include the classes in the <form> tag
	 *
	 */
	public function add_class_submittable() {
		echo ' class="pods-submittable pods-form"';
	}

	/**
	 * Maybe set up the Pods object or return the current one.
	 *
	 * @since 2.8.0
	 *
	 * @param string      $pod_name The pod name.
	 * @param int|null    $id       The item ID or null to not check ID.
	 * @param string|null $pod_type The pod type if we need to be strict on the check.
	 *
	 * @return bool|Pods The Pods object or false if the pod is invalid.
	 */
	public function maybe_set_up_pod( $pod_name, $id = null, $pod_type = null ) {
		// Check if we have a pod object set up for this pod name yet.
		if ( ! is_object( self::$current_pod ) || self::$current_pod->pod !== $pod_name ) {
			self::$current_pod = pods_get_instance( $pod_name, null, true );
		}

		// Check if we need to strictly check the pod type.
		if ( self::$current_pod instanceof Pods && null !== $pod_type && self::$current_pod->pod_data['type'] !== $pod_type ) {
			self::$current_pod = false;
		}

		// Check if we have a valid pod and if we need to fetch the new ID.
		if ( self::$current_pod instanceof Pods && null !== $id && (int) self::$current_pod->id() !== (int) $id ) {
			self::$current_pod->fetch( $id );
		}

		return self::$current_pod;
	}

	/**
	 * @param $post
	 * @param $metabox
	 */
	public function meta_post( $post, $metabox ) {

		pods_form_enqueue_style( 'pods-form' );
		pods_form_enqueue_script( 'pods' );

		$pod_type      = 'post_type';
		$pod_meta_type = 'post';

		if ( 'attachment' === $post->post_type ) {
			$pod_type      = 'media';
			$pod_meta_type = 'media';
		}

		do_action( 'pods_meta_meta_post', $post );

		$id = null;

		if ( is_object( $post ) ) {
			$id = $post->ID;
		}

		$pod = $this->maybe_set_up_pod( $metabox['args']['group']['pod']['name'], $id, $pod_type );

		$fields = $metabox['args']['group']['fields'];

		/**
		 * Filter the fields used for the Pods metabox group
		 *
		 * @since 2.6.6
		 *
		 * @param array   $fields  Fields from the current Pod metabox group
		 * @param int     $id      Post ID
		 * @param WP_Post $post    Post object
		 * @param array   $metabox Metabox args from the current Pod metabox group
		 * @param Pods    $pod     Pod object
		 */
		$fields = apply_filters( 'pods_meta_post_fields', $fields, $id, $post, $metabox, $pod );

		if ( empty( $fields ) ) {
			esc_html_e( 'There are no fields to display', 'pods' );

			return;
		}

		static $nonced_types = [];

		if ( ! isset( $nonced_types[ $pod_meta_type ] ) ) {
			$nonced_types[ $pod_meta_type ] = true;

			echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_' . $pod_meta_type ), 'hidden' );
		}
		?>
		<table class="form-table pods-metabox pods-admin pods-dependency">
			<?php
			$field_prefix      = 'pods_meta_';
			$field_row_classes = 'form-field pods-field-input';
			$th_scope          = 'row';

			$value_callback = static function( $field_name, $id, $field, $pod ) use ( $pod_meta_type ) {
				pods_no_conflict_on( $pod_meta_type );

				$value = null;

				if ( ! empty( $pod ) ) {
					/** @var Pods $pod */
					$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
				} elseif ( ! empty( $id ) ) {
					$value = get_post_meta( $id, $field['name'], true );
				}

				if ( ! $value && ! is_numeric( $value ) ) {
					$screen = get_current_screen();

					if ( $screen && 'add' === $screen->action ) {
						// Revert to default.
						$value = null;
					}
				}

				pods_no_conflict_off( $pod_meta_type );

				return $value;
			};

			$pre_callback = static function( $field_name, $id, $field, $pod ) use ( $post ) {
				do_action( "pods_meta_meta_post_{$field_name}", $post, $field, $pod );
				do_action( "pods_meta_meta_post_pre_row_{$field_name}", $post, $field, $pod );
			};

			$post_callback = static function( $field_name, $id, $field, $pod ) use ( $post ) {
				do_action( "pods_meta_meta_post_{$field_name}_post", $post, $field, $pod );
				do_action( "pods_meta_meta_post_post_row_{$field_name}", $post, $field, $pod );
			};

			pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );
			?>
		</table>

		<?php do_action( 'pods_meta_meta_post_post', $post ); ?>

		<script type="text/javascript">
			jQuery( function ( $ ) {
				$( document ).Pods( 'submit_meta' );
			} );
		</script>
		<?php
	}

	/**
	 * Handle integration with the transition_post_status hook
	 *
	 * @see wp_transition_post_status
	 *
	 * @param string  $new_status
	 * @param string  $old_status
	 * @param WP_Post $post
	 */
	public function save_post_detect_new( $new_status, $old_status, $post ) {

		if ( $post ) {
			self::$old_post_status[ $post->post_type ] = $old_status;
		}

	}

	/**
	 * Handle integration with the save_post hook
	 *
	 * @see wp_insert_post
	 *
	 * @param int       $post_id
	 * @param WP_Post   $post
	 * @param bool|null $update
	 */
	public function save_post( $post_id, $post, $update = null ) {

		if ( empty( $post ) ) {
			return;
		}

		$is_new_item = false;

		if ( is_bool( $update ) ) {
			$is_new_item = ! $update;
		} // false is new item
		elseif ( isset( self::$old_post_status[ $post->post_type ] ) && in_array( self::$old_post_status[ $post->post_type ], array(
				'new',
				'auto-draft'
			), true ) ) {
			$is_new_item = true;
		}

		$nonced = wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_post' );

		if ( ! $is_new_item && false === $nonced ) {
			return;
		}

		// Unset to avoid manual new post issues
		if ( isset( self::$old_post_status[ $post->post_type ] ) ) {
			unset( self::$old_post_status[ $post->post_type ] );
		}

		$blacklisted_types = array(
			'revision',
			'_pods_pod',
			'_pods_field',
			'_pods_group',
		);

		$blacklisted_types = apply_filters( 'pods_meta_save_post_blacklist_types', $blacklisted_types, $post_id, $post );

		// @todo Figure out how to hook into autosave for saving meta

		// Block Autosave and Revisions
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || in_array( $post->post_type, $blacklisted_types, true ) ) {
			return;
		}

		// Block Quick Edits / Bulk Edits
		if ( 'edit.php' === pods_v( 'pagenow', 'global' ) && ( 'inline-save' === pods_v( 'action', 'post' ) || null !== pods_v( 'bulk_edit' ) || is_array( pods_v( 'post' ) ) ) ) {
			return;
		}

		// Block Trash
		if ( in_array( pods_v( 'action' ), array( 'untrash', 'trash' ), true ) ) {
			return;
		}

		// Block Auto-drafting and Trash (not via Admin action)
		$blacklisted_status = array(
			'auto-draft',
			'trash',
		);

		$blacklisted_status = apply_filters( 'pods_meta_save_post_blacklist_status', $blacklisted_status, $post_id, $post );

		if ( in_array( $post->post_status, $blacklisted_status, true ) ) {
			return;
		}

		$groups = $this->groups_get( 'post_type', $post->post_type );

		$id   = $post_id;
		$pod  = $this->maybe_set_up_pod( $post->post_type, $id, 'post_type' );
		$data = [];

		if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data, false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return;
			}
		}
		// The following code will run for all post_types (not just Pods)

		if ( false !== $nonced && ! empty( $groups ) ) {
			$layout_field_types = PodsForm::layout_field_types();

			foreach ( $groups as $group ) {
				if ( empty( $group['fields'] ) ) {
					continue;
				}

				if ( ! pods_permission( $group ) ) {
					continue;
				}

				foreach ( $group['fields'] as $field ) {
					if ( in_array( $field['type'], $layout_field_types, true ) ) {
						continue;
					}

					if ( ! pods_permission( $field ) ) {
						if ( 1 !== (int) pods_v( 'hidden', $field, 0 ) ) {
							continue;
						}
					}

					$data[ $field['name'] ] = '';

					if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
						$data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
					}

					if ( 'boolean' === $field['type'] ) {
						$data[ $field['name'] ] = (int) $data[ $field['name'] ];
					}
				}
			}

			if ( $is_new_item ) {
				do_action( 'pods_meta_create_pre_post', $data, $pod, $id, $groups, $post, $post->post_type );
				do_action( "pods_meta_create_pre_post_{$post->post_type}", $data, $pod, $id, $groups, $post );
			}

			do_action( 'pods_meta_save_pre_post', $data, $pod, $id, $groups, $post, $post->post_type, $is_new_item );
			do_action( "pods_meta_save_pre_post_{$post->post_type}", $data, $pod, $id, $groups, $post, $is_new_item );
		}

		if ( $is_new_item || false !== $nonced ) {
			pods_no_conflict_on( 'post' );

			if ( ! empty( $pod ) ) {
				// Fix for Pods doing it's own sanitizing.
				$data = pods_unslash( (array) $data );

				$pod->save( $data, null, $id, array(
					'is_new_item' => $is_new_item,
					'podsmeta'    => true,
					'from'        => 'process_form_meta',
				) );
			} elseif ( ! empty( $id ) ) {
				foreach ( $data as $field => $value ) {
					update_post_meta( $id, $field, $value );
				}
			}

			pods_no_conflict_off( 'post' );
		}

		if ( false !== $nonced && ! empty( $groups ) ) {
			if ( $is_new_item ) {
				do_action( 'pods_meta_create_post', $data, $pod, $id, $groups, $post, $post->post_type );
				do_action( "pods_meta_create_post_{$post->post_type}", $data, $pod, $id, $groups, $post );
			}

			do_action( 'pods_meta_save_post', $data, $pod, $id, $groups, $post, $post->post_type, $is_new_item );
			do_action( "pods_meta_save_post_{$post->post_type}", $data, $pod, $id, $groups, $post, $is_new_item );
		}

	}

	/**
	 * Track changed fields before save for posts.
	 *
	 * @param array $data
	 * @param array $postarr
	 *
	 * @return array
	 */
	public function save_post_track_changed_fields( $data, $postarr ) {

		$no_conflict = pods_no_conflict_check( 'post' );

		if ( ! $no_conflict && ! empty( $data['post_type'] ) && ! empty( $postarr['ID'] ) ) {
			$pod = $data['post_type'];
			$id  = $postarr['ID'];

			PodsAPI::handle_changed_fields( $pod, $id, 'reset' );
		}

		return $data;

	}

	/**
	 * @param $form_fields
	 * @param $post
	 *
	 * @return array
	 */
	public function meta_media( $form_fields, $post ) {

		$groups = $this->groups_get( 'media', 'media' );

		if ( empty( $groups ) || 'attachment' === pods_v( 'typenow', 'global' ) ) {
			return $form_fields;
		}

		pods_form_enqueue_style( 'pods-form' );
		pods_form_enqueue_script( 'pods' );

		$id = null;

		if ( is_object( $post ) ) {
			$id = $post->ID;
		}

		$pod = null;

		$meta_nonce = PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_media' ), 'hidden' );

		$did_init = false;

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			if ( null === $pod || ( is_object( $pod ) && (int) $pod->id() !== (int) $id ) ) {
				$pod = $this->maybe_set_up_pod( $group['pod']['name'], $id, 'media' );
			}

			foreach ( $group['fields'] as $field ) {
				if ( ! pods_permission( $field ) ) {
					if ( 1 !== (int) pods_v( 'hidden', $field, 0 ) ) {
						continue;
					}
				}

				// Skip heavy fields.
				if ( in_array( $field['type'], [ 'wysiwyg', 'code', 'file', 'oembed' ], true ) ) {
					continue;
				}

				$value = '';

				pods_no_conflict_on( 'post' );

				if ( ! empty( $pod ) ) {
					$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
				} elseif ( ! empty( $id ) ) {
					$value = get_post_meta( $id, $field['name'], true );
				}

				pods_no_conflict_off( 'post' );

				$form_fields[ 'pods_meta_' . $field['name'] ] = array(
					'label' => $field['label'],
					'input' => 'html',
					'html'  => PodsForm::field( 'pods_meta_' . $field['name'], $value, $field['type'], $field, $pod, $id ),
					'helps' => PodsForm::comment( 'pods_meta_' . $field['name'], $field['description'], $field )
				);

				// Manually force DFV initialization.  This is needed for attachments in "grid mode" in the
				// media library.  Note that this should only occur for attachment_fields_to_edit (see #4785)
				$dfv_init_script = '<script>window.PodsDFV.init(\'script[data-pod="' . $group['pod']['name'] . '"]\');</script>';

				// Only output nonce/init script on the very first field of the first group we have.
				if ( ! $did_init ) {
					$form_fields[ 'pods_meta_' . $field['name'] ]['html'] .= $meta_nonce;
					$form_fields[ 'pods_meta_' . $field['name'] ]['html'] .= $dfv_init_script;

					$did_init = true;
				}

				if ( 'heading' === $field['type'] ) {
					$form_fields[ 'pods_meta_' . $field['name'] ]['html']  = $form_fields[ 'pods_meta_' . $field['name'] ]['label'];
					$form_fields[ 'pods_meta_' . $field['name'] ]['label'] = '';
				} elseif ( 'html' === $field['type'] ) {
					$form_fields[ 'pods_meta_' . $field['name'] ]['label'] = '';
					$form_fields[ 'pods_meta_' . $field['name'] ]['helps'] = '';
				}
			}
		}

		$form_fields = apply_filters( 'pods_meta_meta_media', $form_fields );

		return $form_fields;
	}

	/**
	 * @param $post
	 * @param $attachment
	 *
	 * @return mixed
	 */
	public function save_media( $post, $attachment ) {

		$groups = $this->groups_get( 'media', 'media' );

		if ( empty( $groups ) ) {
			return $post;
		}

		$post_id = $attachment;

		if ( empty( $_POST ) || ! wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_media' ) ) {
			return $post;
		}

		if ( is_array( $post ) && ! empty( $post ) && isset( $post['ID'] ) && 'attachment' === $post['post_type'] ) {
			$post_id = $post['ID'];
		}

		if ( is_array( $post_id ) || empty( $post_id ) ) {
			return $post;
		}

		$data = array();

		$id  = $post_id;
		$pod = null;

		$layout_field_types = PodsForm::layout_field_types();

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			if ( null === $pod || ( is_object( $pod ) && (int) $pod->id() !== (int) $id ) ) {
				$pod = $this->maybe_set_up_pod( $group['pod']['name'], $id, 'media' );
			}

			foreach ( $group['fields'] as $field ) {
				if ( in_array( $field['type'], $layout_field_types, true ) ) {
					continue;
				}

				if ( ! pods_permission( $field ) ) {
					if ( ! pods_v( 'hidden', $field, false ) ) {
						continue;
					}
				}

				$data[ $field['name'] ] = '';

				if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
					$data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
				}
			}
		}

		if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data, false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return $post;
			}
		}

		do_action( 'pods_meta_save_pre_media', $data, $pod, $id, $groups, $post, $attachment );

		if ( ! empty( $pod ) ) {
			// Fix for Pods doing it's own sanitization
			$data = pods_unslash( (array) $data );

			$pod->save( $data, null, $id, array(
				'podsmeta' => true,
				'from'     => 'process_form_meta',
			) );
		} elseif ( ! empty( $id ) ) {
			pods_no_conflict_on( 'post' );

			foreach ( $data as $field => $value ) {
				update_post_meta( $id, $field, $value );
			}

			pods_no_conflict_off( 'post' );
		}

		do_action( 'pods_meta_save_media', $data, $pod, $id, $groups, $post, $attachment );

		return $post;
	}

	/**
	 *
	 */
	public function save_media_ajax() {

		if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) || absint( $_POST['id'] ) < 1 ) {
			return;
		}

		$id = absint( $_POST['id'] );

		if ( ! isset( $_POST['nonce'] ) || empty( $_POST['nonce'] ) ) {
			return;
		}

		check_ajax_referer( 'update-post_' . $id, 'nonce' );

		if ( ! current_user_can( 'edit_post', $id ) ) {
			return;
		}

		$post = get_post( $id, ARRAY_A );

		if ( 'attachment' !== $post['post_type'] ) {
			return;
		}

		// fix ALL THE THINGS

		if ( ! isset( $_REQUEST['attachments'] ) ) {
			$_REQUEST['attachments'] = array();
		}

		if ( ! isset( $_REQUEST['attachments'][ $id ] ) ) {
			$_REQUEST['attachments'][ $id ] = array();
		}

		if ( empty( $_REQUEST['attachments'][ $id ] ) ) {
			$_REQUEST['attachments'][ $id ]['_fix_wp'] = 1;
		}
	}

	/**
	 * @param      $tag
	 * @param null $taxonomy
	 */
	public function meta_taxonomy( $tag, $taxonomy = null ) {

		pods_form_enqueue_style( 'pods-form' );
		pods_form_enqueue_script( 'pods' );

		do_action( 'pods_meta_meta_taxonomy', $tag, $taxonomy );

		$taxonomy_name = $taxonomy;

		if ( ! is_object( $tag ) ) {
			$taxonomy_name = $tag;
		}

		$groups = $this->groups_get( 'taxonomy', $taxonomy_name );

		$id = null;

		if ( is_object( $tag ) ) {
			$id = $tag->term_id;
		}

		$pod = null;

		static $nonced_types = [];

		if ( ! isset( $nonced_types[ $taxonomy_name ] ) ) {
			$nonced_types[ $taxonomy_name ] = true;

			echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_' . $taxonomy_name ), 'hidden' );
		}

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			if ( null === $pod || ( is_object( $pod ) && (int) $pod->id() !== (int) $id ) ) {
				$pod = $this->maybe_set_up_pod( $group['pod']['name'], $id, 'taxonomy' );
			}

			$fields            = array_merge( [
				'_group_title' => [
					'name'        => '_group_title',
					'label'       => $group['label'],
					'type'        => 'heading',
					'heading_tag' => 'h2',
				],
			], $group['fields'] );
			$field_prefix      = 'pods_meta_';
			$field_row_classes = 'form-field';
			$th_scope          = 'row';

			$value_callback = static function( $field_name, $id, $field, $pod ) {
				$value = '';

				pods_no_conflict_on( 'taxonomy' );

				if ( ! empty( $pod ) ) {
					$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
				} elseif ( ! empty( $id ) ) {
					$value = get_term_meta( $id, $field['name'], true );
				}

				pods_no_conflict_off( 'taxonomy' );

				return $value;
			};

			$pre_callback = static function( $field_name, $id, $field, $pod ) use ( $tag ) {
				do_action( "pods_meta_meta_taxonomy_pre_row_{$field_name}", $tag, $field, $pod );
			};

			$post_callback = static function( $field_name, $id, $field, $pod ) use ( $tag ) {
				do_action( "pods_meta_meta_taxonomy_post_row_{$field_name}", $tag, $field, $pod );
			};

			if ( null === $id ) {
				pods_view( PODS_DIR . 'ui/forms/div-rows.php', compact( array_keys( get_defined_vars() ) ) );
			} else {
				pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );
			}
		}

		do_action( 'pods_meta_meta_taxonomy_post', $tag, $taxonomy );
	}

	/**
	 * @param $term_id
	 * @param $term_taxonomy_id
	 * @param $taxonomy
	 */
	public function save_taxonomy( $term_id, $term_taxonomy_id, $taxonomy ) {

		$is_new_item = false;

		if ( 'create_term' === current_filter() ) {
			$is_new_item = true;
		}

		if ( empty( $_POST ) || ! wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_' . $taxonomy ) ) {
			return $term_id;
		}

		// Block Quick Edits / Bulk Edits
		if ( 'inline-save-tax' === pods_v( 'action', 'post' ) || null != pods_v( 'delete_tags', 'post' ) ) {
			return $term_id;
		}

		$groups = $this->groups_get( 'taxonomy', $taxonomy );

		if ( empty( $groups ) ) {
			return $term_id;
		}

		$term = null;

		$id  = $term_id;
		$pod = null;

		$has_fields = false;

		$layout_field_types = PodsForm::layout_field_types();

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			if ( null === $term ) {
				$term = get_term( $term_id, $taxonomy );

				$data = array(
					'name' => $term->name
				);
			}

			$has_fields = true;

			if ( null === $pod || ( is_object( $pod ) && (int) $pod->id() !== (int) $id ) ) {
				$pod = $this->maybe_set_up_pod( $group['pod']['name'], $id, 'taxonomy' );
			}

			foreach ( $group['fields'] as $field ) {
				if ( in_array( $field['type'], $layout_field_types, true ) ) {
					continue;
				}

				if ( ! pods_permission( $field ) ) {
					if ( ! pods_v( 'hidden', $field, false ) ) {
						continue;
					}
				}

				$data[ $field['name'] ] = '';

				if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
					$data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
				}
			}
		}

		if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data, false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return $term_id;
			}
		}

		if ( ! $has_fields ) {
			return $term_id;
		}

		if ( $is_new_item ) {
			do_action( 'pods_meta_create_pre_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
			do_action( "pods_meta_create_pre_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
		}

		do_action( 'pods_meta_save_pre_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy, $is_new_item );
		do_action( "pods_meta_save_pre_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy, $is_new_item );

		pods_no_conflict_on( 'taxonomy' );

		if ( ! empty( $pod ) ) {
			// Fix for Pods doing it's own sanitization
			$data = pods_unslash( (array) $data );

			$pod->save( $data, null, $id, array(
				'is_new_item' => $is_new_item,
				'podsmeta'    => true,
				'from'        => 'process_form_meta',
			) );
		}

		pods_no_conflict_off( 'taxonomy' );

		if ( $is_new_item ) {
			do_action( 'pods_meta_create_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
			do_action( "pods_meta_create_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
		}

		do_action( 'pods_meta_save_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy, $is_new_item );
		do_action( "pods_meta_save_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy, $is_new_item );

		return $term_id;
	}

	/**
	 * Track changed fields before save for terms.
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 */
	public function save_taxonomy_track_changed_fields( $term_id, $taxonomy ) {

		$no_conflict = pods_no_conflict_check( 'term' );

		if ( ! $no_conflict ) {
			$pod = $taxonomy;
			$id  = $term_id;

			PodsAPI::handle_changed_fields( $pod, $id, 'reset' );
		}

	}

	/**
	 * @param $user_id
	 */
	public function meta_user( $user_id ) {
		$is_bbpress_profile = doing_action( 'bbp_user_edit_after' );

		if ( $is_bbpress_profile ) {
			/**
			 * Allow filtering whether to show groups on bbPress profile form.
			 *
			 * @since 2.8.6
			 *
			 * @param bool $show_groups_on_bbpress_profile Whether to show groups on bbPress profile form.
			 */
			$show_groups_on_bbpress_profile = apply_filters( 'pods_meta_user_show_groups_on_bbpress_profile', true );

			if ( ! $show_groups_on_bbpress_profile ) {
				return;
			}
		}

		pods_form_enqueue_style( 'pods-form' );
		pods_form_enqueue_script( 'pods' );

		do_action( 'pods_meta_meta_user', $user_id );

		$groups = $this->groups_get( 'user', 'user' );

		if ( is_object( $user_id ) ) {
			$user    = $user_id;
			$user_id = $user_id->ID;
		} else {
			$user = get_userdata( $user_id );
		}

		$id  = $user_id;
		$pod = null;

		static $nonced = false;

		$show_nonce = true;

		if ( ! $nonced ) {
			$nonced = true;
		} else {
			$show_nonce = false;
		}

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			if ( null === $pod || ( is_object( $pod ) && (int) $pod->id() !== (int) $id ) ) {
				$pod = $this->maybe_set_up_pod( $group['pod']['name'], $id, 'user' );
			}

			$fields            = $group['fields'];
			$field_prefix      = 'pods_meta_';
			$field_row_classes = 'form-field pods-field-input';
			$th_scope          = 'row';

			$value_callback = static function( $field_name, $id, $field, $pod ) {
				$value = '';

				pods_no_conflict_on( 'user' );

				if ( ! empty( $pod ) ) {
					$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
				} elseif ( ! empty( $id ) ) {
					$value = get_user_meta( $id, $field['name'], true );
				}

				pods_no_conflict_off( 'user' );

				return $value;
			};

			$pre_callback = static function( $field_name, $id, $field, $pod ) use ( $user ) {
				do_action( "pods_meta_meta_user_pre_row_{$field_name}", $user, $field, $pod );
			};

			$post_callback = static function( $field_name, $id, $field, $pod ) use ( $user ) {
				do_action( "pods_meta_meta_user_post_row_{$field_name}", $user, $field, $pod );
			};

			if ( $is_bbpress_profile ) {
			?>
				<style type="text/css">
					#bbpress-forums #bbp-your-profile fieldset div.pods-form-ui-field,
					#bbpress-forums #bbp-your-profile fieldset div.pods-form-ui-field div {
						margin: 0;
						float: none;
						width: auto;
						clear: none;
					}
				</style>

				<h2 class="entry-title"><?php echo wp_kses_post( $group['label'] ); ?></h2>

				<fieldset class="bbp-form pods-meta">
					<legend><?php echo wp_kses_post( $group['label'] ); ?></legend>

					<?php if ( $show_nonce ) { ?>
						<?php $show_nonce = false; ?>
						<?php echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_user' ), 'hidden' ); ?>
					<?php } ?>

					<?php pods_view( PODS_DIR . 'ui/forms/div-rows.php', compact( array_keys( get_defined_vars() ) ) ); ?>
				</fieldset>
			<?php } else { ?>
				<h3><?php echo wp_kses_post( $group['label'] ); ?></h3>

				<?php if ( $show_nonce ) { ?>
					<?php $show_nonce = false; ?>
					<?php echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_user' ), 'hidden' ); ?>
				<?php } ?>

				<table class="form-table pods-meta">
					<tbody>
						<?php pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) ); ?>
					</tbody>
				</table>
			<?php
			}
		}

		do_action( 'pods_meta_meta_user_post', $user_id );
	}

	/**
	 * Handle integration with the user_register and profile_update hooks.
	 *
	 * @see wp_insert_user
	 *
	 * @param int         $user_id       User ID.
	 * @param object|null $old_user_data Object containing user's data prior to update.
	 */
	public function save_user( $user_id, $old_user_data = null ) {

		$is_new_item = false;

		if ( 'user_register' === current_filter() ) {
			$is_new_item = true;
		}

		$nonced = wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_user' );

		if ( ! $is_new_item && false === $nonced ) {
			return;
		}

		if ( is_object( $user_id ) ) {
			$user_id = $user_id->ID;
		}

		$groups = $this->groups_get( 'user', 'user' );

		$id   = $user_id;
		$pod  = $this->maybe_set_up_pod( 'user', $id, 'user' );
		$data = [];

		if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data, false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return;
			}
		}

		if ( false !== $nonced && ! empty( $groups ) ) {
			$layout_field_types = PodsForm::layout_field_types();

			foreach ( $groups as $group ) {
				if ( empty( $group['fields'] ) ) {
					continue;
				}

				if ( ! pods_permission( $group ) ) {
					continue;
				}

				foreach ( $group['fields'] as $field ) {
					if ( in_array( $field['type'], $layout_field_types, true ) ) {
						continue;
					}

					if ( ! pods_permission( $field ) ) {
						if ( 1 !== (int) pods_v( 'hidden', $field, 0 ) ) {
							continue;
						}
					}

					$data[ $field['name'] ] = '';

					if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
						$data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
					}
				}
			}

			if ( $is_new_item ) {
				do_action( 'pods_meta_create_pre_user', $data, $pod, $id, $groups );
			}

			do_action( 'pods_meta_save_pre_user', $data, $pod, $id, $groups, $is_new_item );
		}

		if ( $is_new_item || false !== $nonced ) {
			pods_no_conflict_on( 'user' );

			if ( ! empty( $pod ) ) {
				// Fix for Pods doing it's own sanitizing
				$data = pods_unslash( (array) $data );

				$pod->save( $data, null, $id, array(
					'is_new_item' => $is_new_item,
					'podsmeta'    => true,
					'from'        => 'process_form_meta',
				) );
			} elseif ( ! empty( $id ) ) {
				foreach ( $data as $field => $value ) {
					update_user_meta( $id, $field, $value );
				}
			}

			pods_no_conflict_off( 'user' );
		}

		if ( false !== $nonced && ! empty( $groups ) ) {
			if ( $is_new_item ) {
				do_action( 'pods_meta_create_user', $data, $pod, $id, $groups );
			}

			do_action( 'pods_meta_save_user', $data, $pod, $id, $groups, $is_new_item );
		}

	}

	/**
	 * Track changed fields before save for users.
	 *
	 * @param string $user_login
	 *
	 * @return string
	 */
	public function save_user_track_changed_fields( $user_login ) {

		$no_conflict = pods_no_conflict_check( 'user' );

		if ( ! $no_conflict ) {
			$user = get_user_by( 'login', $user_login );

			if ( $user && ! is_wp_error( $user ) ) {
				$pod = 'user';
				$id  = $user->ID;

				PodsAPI::handle_changed_fields( $pod, $id, 'reset' );
			}
		}

		return $user_login;

	}

	/**
	 * @deprecated since 2.8.4
	 */
	public function meta_comment_new_logged_in() {
		return null;
	}

	/**
	 * @param string $submit_field HTML markup for the submit field.
	 *
	 * @return string HTML markup for the submit field.
	 */
	public function meta_comment_new( $submit_field ) {
		ob_start();

		pods_form_enqueue_style( 'pods-form' );
		pods_form_enqueue_script( 'pods' );

		$groups = $this->groups_get( 'comment', 'comment' );

		$id  = null;
		$pod = null;

		static $nonced = false;

		if ( ! $nonced ) {
			$nonced = true;

			echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_comment'  ), 'hidden' );
		}

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			if ( null === $pod || ( is_object( $pod ) && (int) $pod->id() !== (int) $id ) ) {
				$pod = $this->maybe_set_up_pod( $group['pod']['name'], $id, 'comment' );
			}

			$fields            = array_merge( [
				'_group_title' => [
					'name'        => '_group_title',
					'label'       => $group['label'],
					'type'        => 'heading',
					'heading_tag' => 'h3',
				],
			], $group['fields'] );
			$field_prefix      = 'pods_meta_';
			$field_row_classes = 'comment-form-author comment-form-pods-meta';

			$value_callback = static function( $field_name, $id, $field, $pod ) {
				$value = '';

				pods_no_conflict_on( 'comment' );

				if ( ! empty( $pod ) ) {
					$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
				} elseif ( ! empty( $id ) ) {
					$value = get_comment_meta( $id, $field['name'], true );
				}

				pods_no_conflict_off( 'comment' );

				return $value;
			};

			// There is no comment yet.
			$comment = null;

			$pre_callback = static function( $field_name, $id, $field, $pod ) use ( $comment ) {
				do_action( "pods_meta_meta_comment_pre_row_{$field_name}", $comment, $field, $pod );
			};

			$post_callback = static function( $field_name, $id, $field, $pod ) use ( $comment ) {
				do_action( "pods_meta_meta_comment_post_row_{$field_name}", $comment, $field, $pod );
			};

			foreach ( $fields as $field ) {
				$field['name_prefix'] = $field_prefix;

				$hidden_field = 1 === (int) pods_v( 'hidden', $field, 0 );

				if (
					! pods_permission( $field )
					|| ( ! pods_has_permissions( $field ) && $hidden_field )
				) {
					if ( ! $hidden_field ) {
						continue;
					}

					$field['type'] = 'hidden';
				}

				$value = '';

				if ( ! empty( $value_callback ) && is_callable( $value_callback ) ) {
					$value = $value_callback( $field['name'], $id, $field, $pod );
				} elseif ( ! empty( $pod ) ) {
					$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
				}

				$row_classes = $field_row_classes . ' pods-form-ui-row-type-' . $field['type'] . ' pods-form-ui-row-name-' . PodsForm::clean( $field['name'], true );
				$row_classes = trim( $row_classes );

				if ( ! empty( $pre_callback ) && is_callable( $pre_callback ) ) {
					$pre_callback( $field['name'], $id, $field, $pod );
				}

				pods_view( PODS_DIR . 'ui/forms/p-row.php', compact( array_keys( get_defined_vars() ) ) );

				if ( ! empty( $post_callback ) && is_callable( $post_callback ) ) {
					$post_callback( $field['name'], $id, $field, $pod );
				}
			}
		}

		// Add the fields before submit.
		return ob_get_clean() . $submit_field;
	}

	/**
	 * @param      $comment_type
	 * @param null $comment
	 */
	public function meta_comment_add( $comment_type, $comment = null ) {

		if ( is_object( $comment ) && isset( $comment_type->comment_type ) ) {
			$comment_type = $comment->comment_type;
		}

		if ( is_object( $comment_type ) && isset( $comment_type->comment_type ) ) {
			$comment      = $comment_type;
			$comment_type = $comment_type->comment_type;
		}

		if ( is_object( $comment_type ) ) {
			return;
		} elseif ( empty( $comment_type ) ) {
			$comment_type = 'comment';
		}

		$groups = $this->groups_get( 'comment', $comment_type );

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			$field_found = false;

			foreach ( $group['fields'] as $field ) {
				if ( ! PodsForm::permission( $field ) ) {
					if ( 1 === (int) pods_v( 'hidden', $field, 0 ) ) {
						$field_found = true;
						break;
					} else {
						continue;
					}
				} else {
					$field_found = true;
					break;
				}
			}

			if ( $field_found ) {
				add_meta_box( 'pods-meta-' . sanitize_title( $group['label'] ), wp_kses_post( $group['label'] ), array(
						$this,
						'meta_comment'
					), $comment_type, $group['context'], $group['priority'], array( 'group' => $group ) );
			}
		}
	}

	/**
	 * @param $comment
	 * @param $metabox
	 */
	public function meta_comment( $comment, $metabox ) {

		pods_form_enqueue_style( 'pods-form' );
		pods_form_enqueue_script( 'pods' );

		do_action( 'pods_meta_meta_comment', $comment, $metabox );

		$hidden_fields = array();

		static $nonced = false;

		if ( ! $nonced ) {
			$nonced = true;

			echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_comment'  ), 'hidden' );
		}
		?>
		<table class="form-table editcomment pods-metabox">
			<?php
			$id = null;

			if ( is_object( $comment ) ) {
				$id = $comment->comment_ID;
			}

			$pod = $this->maybe_set_up_pod( $metabox['args']['group']['pod']['name'], $id, 'comment' );

			$fields            = $metabox['args']['group']['fields'];
			$field_prefix      = 'pods_meta_';
			$field_row_classes = 'comment-form-author comment-form-pods-meta';

			$value_callback = static function( $field_name, $id, $field, $pod ) {
				$value = '';

				pods_no_conflict_on( 'comment' );

				if ( ! empty( $pod ) ) {
					$value = $pod->field( [ 'name' => $field['name'], 'in_form' => true ] );
				} elseif ( ! empty( $id ) ) {
					$value = get_comment_meta( $id, $field['name'], true );
				}

				pods_no_conflict_off( 'comment' );

				return $value;
			};

			$pre_callback = static function( $field_name, $id, $field, $pod ) use ( $comment ) {
				do_action( "pods_meta_meta_comment_pre_row_{$field_name}", $comment, $field, $pod );
			};

			$post_callback = static function( $field_name, $id, $field, $pod ) use ( $comment ) {
				do_action( "pods_meta_meta_comment_post_row_{$field_name}", $comment, $field, $pod );
			};

			pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );
			?>
		</table>
		<?php
		do_action( 'pods_meta_meta_comment_post', $comment, $metabox );
	}

	/**
	 * @param $approved
	 * @param $commentdata
	 */
	public function validate_comment( $approved, $commentdata ) {

		$groups = $this->groups_get( 'comment', 'comment' );

		if ( empty( $groups ) ) {
			return $approved;
		}

		$data = array();

		$pod = null;
		$id  = null;

		$layout_field_types = PodsForm::layout_field_types();

		$api = pods_api();

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			if ( null === $pod || ( is_object( $pod ) && (int) $pod->id() !== (int) $id ) ) {
				$pod = $this->maybe_set_up_pod( $group['pod']['name'], $id, 'comment' );
			}

			foreach ( $group['fields'] as $field ) {
				if ( in_array( $field['type'], $layout_field_types, true ) ) {
					continue;
				}

				if ( ! pods_permission( $field ) ) {
					if ( ! pods_v( 'hidden', $field, false ) ) {
						continue;
					}
				}

				$data[ $field['name'] ] = '';

				if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
					$data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
				}

				$validate = $api->handle_field_validation( $data[ $field['name'] ], $field['name'], $api->get_wp_object_fields( 'comment' ), $pod->fields(), $pod, array() );

				if ( false === $validate ) {
					$validate = sprintf( __( 'There was an issue validating the field %s', 'pods' ), $field['label'] );
				}

				if ( ! is_bool( $validate ) && ! empty( $validate ) ) {
					return pods_error( $validate, $this );
				}
			}
		}

		return $approved;
	}

	/**
	 * @param $comment_id
	 */
	public function save_comment( $comment_id ) {

		$groups = $this->groups_get( 'comment', 'comment' );

		if ( empty( $groups ) ) {
			return $comment_id;
		} elseif ( empty( $_POST ) ) {
			return $comment_id;
		} elseif ( ! wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_comment' ) ) {
			return $comment_id;
		}

		$data = array();

		$id  = $comment_id;
		$pod = null;

		$layout_field_types = PodsForm::layout_field_types();

		foreach ( $groups as $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			if ( ! pods_permission( $group ) ) {
				continue;
			}

			if ( null === $pod || ( is_object( $pod ) && (int) $pod->id() !== (int) $id ) ) {
				$pod = $this->maybe_set_up_pod( $group['pod']['name'], $id, 'comment' );
			}

			foreach ( $group['fields'] as $field ) {
				if ( in_array( $field['type'], $layout_field_types, true ) ) {
					continue;
				}

				if ( ! pods_permission( $field ) ) {
					if ( ! pods_v( 'hidden', $field, false ) ) {
						continue;
					}
				}

				$data[ $field['name'] ] = '';

				if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
					$data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
				}
			}
		}

		if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data, false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return $comment_id;
			}
		}

		do_action( 'pods_meta_save_pre_comment', $data, $pod, $id, $groups );

		if ( ! empty( $pod ) ) {
			// Fix for Pods doing it's own sanitization
			$data = pods_unslash( (array) $data );

			$pod->save( $data, null, $id, array(
				'podsmeta' => true,
				'from'     => 'process_form_meta',
			) );
		} elseif ( ! empty( $id ) ) {
			pods_no_conflict_on( 'comment' );

			foreach ( $data as $field => $value ) {
				update_comment_meta( $id, $field, $value );
			}

			pods_no_conflict_off( 'comment' );
		}

		do_action( 'pods_meta_save_comment', $data, $pod, $id, $groups );

		return $comment_id;
	}

	/**
	 * Track changed fields before save for comments.
	 *
	 * @param array $data       The new, processed comment data.
	 * @param array $comment    The old, unslashed comment data.
	 * @param array $commentarr The new, raw comment data.
	 *
	 * @return array
	 */
	public function save_comment_track_changed_fields( $data, $comment, $commentarr ) {

		$no_conflict = pods_no_conflict_check( 'user' );

		if ( ! $no_conflict && ! empty( $comment['comment_type'] ) && ! empty( $comment['comment_ID'] ) ) {
			$pod = $comment['comment_type'];
			$id  = $comment['comment_ID'];

			PodsAPI::handle_changed_fields( $pod, $id, 'reset' );
		}

		return $data;

	}

	/**
	 * Get list of keys not covered for an object type.
	 *
	 * @param string $type The object type.
	 *
	 * @return array The list of keys not covered in key=>true format for isset() optimization.
	 */
	public function get_keys_not_covered( $type ) {
		if ( 'post' === $type ) {
			$type = 'post_type';
		} elseif ( 'term' === $type ) {
			$type = 'taxonomy';
		}

		// These are the keys we want to exclude from any additional queries/checks on within Pods.
		$keys_not_covered = [
			'post_type' => [
				// Disable for all protected meta keys.
				'_.*'                  => true,
				// WP core keys.
				'_additional_settings' => true,
				'_edit_last'           => true,
				'_edit_lock'           => true,
				'_thumbnail_id'        => true,
				'_wp_.*'               => true,
				'term_id'              => true,
				'taxonomy'             => true,
				// Optimize for Duplicate Post plugin.
				'_dp_.*'               => true,
				// Optimize for Elementor plugin.
				'_elementor_.*'        => true,
				// Optimize for Divi.
				'_et_pb_.*'            => true,
				'_et_builder_version'  => true,
				// Optimize for WooCommerce.
				'_product_.*'          => true,
				'_downloadable_files'  => true,
				'_currency'            => true,
				'_bundled_cart_item'   => true,
				'saswp_review_details' => true,
				// Optimize for SEOPress.
				'seopress_.*'          => true,
				'edit_seopress_.*'     => true,
			],
			'taxonomy'  => [
				// Disable for all protected meta keys.
				'_.*' => true,
			],
			'user'      => [
				// Disable for all protected meta keys.
				'_.*'                                => true,
				// WP core keys.
				'admin_color'                        => true,
				'capabilities'                       => true,
				'closedpostboxes_.*'                 => true,
				'comment_shortcuts'                  => true,
				'default_password_nag'               => true,
				'description'                        => true,
				'dismissed_wp_pointers'              => true,
				'first_name'                         => true,
				'last_name'                          => true,
				'locale'                             => true,
				'metaboxhidden_'                     => true,
				'metaboxhidden_.*'                   => true,
				'nav_menu_recently_edited'           => true,
				'nickname'                           => true,
				'primary_blog'                       => true,
				'rich_editing'                       => true,
				'session_tokens'                     => true,
				'show_admin_bar_admin'               => true,
				'show_admin_bar_front'               => true,
				'show_per_page'                      => true,
				'show_welcome_panel'                 => true,
				'syntax_highlighting'                => true,
				'use_ssl'                            => true,
				'user_level'                         => true,
				'user-settings'                      => true,
				'dashboard_quick_press_last_post_id' => true,
				// Optimize for Tribe Common.
				'tribe-dismiss-notice'               => true,
				'tribe-dismiss-notice-.*'            => true,
				// Optimize for Beaver Builder.
				'_fl_builder_launched'               => true,
				// Optimize for Gravity Forms.
				'gform_recent_forms'                 => true,
				// Optimize for WooCommerce.
				'paying_customer'                    => true,
				'last_update'                        => true,
				'woocommerce_.*'                     => true,
				'_woocommerce_.*'                    => true,
				'wc_.*'                              => true,
				// Optimize for SEOPress.
				'seopress_.*'          => true,
				'edit_seopress_.*'     => true,
			],
			'settings'  => [
				'fileupload_maxk'                       => true,
				'upload_filetypes'                      => true,
				'upload_space_check_disabled'           => true,
				// Optimize for Duplicate Post plugin.
				'duplicate_post_increase_menu_order_by' => true,
				'duplicate_post_show_notice'            => true,
				'duplicate_post_title_prefix'           => true,
				// Optimize for WooCommerce.
				'woocommerce_.*'                        => true,
				// Optimize for SEOPress.
				'seopress_.*'                           => true,
			],
		];

		/**
		 * Allow filtering the list of keys not covered.
		 *
		 * @since 2.8.0
		 *
		 * @param array  $keys_not_covered The list of keys not covered in key=>true format for isset() optimization.
		 * @param string $type             The object type.
		 */
		$keys_not_covered = apply_filters( 'pods_meta_keys_not_covered', $keys_not_covered, $type );

		// Add prefix-specific keys for user type.
		if ( 'user' === $type ) {
			global $wpdb;

			$prefix = $wpdb->get_blog_prefix();

			$keys = $keys_not_covered['user'];

			foreach ( $keys as $key => $ignored ) {
				$keys_not_covered['user'][ 'wp_' . $key ] = true;
				$keys_not_covered['user'][ $prefix . $key ] = true;
			}
		}

		return isset( $keys_not_covered[ $type ] ) ? $keys_not_covered[ $type ] : [];
	}

	/**
	 * Determine whether the type is covered.
	 *
	 * @since 2.8.8
	 *
	 * @param string      $type        The object type.
	 * @param string|null $object_name The object name.
	 *
	 * @return bool Whether the type is covered.
	 */
	public function is_type_covered( $type, $object_name = null ) {
		if ( 'post' === $type ) {
			$type = 'post_type';
		} elseif ( 'term' === $type ) {
			$type = 'taxonomy';
		}

		$ignored_types = [
			'post_type' => [
				'revision'             => true,
				'nav_menu_item'        => true,
				'custom_css'           => true,
				'customize_changeset'  => true,
				'oembed_cache'         => true,
				'user_request'         => true,
				'wp_block'             => true,
				'wp_global_styles'     => true,
				'wp_navigation'        => true,
				'wp_template'          => true,
				'wp_template_part'     => true,
				// Disable Beaver Builder post types.
				'fl-theme-layout'      => true,
				'fl-builder-template'  => true,
				// Disable Performance Monitor post types (loaded by Nexcess).
				'pm_report'            => true,
				'pm_page'              => true,
				'pm_site_change'       => true,
				'pm_insight'           => true,
				// Disable Elementor post types.
				'e-landing-page'       => true,
				'elementor_library'    => true,
				'elementor_snippet'    => true,
				'elementor_font'       => true,
				'elementor_icons'      => true,
				// Disable WooCommerce post types.
				'product_variation'    => true,
				'shop_order_placehold' => true,
				'shop_order_refund'    => true,
			],
			'taxonomy'  => [
				'nav_menu'                     => true,
				'post_format'                  => true,
				'wp_theme'                     => true,
				'wp_template_part_area'        => true,
				// Disable Beaver Builder taxonomies.
				'fl-builder-template-category' => true,
				'fl-builder-template-type'     => true,
				// Disable Elementor taxonomies.
				'elementor_library_type'       => true,
				'elementor_library_category'   => true,
				'elementor_font_type'          => true,
				// Disable WooCommerce taxonomies.
				'product_type'                 => true,
				'product_visibility'           => true,
				'product_shipping_class'       => true,
			],
		];

		/**
		 * Allow filtering the list of types not covered.
		 *
		 * @since 2.8.8
		 *
		 * @param array $ignored_types The list of content types not covered, based on object type, in key=>true format for isset() optimization.
		 */
		$ignored_types = apply_filters( 'pods_meta_ignored_types', $ignored_types );

		// Is the type ignored at all?
		if ( ! isset( $ignored_types[ $type ] ) ) {
			return true;
		}

		// Is the whole object type ignored?
		if ( null === $object_name ) {
			return true !== $ignored_types[ $type ];
		}

		// Is the content type ignored?
		return ! isset( $ignored_types[ $type ][ $object_name ] );
	}

	/**
	 * Determine whether the key is covered.
	 *
	 * @since 2.8.2
	 *
	 * @param string      $type        The object type.
	 * @param string      $key         The value key.
	 * @param string|null $object_name The object name.
	 *
	 * @return bool Whether the key is covered.
	 */
	public function is_key_covered( $type, $key, $object_name = null ) {
		if ( 'post' === $type ) {
			$type = 'post_type';
		} elseif ( 'term' === $type ) {
			$type = 'taxonomy';
		}

		if ( ! $this->is_type_covered( $type, $object_name ) ) {
			return false;
		}

		// List of keys we do not cover optimized for fastest isset() operation.
		$keys_not_covered = $this->get_keys_not_covered( $type );

		if ( $object_name ) {
			// Check if object type/name is not covered.
			$cached_is_key_covered = pods_cache_get( $type . '/' . $object_name, __CLASS__ . '/is_key_covered' );

			if ( '404' !== $cached_is_key_covered ) {
				// Check if object type/name/key is not covered.
				$cached_is_key_covered = pods_static_cache_get( $type . '/' . $object_name . '/' . $key, __CLASS__ . '/is_key_covered' );
			}

			if ( '404' === $cached_is_key_covered ) {
				$keys_not_covered[ $key ] = true;
			}
		}

		// Check if this key is covered.
		$key_is_covered = ! isset( $keys_not_covered[ $key ] );

		if ( $key_is_covered ) {
			// Check regex matches.
			$regex_keys = array_keys( $keys_not_covered );

			$regex_keys = array_filter( $regex_keys, static function( $regex_key ) {
                return '.*' === substr( $regex_key, -2 );
            } );

			if ( ! empty( $regex_keys ) ) {
				$regex_keys = implode( '|', $regex_keys );

				$key_is_covered = false === ( (bool) preg_match( '/^(' . $regex_keys . ')$/', $key ) );
			}
		}

		/**
		 * Allow filtering the list of keys not covered.
		 *
		 * @since 2.8.0
		 *
		 * @param bool        $key_is_covered The list of keys not covered in key=>true format for isset() optimization.
		 * @param string      $type           The object type.
		 * @param string      $key            The value key.
		 * @param string|null $object_name    The object name.
		 */
		return (bool) apply_filters( 'pods_meta_key_is_covered', $key_is_covered, $type, $key, $object_name );
	}

	/**
	 * All *_*_meta filter handler aliases
	 *
	 * @return mixed
	 */
	public function get_post_meta() {
		$args = func_get_args();

		array_unshift( $args, 'post_type' );

		$_null = apply_filters( 'pods_meta_get_post_meta', null, $args );

		static $no_conflict = false;

		if ( null !== $_null || $no_conflict ) {
			return $_null;
		}

		$no_conflict = true;

		$return = call_user_func_array( array( $this, 'get_meta' ), $args );

		$no_conflict = false;

		return $return;
	}

	/**
	 * @return mixed
	 */
	public function get_user_meta() {
		$args = func_get_args();

		array_unshift( $args, 'user' );

		$_null = apply_filters( 'pods_meta_get_user_meta', null, $args );

		static $no_conflict = false;

		if ( null !== $_null || $no_conflict ) {
			return $_null;
		}

		$no_conflict = true;

		$return = call_user_func_array( array( $this, 'get_meta' ), $args );

		$no_conflict = false;

		return $return;
	}

	/**
	 * @return mixed
	 */
	public function get_comment_meta() {
		$args = func_get_args();

		array_unshift( $args, 'comment' );

		$_null = apply_filters( 'pods_meta_get_comment_meta', null, $args );

		static $no_conflict = false;

		if ( null !== $_null || $no_conflict ) {
			return $_null;
		}

		$no_conflict = true;

		$return = call_user_func_array( array( $this, 'get_meta' ), $args );

		$no_conflict = false;

		return $return;
	}

	/**
	 * @return mixed
	 */
	public function get_term_meta() {
		$args = func_get_args();

		array_unshift( $args, 'term' );

		$_null = apply_filters( 'pods_meta_get_term_meta', null, $args );

		static $no_conflict = false;

		if ( null !== $_null || $no_conflict ) {
			return $_null;
		}

		$no_conflict = true;

		$return = call_user_func_array( array( $this, 'get_meta' ), $args );

		$no_conflict = false;

		return $return;
	}

	/**
	 * All *_*_meta filter handler aliases
	 *
	 * @return mixed
	 */
	public function get_option() {
		$args = func_get_args();

		if ( 0 === strpos( $args[2], '_transient_' ) || 0 === strpos( $args[2], '_site_transient_' ) ) {
			return $args[0];
		}

		array_unshift( $args, 'settings' );

		$_null = apply_filters( 'pods_meta_get_option', null, $args );

		static $no_conflict = false;

		if ( null !== $_null || $no_conflict ) {
			return $_null;
		}

		$no_conflict = true;

		$return = call_user_func_array( array( $this, 'get_meta' ), $args );

		$no_conflict = false;

		return $return;
	}

	/**
	 * @return mixed
	 */
	public function add_post_meta() {
		$args = func_get_args();

		array_unshift( $args, 'post_type' );

		$_null = apply_filters( 'pods_meta_add_post_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'add_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function add_user_meta() {
		$args = func_get_args();

		array_unshift( $args, 'user' );

		$_null = apply_filters( 'pods_meta_add_user_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'add_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function add_comment_meta() {
		$args = func_get_args();

		array_unshift( $args, 'comment' );

		$_null = apply_filters( 'pods_meta_add_comment_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'add_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function add_term_meta() {
		$args = func_get_args();

		array_unshift( $args, 'term' );

		$_null = apply_filters( 'pods_meta_add_term_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'add_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function add_option() {
		$args = func_get_args();

		array_unshift( $args, 'settings' );

		$_null = apply_filters( 'pods_meta_add_option', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'add_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function update_post_meta() {
		$args = func_get_args();

		array_unshift( $args, 'post_type' );

		$_null = apply_filters( 'pods_meta_update_post_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'update_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function update_user_meta() {
		$args = func_get_args();

		array_unshift( $args, 'user' );

		$_null = apply_filters( 'pods_meta_update_user_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'update_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function update_comment_meta() {
		$args = func_get_args();

		array_unshift( $args, 'comment' );

		$_null = apply_filters( 'pods_meta_update_comment_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'update_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function update_term_meta() {
		$args = func_get_args();

		array_unshift( $args, 'term' );

		$_null = apply_filters( 'pods_meta_update_term_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'update_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function update_option() {
		$args = func_get_args();

		array_unshift( $args, 'settings' );

		$_null = apply_filters( 'pods_meta_update_option', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'update_meta' ), $args );
	}

	/**
	 * Handle updating post meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @return mixed
	 */
	public function update_post_meta_by_id() {
		$args = func_get_args();

		array_unshift( $args, 'post_type' );

		// WP core filter is weird and has meta value before meta key.
		$meta_value = $args[3];
		$meta_key   = $args[4];

		// Switch order of meta key / meta value.
		$args[3] = $meta_key;
		$args[4] = $meta_value;

		/**
		 * Allow circumventing the update meta handling by meta ID for Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param null|bool $_null Whether to override the meta handling by Pods.
		 * @param array     $args  The function arguments with the type added to the front.
		 */
		$_null = apply_filters( 'pods_meta_update_post_meta_by_id', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( [ $this, 'update_meta_by_id' ], $args );
	}

	/**
	 * Handle updating user meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @return mixed
	 */
	public function update_user_meta_by_id() {
		$args = func_get_args();

		array_unshift( $args, 'user' );

		// WP core filter is weird and has meta value before meta key.
		$meta_value = $args[3];
		$meta_key   = $args[4];

		// Switch order of meta key / meta value.
		$args[3] = $meta_key;
		$args[4] = $meta_value;

		/**
		 * Allow circumventing the update meta handling by meta ID for Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param null|bool $_null Whether to override the meta handling by Pods.
		 * @param array     $args  The function arguments with the type added to the front.
		 */
		$_null = apply_filters( 'pods_meta_update_user_meta_by_id', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( [ $this, 'update_meta_by_id' ], $args );
	}

	/**
	 * Handle updating comment meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @return mixed
	 */
	public function update_comment_meta_by_id() {
		$args = func_get_args();

		array_unshift( $args, 'comment' );

		// WP core filter is weird and has meta value before meta key.
		$meta_value = $args[3];
		$meta_key   = $args[4];

		// Switch order of meta key / meta value.
		$args[3] = $meta_key;
		$args[4] = $meta_value;

		/**
		 * Allow circumventing the update meta handling by meta ID for Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param null|bool $_null Whether to override the meta handling by Pods.
		 * @param array     $args  The function arguments with the type added to the front.
		 */
		$_null = apply_filters( 'pods_meta_update_comment_meta_by_id', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( [ $this, 'update_meta_by_id' ], $args );
	}

	/**
	 * Handle updating term meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @return mixed
	 */
	public function update_term_meta_by_id() {
		$args = func_get_args();

		array_unshift( $args, 'term' );

		// WP core filter is weird and has meta value before meta key.
		$meta_value = $args[3];
		$meta_key   = $args[4];

		// Switch order of meta key / meta value.
		$args[3] = $meta_key;
		$args[4] = $meta_value;

		/**
		 * Allow circumventing the update meta handling by meta ID for Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param null|bool $_null Whether to override the meta handling by Pods.
		 * @param array     $args  The function arguments with the type added to the front.
		 */
		$_null = apply_filters( 'pods_meta_update_term_meta_by_id', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( [ $this, 'update_meta_by_id' ], $args );
	}

	/**
	 * @return mixed
	 */
	public function delete_post_meta() {
		$args = func_get_args();

		array_unshift( $args, 'post_type' );

		$_null = apply_filters( 'pods_meta_delete_post_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'delete_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function delete_user_meta() {
		$args = func_get_args();

		array_unshift( $args, 'user' );

		$_null = apply_filters( 'pods_meta_delete_user_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'delete_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function delete_comment_meta() {
		$args = func_get_args();

		array_unshift( $args, 'comment' );

		$_null = apply_filters( 'pods_meta_delete_comment_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'delete_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function delete_term_meta() {
		$args = func_get_args();

		array_unshift( $args, 'term' );

		$_null = apply_filters( 'pods_meta_delete_term_meta', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'delete_meta' ), $args );
	}

	/**
	 * @return mixed
	 */
	public function delete_option() {
		$args = func_get_args();

		array_unshift( $args, 'settings' );

		$_null = apply_filters( 'pods_meta_delete_option', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( array( $this, 'delete_meta' ), $args );
	}

	/**
	 * Handle deleting post meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @return mixed
	 */
	public function delete_post_meta_by_id() {
		$args = func_get_args();

		array_unshift( $args, 'post_type' );

		/**
		 * Allow circumventing the delete meta handling by meta ID for Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param null|bool $_null Whether to override the meta handling by Pods.
		 * @param array     $args  The function arguments with the type added to the front.
		 */
		$_null = apply_filters( 'pods_meta_delete_post_meta_by_id', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( [ $this, 'delete_meta_by_id' ], $args );
	}

	/**
	 * Handle deleting user meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @return mixed
	 */
	public function delete_user_meta_by_id() {
		$args = func_get_args();

		array_unshift( $args, 'user' );

		/**
		 * Allow circumventing the delete meta handling by meta ID for Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param null|bool $_null Whether to override the meta handling by Pods.
		 * @param array     $args  The function arguments with the type added to the front.
		 */
		$_null = apply_filters( 'pods_meta_delete_user_meta_by_id', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( [ $this, 'delete_meta_by_id' ], $args );
	}

	/**
	 * Handle deleting comment meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @return mixed
	 */
	public function delete_comment_meta_by_id() {
		$args = func_get_args();

		array_unshift( $args, 'comment' );

		/**
		 * Allow circumventing the delete meta handling by meta ID for Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param null|bool $_null Whether to override the meta handling by Pods.
		 * @param array     $args  The function arguments with the type added to the front.
		 */
		$_null = apply_filters( 'pods_meta_delete_comment_meta_by_id', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( [ $this, 'delete_meta_by_id' ], $args );
	}

	/**
	 * Handle deleting term meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @return mixed
	 */
	public function delete_term_meta_by_id() {
		$args = func_get_args();

		array_unshift( $args, 'term' );

		/**
		 * Allow circumventing the delete meta handling by meta ID for Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param null|bool $_null Whether to override the meta handling by Pods.
		 * @param array     $args  The function arguments with the type added to the front.
		 */
		$_null = apply_filters( 'pods_meta_delete_term_meta_by_id', null, $args );

		if ( null !== $_null ) {
			return $_null;
		}

		return call_user_func_array( [ $this, 'delete_meta_by_id' ], $args );
	}

	/*
     * The real meta functions
     */
	/**
	 * @param        $object_type
	 * @param        $object_id
	 * @param string $aux
	 *
	 * @return bool|mixed
	 */
	public function get_object( $object_type, $object_id, $aux = '' ) {
		global $wpdb;

		if ( 'term' === $object_type ) {
			$object_type = 'taxonomy';
		}

		if ( 'post_type' === $object_type ) {
			$objects = self::$post_types;
		} elseif ( 'taxonomy' === $object_type ) {
			$objects = self::$taxonomies;
		} elseif ( 'media' === $object_type ) {
			$objects = self::$media;
		} elseif ( 'user' === $object_type ) {
			$objects = self::$user;
		} elseif ( 'comment' === $object_type ) {
			$objects = self::$comment;
		} elseif ( 'pod' === $object_type ) {
			$objects = self::$advanced_content_types;
		} elseif ( 'settings' === $object_type ) {
			$objects = self::$settings;
		} else {
			return false;
		}

		if ( empty( $objects ) || ! is_array( $objects ) ) {
			return false;
		}

		$object_name = null;

		if ( 'media' === $object_type ) {
			return reset( $objects );
		} elseif ( 'user' === $object_type ) {
			return reset( $objects );
		} elseif ( 'comment' === $object_type ) {
			return reset( $objects );
		} elseif ( ! empty( $aux ) ) {
			$object_name = $aux;
		} elseif ( 'post_type' === $object_type ) {
			$object = get_post( $object_id );

			if ( ! is_object( $object ) || empty( $object->post_type ) ) {
				return false;
			}

			$object_name = $object->post_type;
		} elseif ( 'taxonomy' === $object_type ) {
			$object = get_term( $object_id );

			if ( ! is_object( $object ) || empty( $object->taxonomy ) ) {
				return false;
			}

			$object_name = $object->taxonomy;
		} elseif ( 'settings' === $object_type ) {
			$object_name = $object_id;
		} else {
			return false;
		}

		$reserved_post_types = array(
			'revision'
		);

		$reserved_post_types = apply_filters( 'pods_meta_reserved_post_types', $reserved_post_types, $object_type, $object_id, $object_name, $objects );

		if (
			empty( $object_name )
			|| (
				'post_type' === $object_type
				&& 0 === strpos( $object_name, '_pods_' )
			)
			|| in_array( $object_name, $reserved_post_types, true )
		) {
			return false;
		} elseif ( 'attachment' === $object_name ) {
			return reset( self::$media );
		}

		$recheck = array();

		// Return first created by Pods, save extended for later
		foreach ( $objects as $pod ) {
			$pod_object = pods_v( 'object', $pod );

			if ( $object_name === $pod_object ) {
				$recheck[] = $pod;
			}

			if ( '' === $pod_object && $object_name === $pod['name'] ) {
				return $pod;
			}
		}

		// If no objects created by Pods, return first extended
		foreach ( $recheck as $pod ) {
			return $pod;
		}

		return false;
	}

	/**
	 * @param        $object_type
	 * @param null   $_null
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param bool   $single
	 *
	 * @return array|bool|int|mixed|null|string|void
	 */
	public function get_meta( $object_type, $_null = null, $object_id = 0, $meta_key = '', $single = false ) {
		$metadata_integration = (int) pods_get_setting( 'metadata_integration', 1 );

		// Only continue if metadata is integrated with.
		if ( 0 === $metadata_integration ) {
			return $_null;
		}

		$first_pods_version = get_option( 'pods_framework_version_first' );
		$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

		$metadata_override_get = (int) pods_get_setting( 'metadata_override_get', version_compare( $first_pods_version, '2.8.21', '<=' ) ? 1 : 0 );

		// Only continue if metadata is overridden.
		if ( 0 === $metadata_override_get ) {
			return $_null;
		}

		// Enforce boolean as it can be a string sometimes
		$single = filter_var( $single, FILTER_VALIDATE_BOOLEAN );

		$meta_type = $object_type;

		$no_conflict = pods_no_conflict_check( $meta_type );

		if ( ! $no_conflict ) {
			pods_no_conflict_on( $meta_type );
		}

		if ( in_array( $meta_type, array( 'post', 'post_type', 'media' ) ) ) {
			$meta_type = 'post';

			$object_name = get_post_type( $object_id );
		} elseif ( 'taxonomy' === $meta_type ) {
			$meta_type = 'term';

			$object_name = get_term_field( 'taxonomy', $object_id );
		} else {
			$object_name = $meta_type;
		}

		if ( empty( $object_name ) || is_wp_error( $object_name ) ) {
			$object_name = null;
		}

		// Skip keys we do not cover.
		if ( $meta_key && ! $this->is_key_covered( $object_type, $meta_key, $object_name ) ) {
			if ( ! $no_conflict ) {
				pods_no_conflict_off( $meta_type );
			}

			return $_null;
		}

		if ( empty( $meta_key ) ) {
			// Check whether we want to cover get_*_meta( $id ) calls.
			if ( ! defined( 'PODS_ALLOW_FULL_META' ) || ! PODS_ALLOW_FULL_META ) {
				if ( ! $no_conflict ) {
					pods_no_conflict_off( $meta_type );
				}

				return $_null;
			}

			$single = false;
		}

		if ( 'user' === $object_type && 'locale' === $meta_key ) {
			if ( ! $no_conflict ) {
				pods_no_conflict_off( $meta_type );
			}

			return $_null; // don't interfere with locale
		}

		$object = $this->get_object( $object_type, $object_id );

		$object_is_pod_object = $object instanceof Pod;

		$first_meta_key = false;

		if ( $meta_key ) {
			$first_meta_key = $meta_key;

			if ( false !== strpos( $first_meta_key, '.' ) ) {
				$first_meta_key = current( explode( '.', $first_meta_key ) );
			}
		}

		if (
			empty( $object_id )
			|| empty( $object )
			|| (
				$meta_key
				&& (
					(
						$object_is_pod_object
						&& ! $object->get_field( $first_meta_key, null, false )
					)
					|| (
						! $object_is_pod_object
						&& ! isset( $object['fields'][ $first_meta_key ] )
					)
				)
			)
		) {
			if ( $object_name && empty( $object ) ) {
				pods_cache_set( $object_type . '/' . $object_name, '404', __CLASS__ . '/is_key_covered' );
			}

			if ( $meta_key ) {
				pods_static_cache_set( $object_type . '/' . $object_name . '/' . $meta_key, '404', __CLASS__ . '/is_key_covered' );
			}

			if ( ! $no_conflict ) {
				pods_no_conflict_off( $meta_type );
			}

			return $_null;
		}

		$meta_cache = array();

		if ( ! $single && isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {
			$meta_cache = wp_cache_get( $object_id, 'pods_' . $meta_type . '_meta' );

			if ( empty( $meta_cache ) ) {
				$meta_cache = wp_cache_get( $object_id, $meta_type . '_meta' );

				if ( empty( $meta_cache ) ) {
					$meta_cache = update_meta_cache( $meta_type, array( $object_id ) );
					$meta_cache = $meta_cache[ $object_id ];
				}
			}
		}

		if ( empty( $meta_cache ) || ! is_array( $meta_cache ) ) {
			$meta_cache = array();
		}

		if ( ! is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object['name'] ) {
			self::$current_field_pod = pods_get_instance( $object['name'], $object_id );
		} elseif ( self::$current_field_pod->id() != $object_id ) {
			self::$current_field_pod->fetch( $object_id );
		}

		$pod = self::$current_field_pod;

		$pod_object = $pod->pod_data;

		if ( ! $pod_object instanceof Pod ) {
			if ( ! $no_conflict ) {
				pods_no_conflict_off( $meta_type );
			}

			return $_null;
		}

		$meta_keys = [
			$meta_key,
		];

		if ( empty( $meta_key ) ) {
			$meta_keys = array_keys( $meta_cache );
		}

		$key_found = false;

		$tableless_field_types = PodsForm::tableless_field_types();

		foreach ( $meta_keys as $meta_k ) {
			if ( ! empty( $pod ) ) {
				$first_meta_key = $meta_k;

				if ( false !== strpos( $first_meta_key, '.' ) ) {
					// Get the first meta key.
					$first_meta_key = current( explode( '.', $first_meta_key ) );
				}

				$field_object = $pod_object->get_field( $first_meta_key, null, true, false );

				if ( $field_object && ( ! $field_object instanceof Object_Field || $this->cover_object_fields_in_meta() ) ) {
					$key_found = true;

					$meta_cache[ $meta_k ] = $pod->field( array(
						'name'     => $meta_k,
						'single'   => $single,
						'get_meta' => true,
					) );

					if ( ! is_array( $meta_cache[ $meta_k ] ) || ! isset( $meta_cache[ $meta_k ][0] ) ) {
						if ( empty( $meta_cache[ $meta_k ] ) ) {
							$meta_cache[ $meta_k ] = array();
						} else {
							$meta_cache[ $meta_k ] = array( $meta_cache[ $meta_k ] );
						}
					}

					if ( isset( $meta_cache[ '_pods_' . $first_meta_key ] ) && in_array( $field_object['type'], $tableless_field_types, true ) ) {
						unset( $meta_cache[ '_pods_' . $first_meta_key ] );
					}
				}
			}
		}

		if ( ! $no_conflict ) {
			pods_no_conflict_off( $meta_type );
		}

		unset( $pod ); // memory clear

		if ( ! $key_found ) {
			return $_null;
		}

		if ( ! $single && isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {
			wp_cache_set( $object_id, $meta_cache, 'pods_' . $meta_type . '_meta' );
		}

		if ( empty( $meta_key ) ) {
			return $meta_cache;
		} elseif ( isset( $meta_cache[ $meta_key ] ) ) {
			$value = $meta_cache[ $meta_key ];
		} else {
			$value = '';
		}

		if ( ! is_numeric( $value ) && empty( $value ) ) {
			if ( $single ) {
				$value = '';
			} else {
				$value = array();
			}
		} // get_metadata requires $meta[ 0 ] to be set for first value to be retrieved
		elseif ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		return $value;
	}

	/**
	 * @param        $object_type
	 * @param null   $_null
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param bool   $unique
	 *
	 * @return bool|int|null
	 */
	public function add_meta( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {
		if ( pods_tableless() ) {
			return $_null;
		}

		$metadata_integration = (int) pods_get_setting( 'metadata_integration', 1 );

		// Only continue if metadata is integrated with.
		if ( 0 === $metadata_integration ) {
			return $_null;
		}

		if ( in_array( $object_type, array( 'post', 'post_type', 'media' ) ) ) {
			$object_name = get_post_type( $object_id );
		} elseif ( 'taxonomy' === $object_type ) {
			$object_name = get_term_field( 'taxonomy', $object_id );
		} else {
			$object_name = $object_type;
		}

		if ( empty( $object_name ) || is_wp_error( $object_name ) ) {
			$object_name = null;
		}

		// Skip keys we do not cover.
		if ( $meta_key && ! $this->is_key_covered( $object_type, $meta_key, $object_name ) ) {
			return $_null;
		}

		$object = $this->get_object( $object_type, $object_id );

		$object_is_pod_object = $object instanceof Pod;

		$first_meta_key = false;

		if ( $meta_key ) {
			$first_meta_key = $meta_key;

			if ( false !== strpos( $first_meta_key, '.' ) ) {
				$first_meta_key = current( explode( '.', $first_meta_key ) );
			}
		}

		if (
			empty( $object_id )
			|| empty( $object )
			|| (
				$meta_key
				&& (
					(
						$object_is_pod_object
						&& ! $object->get_field( $first_meta_key, null, false )
					)
					|| (
						! $object_is_pod_object
						&& ! isset( $object['fields'][ $first_meta_key ] )
					)
				)
			)
		) {
			if ( $object_name && empty( $object ) ) {
				pods_cache_set( $object_type . '/' . $object_name, '404', __CLASS__ . '/is_key_covered' );
			}

			if ( $meta_key ) {
				pods_static_cache_set( $object_type . '/' . $object_name . '/' . $meta_key, '404', __CLASS__ . '/is_key_covered' );
			}

			return $_null;
		}

		if ( in_array( $object['fields'][ $meta_key ]['type'], PodsForm::tableless_field_types() ) ) {
			if ( ! is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object['name'] ) {
				self::$current_field_pod = pods_get_instance( $object['name'], $object_id );
			} elseif ( self::$current_field_pod->id() != $object_id ) {
				self::$current_field_pod->fetch( $object_id );
			}

			$pod = self::$current_field_pod;

			$field = $pod->fields( $meta_key );

			// Don't save object fields using meta integration.
			if ( $field instanceof Object_Field && ! $this->cover_object_fields_in_meta() ) {
				return $_null;
			}

			$pod->add_to( $meta_key, $meta_value );
		} else {
			if ( ! is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object['name'] ) {
				self::$current_field_pod = pods_get_instance( $object['name'] );
			}

			$pod = self::$current_field_pod;

			$field = $pod->fields( $meta_key );

			// Don't save object fields using meta integration.
			if ( $field instanceof Object_Field && ! $this->cover_object_fields_in_meta() ) {
				return $_null;
			}

			$pod->save( $meta_key, $meta_value, $object_id, array(
				'podsmeta_direct' => true,
				'error_mode'      => 'false'
			) );
		}

		return $object_id;
	}

	/**
	 * @param        $object_type
	 * @param null   $_null
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param string $prev_value
	 *
	 * @return bool|int|null
	 */
	public function update_meta( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {
		if ( pods_tableless() ) {
			return $_null;
		}

		$metadata_integration = (int) pods_get_setting( 'metadata_integration', 1 );

		// Only continue if metadata is integrated with.
		if ( 0 === $metadata_integration ) {
			return $_null;
		}

		if ( in_array( $object_type, array( 'post', 'post_type', 'media' ) ) ) {
			$object_name = get_post_type( $object_id );
		} elseif ( 'taxonomy' === $object_type ) {
			$object_name = get_term_field( 'taxonomy', $object_id );
		} else {
			$object_name = $object_type;
		}

		if ( empty( $object_name ) || is_wp_error( $object_name ) ) {
			$object_name = null;
		}

		// Skip keys we do not cover.
		if ( $meta_key && ! $this->is_key_covered( $object_type, $meta_key, $object_name ) ) {
			return $_null;
		}

		$object = $this->get_object( $object_type, $object_id );

		$object_is_pod_object = $object instanceof Pod;

		$first_meta_key = false;

		if ( $meta_key ) {
			$first_meta_key = $meta_key;

			if ( false !== strpos( $first_meta_key, '.' ) ) {
				$first_meta_key = current( explode( '.', $first_meta_key ) );
			}
		}

		if (
			empty( $object_id )
			|| empty( $object )
			|| (
				$meta_key
				&& (
					(
						$object_is_pod_object
						&& ! $object->get_field( $first_meta_key, null, false )
					)
					|| (
						! $object_is_pod_object
						&& ! isset( $object['fields'][ $first_meta_key ] )
					)
				)
			)
		) {
			if ( $object_name && empty( $object ) ) {
				pods_cache_set( $object_type . '/' . $object_name, '404', __CLASS__ . '/is_key_covered' );
			}

			if ( $meta_key ) {
				pods_static_cache_set( $object_type . '/' . $object_name . '/' . $meta_key, '404', __CLASS__ . '/is_key_covered' );
			}

			return $_null;
		}

		if ( ! is_object( self::$current_field_pod ) || self::$current_field_pod->pod !== $object['name'] ) {
			self::$current_field_pod = pods_get_instance( $object['name'] );
		}

		$pod = self::$current_field_pod;

		$pod_object = $pod->pod_data;

		if ( ! $pod_object instanceof Pod ) {
			return $_null;
		}

		$field_object = $pod_object->get_field( $meta_key );

		// Don't save object fields using meta integration.
		if ( $field_object instanceof Object_Field && ! $this->cover_object_fields_in_meta() ) {
			return $_null;
		}

		$tableless_field_types = PodsForm::tableless_field_types();

		if ( null !== $pod->data->row && ( $field_object || false !== strpos( $meta_key, '.' ) ) ) {
			$key = $meta_key;

			if ( false !== strpos( $meta_key, '.' ) ) {
				$key = current( explode( '.', $meta_key ) );
			}

			$pod->data->row[ $meta_key ] = $meta_value;

			if ( isset( $meta_cache[ '_pods_' . $key ] ) && $field_object && in_array( $field_object['type'], $tableless_field_types, true ) ) {
				unset( $meta_cache[ '_pods_' . $key ] );
			}
		}

		$pod->save( $meta_key, $meta_value, $object_id, array(
			'podsmeta_direct' => true,
			'error_mode'      => 'false',
		) );

		return $object_id;
	}

	/**
	 * Handle updating the meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @param string $object_type The object type.
	 * @param null   $_null       The default value for the filter.
	 * @param int    $meta_id     The meta ID.
	 * @param string $meta_value  The meta value.
	 * @param string $meta_key    The meta key.
	 *
	 * @return bool|int|null
	 */
	public function update_meta_by_id( $object_type, $_null = null, $meta_id = 0, $meta_key = '', $meta_value = '' ) {
		$metadata_integration = (int) pods_get_setting( 'metadata_integration', 1 );

		// Only continue if metadata is integrated with.
		if ( 0 === $metadata_integration ) {
			return $_null;
		}

		$meta_type = 'post_type' === $object_type ? 'post' : $object_type;

		// Get the original meta record.
		$meta = get_metadata_by_mid( $meta_type, $meta_id );

		// Stop overriding the saving process if the original meta record was not found.
		if ( ! $meta ) {
			return $_null;
		}

		$column = sanitize_key( $meta_type . '_id' );

		// Get the object ID from the original meta record.
		$object_id = $meta->{$column};

		return $this->update_meta( $object_type, $_null, $object_id, $meta_key, $meta_value );
	}

	/**
	 * @param        $object_type
	 * @param null   $_null
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param bool   $delete_all
	 *
	 * @return null
	 */
	public function delete_meta( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $delete_all = false ) {
		if ( pods_tableless() ) {
			return $_null;
		}

		$metadata_integration = (int) pods_get_setting( 'metadata_integration', 1 );

		// Only continue if metadata is integrated with.
		if ( 0 === $metadata_integration ) {
			return $_null;
		}

		if ( in_array( $object_type, array( 'post', 'post_type', 'media' ) ) ) {
			$object_name = get_post_type( $object_id );
		} elseif ( 'taxonomy' === $object_type ) {
			$object_name = get_term_field( 'taxonomy', $object_id );
		} else {
			$object_name = $object_type;
		}

		if ( empty( $object_name ) || is_wp_error( $object_name ) ) {
			$object_name = null;
		}

		// Skip keys we do not cover.
		if ( $meta_key && ! $this->is_key_covered( $object_type, $meta_key, $object_name ) ) {
			return $_null;
		}

		$object = $this->get_object( $object_type, $object_id );

		$object_is_pod_object = $object instanceof Pod;

		$first_meta_key = false;

		if ( $meta_key ) {
			$first_meta_key = $meta_key;

			if ( false !== strpos( $first_meta_key, '.' ) ) {
				$first_meta_key = current( explode( '.', $first_meta_key ) );
			}
		}

		if (
			empty( $object_id )
			|| empty( $object )
			|| (
				$meta_key
				&& (
					(
						$object_is_pod_object
						&& ! $object->get_field( $first_meta_key, null, false )
					)
					|| (
						! $object_is_pod_object
						&& ! isset( $object['fields'][ $first_meta_key ] )
					)
				)
			)
		) {
			if ( $object_name && empty( $object ) ) {
				pods_cache_set( $object_type . '/' . $object_name, '404', __CLASS__ . '/is_key_covered' );
			}

			if ( $meta_key ) {
				pods_static_cache_set( $object_type . '/' . $object_name . '/' . $meta_key, '404', __CLASS__ . '/is_key_covered' );
			}

			return $_null;
		}

		// @todo handle $delete_all (delete the field values from all pod items)
		if ( ! empty( $meta_value ) && in_array( $object['fields'][ $meta_key ]['type'], PodsForm::tableless_field_types() ) ) {
			if ( ! is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object['name'] ) {
				self::$current_field_pod = pods_get_instance( $object['name'], $object_id );
			} elseif ( self::$current_field_pod->id() != $object_id ) {
				self::$current_field_pod->fetch( $object_id );
			}

			$pod = self::$current_field_pod;

			$field = $pod->fields( $meta_key );

			// Don't save object fields using meta integration.
			if ( $field instanceof Object_Field && ! $this->cover_object_fields_in_meta() ) {
				return $_null;
			}

			$pod->remove_from( $meta_key, $meta_value );
		} else {
			if ( ! is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object['name'] ) {
				self::$current_field_pod = pods_get_instance( $object['name'] );
			}

			$pod = self::$current_field_pod;

			$field = $pod->fields( $meta_key );

			// Don't save object fields using meta integration.
			if ( $field instanceof Object_Field && ! $this->cover_object_fields_in_meta() ) {
				return $_null;
			}

			$pod->save( array( $meta_key => null ), null, $object_id, array(
				'podsmeta_direct' => true,
				'error_mode'      => 'false',
			) );
		}

		return $_null;
	}

	/**
	 * Handle delete the meta by meta ID.
	 *
	 * @since 2.8.0
	 *
	 * @param string $object_type The object type.
	 * @param null   $_null       The default value for the filter.
	 * @param int    $meta_id     The meta ID.
	 *
	 * @return bool|int|null
	 */
	public function delete_meta_by_id( $object_type, $_null = null, $meta_id = 0 ) {
		$metadata_integration = (int) pods_get_setting( 'metadata_integration', 1 );

		// Only continue if metadata is integrated with.
		if ( 0 === $metadata_integration ) {
			return $_null;
		}

		$meta_type = 'post_type' === $object_type ? 'post' : $object_type;

		// Get the original meta record.
		$meta = get_metadata_by_mid( $meta_type, $meta_id );

		// Stop overriding the saving process if the original meta record was not found.
		if ( ! $meta ) {
			return $_null;
		}

		$column = sanitize_key( $meta_type . '_id' );

		// Get the object ID from the original meta record.
		$object_id = $meta->{$column};

		return $this->delete_meta( $object_type, $_null, $object_id, $meta->meta_key, $meta->meta_value );
	}

	/**
	 * @param $id
	 *
	 * @return bool|void
	 */
	public function delete_post( $id ) {
		$post = get_post( $id );

		if ( empty( $post ) ) {
			return;
		}

		$id        = $post->ID;
		$post_type = $post->post_type;

		return $this->delete_object( 'post_type', $id, $post_type );
	}

	/**
	 * @param $id
	 */
	public function delete_taxonomy( $id ) {
		/**
		 * @var $wpdb WPDB
		 */
		global $wpdb;

		$terms = $wpdb->get_results( "SELECT `term_id`, `taxonomy` FROM `{$wpdb->term_taxonomy}` WHERE `term_taxonomy_id` = {$id}" );

		if ( empty( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			$id       = $term->term_id;
			$taxonomy = $term->taxonomy;

			$this->delete_object( 'taxonomy', $id, $taxonomy );
		}
	}

	/**
	 * Hook the split_shared_term action and point it to this method
	 *
	 * Fires after a previously shared taxonomy term is split into two separate terms.
	 *
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function split_shared_term( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {
		$term_splitting = new Pods_Term_Splitting( $term_id, $new_term_id, $taxonomy );
		$term_splitting->split_shared_term();
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function delete_user( $id ) {
		return $this->delete_object( 'user', $id );
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function delete_comment( $id ) {
		return $this->delete_object( 'comment', $id );
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function delete_media( $id ) {

		return $this->delete_object( 'media', $id );
	}

	/**
	 * @param      $type
	 * @param      $id
	 * @param null $name
	 *
	 * @return bool
	 */
	public function delete_object( $type, $id, $name = null ) {
		if ( empty( $name ) ) {
			$name = $type;
		}

		$object = $this->object_get( $type, $name );

		if ( ! empty( $object ) ) {
			$params = array(
				'pod'    => pods_v( 'name', $object ),
				'pod_id' => pods_v( 'id', $object ),
				'id'     => $id,
				'strict' => false,
			);

			return pods_api()->delete_pod_item( $params, false );
		} else {
			return pods_api()->delete_object_from_relationships( $id, $type, $name );
		}
	}

	/**
	 * Determine whether to cover object fields in metadata integration.
	 *
	 * @since 2.8.8
	 *
	 * @return bool Whether to cover object fields in metadata integration.
	 */
	public function cover_object_fields_in_meta() {
		/**
		 * Allow filtering whether to cover object fields in metadata integration.
		 *
		 * @since 2.8.8
		 *
		 * @param bool $cover_object_fields_in_meta Whether to cover object fields in metadata integration.
		 */
		return (bool) apply_filters( 'pods_meta_cover_object_fields_in_meta', false );
	}
}
