<?php
/**
 * @package Pods\Global\Functions\Forms
 */

use Pods\Whatsit;
use Pods\Whatsit\Field;
use Pods\Whatsit\Pod;
use Pods\Whatsit\Store;
use Pods\Permissions;

/**
 * Render form fields outside of the <form> context.
 *
 * @since 2.8.0
 *
 * @param string     $name      The object name.
 * @param int|string $object_id The object ID.
 * @param array      $options   The customization options.
 */
function pods_form_render_fields( $name, $object_id, array $options = [] ) {
	$defaults = [
		'section_field'     => null,
		'section'           => null,
		'separator'         => 'before',
		'heading'           => 'h2',
		'separated_heading' => null,
		'render'            => 'table',
	];

	$options = array_merge( $defaults, $options );

	$pod = pods( $name, $object_id, true );

	if ( ! $pod ) {
		return;
	}

	// Return groups.
	$options['return_type'] = 'group';

	// Get groups/fields and render them.
	$groups = pods_form_get_visible_objects( $pod, $options );

	foreach ( $groups as $group ) {
		$fields = $group->get_fields();

		/**
		 * Allow hooking into before the form field output for a group is rendered.
		 *
		 * @since 2.8.0
		 *
		 * @param Whatsit\Group $group The Group object.
		 */
		do_action( 'pods_form_render_fields_group_pre', $group );

		$is_table_separated_render = 'table-separated' === $options['render'];
		$is_table_render           = 'table' === $options['render'] || $is_table_separated_render;
		$is_table_rows_render      = 'table-rows' === $options['render'];

		if ( $is_table_separated_render ) {
			echo "</table>\n";
		}

		if ( ! $is_table_rows_render && 'before' === $options['separator'] ) {
			echo "<hr />\n";
		}

		if ( $is_table_rows_render ) {
			echo '<tr><td colspan="2">';
		}

		if ( $options['heading'] ) {
			$heading_classes = [
				'pods-form-heading',
				'pods-form-heading--pod-' . $name,
				'pods-form-heading--group',
				'pods-form-heading--group-' . $group['name'],
			];

			$heading_classes = array_map( 'sanitize_html_class', $heading_classes );
			$heading_classes = implode( ' ', $heading_classes );

			printf(
				'<%1$s class="%2$s">%3$s</%1$s>' . "\n",
				esc_html( $options['heading'] ),
				esc_attr( $heading_classes ),
				wp_kses_post( $group['label'] )
			);
		}

		if ( $is_table_rows_render ) {
			echo '</td></tr>';
		}

		$container_classes = [
			'pods-form',
			'pods-form-container',
			'pods-form-container--pod--' . $name,
			'pods-form-container--group',
			'pods-form-container--group--' . $group['name'],
		];

		$container_classes = array_map( 'sanitize_html_class', $container_classes );
		$container_classes = implode( ' ', $container_classes );

		$id = $object_id;

		if ( $is_table_render || $is_table_rows_render ) {
			if ( $is_table_render ) {
				echo '<table class="form-table ' . esc_attr( $container_classes ) . '">' . "\n";
			}

			$field_prefix      = 'pods_meta_';
			$field_row_classes = 'pods-meta';

			pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );

			if ( $is_table_render ) {
				echo "</table>\n";
			}
		} elseif ( 'div-rows' === $options['render'] ) {
			echo '<div class="' . esc_attr( $container_classes ) . '">' . "\n";

			$field_prefix      = 'pods_meta_';
			$field_row_classes = 'pods-meta';

			pods_view( PODS_DIR . 'ui/forms/div-rows.php', compact( array_keys( get_defined_vars() ) ) );

			echo "</div>\n";
		}

		if ( $is_table_separated_render ) {
			if ( $options['heading'] && $options['separated_heading'] ) {
				printf( '<%1$s>%2$s</%1$s>' . "\n", esc_html( $options['heading'] ), wp_kses_post( $options['separated_heading'] ) );
			}

			echo '<table class="form-table">' . "\n";
		}

		if ( ! $is_table_rows_render && 'after' === $options['separator'] ) {
			echo "<hr />\n";
		}

		/**
		 * Allow hooking into after the form field output for a group is rendered.
		 *
		 * @since 2.8.0
		 *
		 * @param Whatsit\Group $group The Group object.
		 */
		do_action( 'pods_form_render_fields_group_post', $group );
	}
}

/**
 * Get the list of Groups or Fields that are able to be shown.
 *
 * @since 2.8.0
 *
 * @param Pods  $pod     The Pods object.
 * @param array $options The customization options.
 *
 * @return Whatsit\Group[]|Whatsit\Field[] List of Groups or Fields that are able to be shown.
 */
function pods_form_get_visible_objects( $pod, array $options = [] ) {
	$defaults = [
		'section_field' => null,
		'section'       => null,
		'return_type'   => 'group',
	];

	$options = array_merge( $defaults, $options );

	$visible_groups = [];
	$visible_fields = [];

	$return_fields = 'field' === $options['return_type'];

	// Get groups/fields and render them.
	$groups = $pod->pod_data->get_groups();

	foreach ( $groups as $group ) {
		// Skip if the section does not match.
		if (
			$options['section']
			&& $options['section_field']
			&& (
				'any' === $options['section']
				|| ! in_array( $options['section'], (array) $group[ $options['section_field'] ], true )
			)
		) {
			continue;
		}

		$fields = $group->get_fields();

		if ( empty( $fields ) ) {
			continue;
		}

		if ( ! pods_permission( $group ) ) {
			continue;
		}

		$field_found = false;

		foreach ( $fields as $field ) {
			if ( ! pods_permission( $field ) ) {
				continue;
			}

			if ( pods_v( 'hidden', $field, false ) ) {
				continue;
			}

			if ( $return_fields ) {
				$visible_fields[] = $field;

				continue;
			}

			$field_found = true;

			break;
		}

		if ( ! $field_found ) {
			continue;
		}

		$visible_groups[] = $group;
	}

	if ( $return_fields ) {
		return $visible_fields;
	}

	return $visible_groups;
}

/**
 * Validate the submitted fields from the form.
 *
 * @since 2.8.0
 *
 * @param string          $name      The object name.
 * @param int|string|null $object_id The object ID.
 * @param array           $options   The customization options.
 *
 * @return true|WP_Error[]|null True if the fields validate, a list of WP_Error objects with validation errors, or null if Pod does not exist.
 */
function pods_form_validate_submitted_fields( $name, $object_id = null, array $options = [] ) {
	$pod = pods( $name, $object_id, true );

	if ( ! $pod ) {
		return null;
	}

	// Get the fields.
	$options['return_type'] = 'field';

	// Get fields and save them.
	$fields = pods_form_get_visible_objects( $pod, $options );

	$api = pods_api();

	// Enforce WP_Error objects for validation errors.
	$api->display_errors = 'wp_error';

	$errors = [];

	foreach ( $fields as $field ) {
		$field_name = $field['name'];

		$value = pods_form_get_submitted_field_value( $field_name );

		$field_is_valid = $api->handle_field_validation( $value, $field_name, [], $fields, $pod );

		if ( is_wp_error( $field_is_valid ) ) {
			$errors[] = $field_is_valid;
		}
	}

	// Check for validation errors.
	if ( ! empty( $errors ) ) {
		return $errors;
	}

	// Fields are valid.
	return true;
}

/**
 * Save the submitted fields from the form.
 *
 * @since 2.8.0
 *
 * @param string     $name        The object name.
 * @param int|string $object_id   The object ID.
 * @param bool       $is_new_item Whether this is a new item being saved.
 * @param array      $options     The customization options.
 *
 * @return int|null The saved item or null if the pod does not exist.
 */
function pods_form_save_submitted_fields( $name, $object_id, $is_new_item = false, array $options = [] ) {
	$pod = pods( $name, $object_id, true );

	if ( ! $pod ) {
		return null;
	}

	// Get the submitted field values.
	$data = pods_form_get_submitted_field_values( $name, $options );

	return $pod->save( $data, null, null, [
		'is_new_item' => $is_new_item,
		'podsmeta'    => true,
	] );
}

/**
 * Get the submitted field values from the form.
 *
 * @since 2.8.0
 *
 * @param string $name    The object name.
 * @param array  $options The customization options.
 *
 * @return array List of submitted field values and their values.
 */
function pods_form_get_submitted_field_values( $name, array $options = [] ) {
	// Get the submitted fields.
	$fields = pods_form_get_submitted_fields( $name, $options );

	$data = [];

	foreach ( $fields as $field ) {
		$field_name = $field['name'];

		$data[ $field_name ] = pods_form_get_submitted_field_value( $field_name );
	}

	return $data;
}

/**
 * Get the submitted field value for a field.
 *
 * @since 2.8.0
 *
 * @param string|array|Field $field  The field name or object.
 * @param string             $method The method to get the value from (default: post).
 *
 * @return mixed The submitted field value for a field.
 */
function pods_form_get_submitted_field_value( $field, $method = 'post' ) {
	$field_name = $field;

	if ( $field instanceof Field ) {
		$field_name = $field->get_name();
	} elseif ( is_array( $field ) ) {
		$field_name = $field['name'];
	} elseif ( ! is_string( $field ) ) {
		return '';
	}

	return pods_v( 'pods_meta_' . $field_name, $method, '' );
}


/**
 * Get the submitted fields from the form.
 *
 * @since 2.8.0
 *
 * @param string $name    The object name.
 * @param array  $options The customization options.
 *
 * @return array List of submitted fields and their values.
 */
function pods_form_get_submitted_fields( $name, array $options = [] ) {
	$pod = pods( $name, null, true );

	if ( ! $pod ) {
		return [];
	}

	// Get the fields.
	$options['return_type'] = 'field';

	// Get fields and save them.
	return pods_form_get_visible_objects( $pod, $options );
}
