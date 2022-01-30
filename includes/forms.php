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
 * Enqueue a script in a way that is compatible with enqueueing before the asset was registered.
 *
 * @since 2.8.6
 *
 * @param string           $handle    Name of the script. Should be unique.
 * @param string           $src       Full URL of the script, or path of the script relative to the WordPress root directory.
 *                                    Default empty.
 * @param string[]         $deps      Optional. An array of registered script handles this script depends on. Default empty array.
 * @param string|bool|null $ver       Optional. String specifying script version number, if it has one, which is added to the URL
 *                                    as a query string for cache busting purposes. If version is set to false, a version
 *                                    number is automatically added equal to current installed WordPress version.
 *                                    If set to null, no version is added.
 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
 *                                    Default 'false'.
 */
function pods_form_enqueue_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = false ) {
	// Use dynamic arguments to support future versions of wp_enqueue_script.
	$args = func_get_args();

	// Check that the script is already registered, or we are registering it when enqueueing.
	if ( ! empty( $src ) || wp_script_is( $handle, 'registered' ) ) {
		call_user_func_array( 'wp_enqueue_script', $args );

		return;
	}

	// The script was enqueued before the enqueue scripts action was called.
	add_action( 'pods_after_enqueue_scripts', static function() use ( $args ) {
		call_user_func_array( 'wp_enqueue_script', $args );
	} );
}

/**
 * Enqueue a style in a way that is compatible with enqueueing before the asset was registered.
 *
 * @since 2.8.6
 *
 * @param string           $handle Name of the stylesheet. Should be unique.
 * @param string           $src    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
 *                                 Default empty.
 * @param string[]         $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
 * @param string|bool|null $ver    Optional. String specifying stylesheet version number, if it has one, which is added to the URL
 *                                 as a query string for cache busting purposes. If version is set to false, a version
 *                                 number is automatically added equal to current installed WordPress version.
 *                                 If set to null, no version is added.
 * @param string           $media  Optional. The media for which this stylesheet has been defined.
 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
 *                                 '(orientation: portrait)' and '(max-width: 640px)'.
 */
function pods_form_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
	// Use dynamic arguments to support future versions of wp_enqueue_script.
	$args = func_get_args();

	// Check that the style is already registered, or we are registering it when enqueueing.
	if ( ! empty( $src ) || wp_style_is( $handle, 'registered' ) ) {
		call_user_func_array( 'wp_enqueue_style', $args );

		return;
	}

	// The style was enqueued before the enqueue scripts action was called.
	add_action( 'pods_after_enqueue_scripts', static function() use ( $args ) {
		call_user_func_array( 'wp_enqueue_style', $args );
	} );
}

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
		'section_field'                   => null,
		'section'                         => null,
		'separator'                       => 'before',
		'wrapper'                         => false,
		'wrapper_class'                   => null,
		'container_class'                 => null,
		'heading'                         => 'h2',
		'heading_class'                   => null,
		'heading_sub_container'           => null,
		'heading_sub_container_class'     => null,
		'separated_heading'               => null,
		'render'                          => 'table',
	];

	$options = array_merge( $defaults, $options );

	$pod = pods( $name, $object_id, true );

	if ( ! $pod ) {
		return;
	}

	// Return groups.
	$options['return_type'] = 'group';

	$wrapper_classes = [
		'pods-form-wrapper',
		'pods-form-wrapper--pod--' . $name,
	];

	if ( $options['wrapper_class'] ) {
		if ( ! is_array( $options['wrapper_class'] ) ) {
			$options['wrapper_class'] = explode( ' ', $options['wrapper_class'] );
		}

		foreach ( $options['wrapper_class'] as $wrapper_class ) {
			$wrapper_classes[] = $wrapper_class;
		}
	}

	$wrapper_classes = array_map( 'sanitize_html_class', $wrapper_classes );
	$wrapper_classes = implode( ' ', $wrapper_classes );

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
		$is_div_rows_render        = 'div-rows' === $options['render'];

		if ( $is_table_separated_render ) {
			echo "</table>\n";
		}

		if ( ! $is_table_rows_render && 'before' === $options['separator'] ) {
			echo "<hr />\n";
		}

		if ( $is_table_rows_render ) {
			printf(
				'<tr><td colspan="2" class="%s">',
				$wrapper_classes
			);
		} elseif ( $is_div_rows_render && $options['wrapper'] ) {
			printf(
				'<div class="%s">',
			    $wrapper_classes
            );
		}

		if ( $options['heading'] ) {
			$heading_classes = [
				'pods-form-heading',
				'pods-form-heading--pod-' . $name,
				'pods-form-heading--group',
				'pods-form-heading--group-' . $group['name'],
			];

			if ( $options['heading_class'] ) {
				if ( ! is_array( $options['heading_class'] ) ) {
					$options['heading_class'] = explode( ' ', $options['heading_class'] );
				}

				foreach ( $options['heading_class'] as $heading_class ) {
					$heading_classes[] = $heading_class;
				}
			}

			$heading_classes = array_map( 'sanitize_html_class', $heading_classes );
			$heading_classes = implode( ' ', $heading_classes );

			$heading_text = wp_kses_post( $group['label'] );

			if ( $options['heading_sub_container'] ) {
				$heading_sub_container_classes = [];

				if ( $options['heading_sub_container_class'] ) {
					if ( ! is_array( $options['heading_sub_container_class'] ) ) {
						$options['heading_sub_container_class'] = explode( ' ', $options['heading_sub_container_class'] );
					}

					foreach ( $options['heading_sub_container_class'] as $heading_sub_container_class ) {
						$heading_sub_container_classes[] = $heading_sub_container_class;
					}
				}

				$heading_sub_container_extra_html = '';

				if ( ! empty( $heading_sub_container_classes ) ) {
					$heading_sub_container_classes = array_map( 'sanitize_html_class', $heading_sub_container_classes );
					$heading_sub_container_classes = implode( ' ', $heading_sub_container_classes );

					$heading_sub_container_extra_html = sprintf(
						' class="%s"',
						esc_attr( $heading_sub_container_classes )
					);
				}

				$heading_text = sprintf(
					'<%1$s%2$s>%3$s</%1$s>' . "\n",
					esc_html( $options['heading_sub_container'] ),
					$heading_sub_container_extra_html,
					$heading_text
				);
			}

			printf(
				'<%1$s class="%2$s">%3$s</%1$s>' . "\n",
				esc_html( $options['heading'] ),
				esc_attr( $heading_classes ),
				$heading_text
			);
		}

		if ( $is_table_rows_render ) {
			echo '</td></tr>';
		}

		$id = $object_id;

		$container_classes = [
			'pods-form',
			'pods-form-container',
			'pods-form-container--pod--' . $name,
			'pods-form-container--group',
			'pods-form-container--group--' . $group['name'],
		];

		if ( $options['container_class'] ) {
			if ( ! is_array( $options['container_class'] ) ) {
				$options['container_class'] = explode( ' ', $options['container_class'] );
			}

			foreach ( $options['container_class'] as $container_class ) {
				$container_classes[] = $container_class;
			}
		}

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

			pods_view( PODS_DIR . 'ui/forms/div-rows.php', compact( array_keys( get_defined_vars() ) ) );

			echo "</div>\n";
		}

		if ( $is_table_separated_render ) {
			if ( $options['heading'] && $options['separated_heading'] ) {
				printf( '<%1$s>%2$s</%1$s>' . "\n", esc_html( $options['heading'] ), wp_kses_post( $options['separated_heading'] ) );
			}

			echo '<table class="form-table">' . "\n";
		} elseif ( $is_div_rows_render && $options['wrapper'] ) {
			echo '</div>';
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
				$visible_fields[ $field['name'] ] = $field;

				continue;
			}

			$field_found = true;

			break;
		}

		if ( ! $field_found ) {
			continue;
		}

		$visible_groups[ $group['name'] ] = $group;
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
	$fields = array_keys( $fields );

	$data = [];

	foreach ( $fields as $field_name ) {
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
