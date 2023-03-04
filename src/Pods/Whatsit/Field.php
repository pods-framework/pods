<?php

namespace Pods\Whatsit;

use PodsForm;
use Pods\Whatsit;

/**
 * Field class.
 *
 * @since 2.8.0
 */
class Field extends Whatsit {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'field';

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		if ( null !== $this->_table_info ) {
			return $this->_table_info;
		}

		$related_type = $this->get_related_object_type();
		$related_name = $this->get_related_object_name();

		if ( null === $related_type || null === $related_name ) {
			return [];
		}

		$api = pods_api();

		$table_info = $api->get_table_info( $related_type, $related_name, null, null, $this );

		if ( ! $table_info ) {
			$table_info = [];
		}

		$this->_table_info = $table_info;

		return $table_info;
	}

	/**
	 * Get the type-specific object argument value.
	 *
	 * @since 2.8.9
	 *
	 * @param string     $arg     Argument name.
	 * @param mixed|null $default Default to use if not set.
	 * @param bool       $strict  Whether to check only normal arguments and not special arguments.
	 *
	 * @return null|mixed Argument value, or null if not set.
	 */
	public function get_type_arg( $arg, $default = null, $strict = false ) {
		return $this->get_arg( $this->get_type() . '_' . $arg, $default, $strict );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_arg( $arg, $default = null, $strict = false ) {
		$arg = (string) $arg;

		$special_args = [
			// Pod args.
			'pod_id'                    => 'get_parent_id',
			'pod'                       => 'get_parent_name',
			'pod_name'                  => 'get_parent_name',
			'pod_identifier'            => 'get_parent_identifier',
			'pod_label'                 => 'get_parent_label',
			'pod_description'           => 'get_parent_description',
			'pod_object'                => 'get_parent_object',
			'pod_object_type'           => 'get_parent_object_type',
			'pod_object_storage_type'   => 'get_parent_object_storage_type',
			'pod_type'                  => 'get_parent_type',
			// Group args.
			'group_id'                  => 'get_group_id',
			'group_name'                => 'get_group_name',
			'group_identifier'          => 'get_group_identifier',
			'group_label'               => 'get_group_label',
			'group_description'         => 'get_group_description',
			'group_object'              => 'get_group_object',
			'group_object_type'         => 'get_group_object_type',
			'group_object_storage_type' => 'get_group_object_storage_type',
			'group_type'                => 'get_group_type',
		];

		if ( isset( $special_args[ $arg ] ) ) {
			return $this->{$special_args[ $arg ]}();
		}

		$type = isset( $this->args['type'] ) ? $this->args['type'] : 'invalid';

		$invalid_options = [
			0,
			'0',
			'',
			'-- Select One --',
			__( '-- Select One --', 'pods' ),
			null,
		];

		// Handle related object types.
		if ( ! $strict && $type . '_object' === $arg ) {
			if ( ! isset( $this->args[ $arg ] ) || in_array( $this->args[ $arg ], $invalid_options, true ) ) {
				return $default;
			}

			return $this->get_related_object_type();
		}

		// Handle related object name.
		if ( ! $strict && $type . '_val' === $arg ) {
			if ( ! isset( $this->args[ $arg ] ) || in_array( $this->args[ $arg ], $invalid_options, true ) ) {
				return $default;
			}

			return $this->get_related_object_name();
		}

		// Backwards compatibility with previous Pods 2.8 pre-releases.
		if ( 'sister_id' === $arg && isset( $this->args[ $arg ] ) ) {
			if ( in_array( $this->args[ $arg ], $invalid_options, true ) ) {
				return $default;
			}

			return (int) $this->args[ $arg ];
		}

		return parent::get_arg( $arg, $default );
	}

	/**
	 * Determine whether this is a required field.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether this is a required field.
	 */
	public function is_required() {
		return filter_var( $this->get_arg( 'required', false ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Determine whether this is a unique field.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether this is a unique field.
	 */
	public function is_unique() {
		$parent_object = $this->get_parent_object();

		if ( ! $parent_object instanceof Pod ) {
			return false;
		}

		// Only table-based Pods can have unique fields.
		if ( ! $parent_object->is_table_based() ) {
			return false;
		}

		return filter_var( $this->get_arg( 'unique', false ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Determine whether this is a repeatable field.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether this is a repeatable field.
	 */
	public function is_repeatable() {
		$parent_object = $this->get_parent_object();

		// Only non table-based Pods can have repeatable fields.
		if ( $parent_object instanceof Whatsit && $parent_object->is_table_based() ) {
			return false;
		}

		$repeatable_field_types = PodsForm::repeatable_field_types();

		$type = $this->get_type();

		// It must be a repeatable field type.
		if ( ! in_array( $type, $repeatable_field_types, true ) ) {
			return false;
		}

		// Disable repeatable for WYSIWYG TinyMCE fields.
		if ( 'wysiwyg' === $type && 'tinymce' === $this->get_arg( 'wysiwyg_editor', 'tinymce' ) ) {
			return false;
		}

		return (
			filter_var( $this->get_arg( 'repeatable', false ), FILTER_VALIDATE_BOOLEAN )
			&& 1 !== (int) $this->get_arg( 'repeatable_limit', 0 )
		);
	}

	/**
	 * Get related object type from field.
	 *
	 * @since 2.8.0
	 *
	 * @return string|null The related object type, or null if not found.
	 */
	public function get_related_object_type() {
		// Only continue if this is a relationship field.
		if ( ! $this->is_relationship() ) {
			return null;
		}

		$type = $this->get_type();

		// File field types are always related to the media object type.
		if ( 'file' === $type ) {
			return 'media';
		}

		$related_type = $this->get_arg( $type . '_object', $this->get_arg( 'pick_object', null, true ), true );

		if ( '__current__' === $related_type ) {
			$related_type = $this->get_parent_type();
		}

		if ( empty( $related_type ) && 'avatar' === $type ) {
			$related_type = 'media';
		}

		if ( empty( $related_type ) ) {
			return null;
		}

		return $related_type;
	}

	/**
	 * Get related object name from field.
	 *
	 * @since 2.8.0
	 *
	 * @return string|null The related object name, or null if not found.
	 */
	public function get_related_object_name() {
		// Only continue if this is a relationship field.
		if ( ! $this->is_relationship() ) {
			return null;
		}

		$is_simple_relationship = $this->is_simple_relationship();

		// Only continue if this is not a simple relationship field.
		if ( null === $is_simple_relationship || true === $is_simple_relationship ) {
			return null;
		}

		$related_type = $this->get_related_object_type();

		// Only continue if we have a related object type.
		if ( null === $related_type ) {
			return null;
		}

		$type = $this->get_type();

		$related_name = $this->get_arg( $type . '_val', $this->get_arg( 'pick_val', $related_type, true ), true );

		if ( '__current__' === $related_name ) {
			$related_name = $this->get_parent_name();
		}

		if ( 'table' === $related_type ) {
			$related_name = $this->get_arg( 'related_table', $related_name );
		} elseif ( in_array( $related_type, [ 'user', 'media', 'comment' ], true ) ) {
			$related_name = $related_type;
		}

		return $related_name;
	}

	/**
	 * Get related object data from field.
	 *
	 * @since 2.8.0
	 *
	 * @return array|null The related object data, or null if not found.
	 */
	public function get_related_object_data() {
		// Only continue if this is a relationship field.
		if ( ! $this->is_relationship() ) {
			return null;
		}

		return PodsForm::field_method( $this->args['type'], 'data', $this->args['name'], null, $this->args, null, null, true );
	}

	/**
	 * Get the related Pod object if it exists.
	 *
	 * @since 2.8.0
	 *
	 * @return Whatsit|array|null The related object, or null if not found.
	 */
	public function get_related_object() {
		// Only continue if this is a relationship field.
		if ( ! $this->is_relationship() ) {
			return null;
		}

		$table_info = $this->get_table_info();

		// Check if the pod was found.
		if ( ! $table_info || empty( $table_info['pod'] ) ) {
			return null;
		}

		return $table_info['pod'];
	}

	/**
	 * Determine whether this is a relationship field (pick/file/etc).
	 *
	 * @since 2.8.9
	 *
	 * @return bool Whether this is a relationship field (pick/file/etc).
	 */
	public function is_relationship() {
		$type = $this->get_type();

		$tableless_field_types = PodsForm::tableless_field_types();

		return in_array( $type, $tableless_field_types, true );
	}

	/**
	 * Determine whether this is a relationship field (pick/file/etc).
	 *
	 * @since 2.9.7
	 *
	 * @return bool Whether this is a relationship field (pick/file/etc).
	 */
	public function is_file() {
		$type = $this->get_type();

		$file_field_types = PodsForm::file_field_types();

		return in_array( $type, $file_field_types, true );
	}

	/**
	 * Determine whether this is an autocomplete relationship field.
	 *
	 * @since 2.9.4
	 *
	 * @return bool Whether this is an autocomplete relationship field.
	 */
	public function is_autocomplete_relationship() {
		if ( ! $this->is_relationship() ) {
			return false;
		}

		$autocomplete_formats = [
			'autocomplete',
			'list',
		];

		$single_multi = $this->get_single_multi();

		$default = 'single' === $single_multi ? 'dropdown' : 'list';

		$format = $this->get_type_arg( 'format_' . $single_multi, $default, true );

		return in_array( $format, $autocomplete_formats, true );
	}

	/**
	 * Determine whether the relationship field is a simple relationship.
	 *
	 * @since 2.8.9
	 *
	 * @return bool|null Whether the relationship field is a simple relationship, or null if not a relationship field.
	 */
	public function is_simple_relationship() {
		// Only continue if this is a relationship field.
		if ( ! $this->is_relationship() ) {
			return null;
		}

		$related_type = $this->get_related_object_type();

		// Only continue if this is related to an object.
		if ( null === $related_type ) {
			return true;
		}

		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		return in_array( $related_type, $simple_tableless_objects, true );
	}

	/**
	 * Determine whether the separator is excluded for this field.
	 *
	 * @since 2.9.8
	 *
	 * @return bool Whether the separator is excluded for this field.
	 */
	public function is_separator_excluded() {
		$type = $this->get_type();

		$separator_excluded_field_types = PodsForm::separator_excluded_field_types();

		return in_array( $type, $separator_excluded_field_types, true );
	}

	/**
	 * Get the bi-directional field if it is set.
	 *
	 * @since 2.8.0
	 *
	 * @return Whatsit|null The bi-directional field if it is set.
	 */
	public function get_bidirectional_field() {
		// Only continue if this is a relationship field.
		if ( ! $this->is_relationship() ) {
			return null;
		}

		$sister_id = $this->get_arg( 'sister_id' );

		if ( ! $sister_id ) {
			return null;
		}

		$related_field = Store::get_instance()->get_object( $sister_id );

		// Only return if it is a valid field.
		if ( ! $related_field instanceof Field ) {
			return null;
		}

		return $related_field;
	}

	/**
	 * Get field value limit from field.
	 *
	 * @since 2.8.0
	 *
	 * @return int The field value limit.
	 */
	public function get_limit() {
		// If this is a repeatable field then use the repeatable limit (if any).
		if ( $this->is_repeatable() ) {
			return $this->get_arg( 'repeatable_limit', 0 );
		}

		if ( 'multi' === $this->get_single_multi() ) {
			return (int) $this->get_type_arg( 'limit', 0 );
		}

		return 1;
	}

	/**
	 * Get whether the field allows for single or multi tableless field values.
	 *
	 * @since 2.8.22
	 *
	 * @return string Whether the field allows for single or multi tableless field values.
	 */
	public function get_single_multi() {
		if ( ! $this->is_relationship() ) {
			return 'single';
		}

		$format_type = $this->get_type_arg( 'format_type', 'single' );

		if ( ! $format_type ) {
			return 'single';
		}

		return $format_type;
	}

	/**
	 * Determine whether this is a multiple value field.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether this is a multiple value field.
	 */
	public function is_multi_value() {
		// This is a multiple value field if the limit is not 1 (0 for no limit or 2+).
		return 1 !== $this->get_limit();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_fields( array $args = [] ) {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_groups( array $args = [] ) {
		return [];
	}

}
