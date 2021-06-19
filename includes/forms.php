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

	$pod = pods( $name, $object_id );

	// Return groups.
	$options['return_type'] = 'group';

	// Get groups/fields and render them.
	$groups = pods_form_get_visible_objects( $pod, $options );

	foreach ( $groups as $group ) {
		$fields = $group->get_fields();

		/**
		 * Allow hooking into before the form field output for a group is rendered.
		 *
		 * @since TBD
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

			pods_view( PODS_DIR . 'ui/forms/table-rows.php', compact( array_keys( get_defined_vars() ) ) );

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
		 * @since TBD
		 *
		 * @param Whatsit\Group $group The Group object.
		 */
		do_action( 'pods_form_render_fields_group_post', $group );
	}
}

/**
 * Get the list of Groups or Fields that are able to be shown.
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
 * Save the submitted fields from the form.
 *
 * @param string     $name        The object name.
 * @param int|string $object_id   The object ID.
 * @param bool       $is_new_item Whether this is a new item being saved.
 */
function pods_form_save_submitted_fields( $name, $object_id, $is_new_item = false, array $options = [] ) {
	$pod = pods( $name, $object_id );

	// Get the fields.
	$options['return_type'] = 'field';

	// Get fields and save them.
	$fields = pods_form_get_visible_objects( $pod, $options );

	$data = [];

	foreach ( $fields as $field ) {
		$field_name = $field['name'];

		$value = pods_v( 'pods_meta_' . $field_name, 'post' );

		if ( null === $value ) {
			continue;
		}

		$data[ $field_name ] = $value;
	}

	return $pod->save( $data, null, null, [
		'is_new_item' => $is_new_item,
		'podsmeta'    => true,
	] );
}
