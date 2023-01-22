<?php
/**
 * Utility function for processesing fromtier based templates
 *
 * @package Pods_Frontier_Template_Editor\view_template
 */

use Pods\Whatsit\Pod;
use Pods\Whatsit\Field;

// add filters
add_filter( 'pods_templates_post_template', 'frontier_end_template', 25, 4 );
add_filter( 'pods_templates_do_template', 'frontier_do_shortcode', 25, 1 );

// template shortcode handlers
add_shortcode( 'pod_sub_template', 'frontier_do_subtemplate' );
add_shortcode( 'pod_once_template', 'frontier_template_once_blocks' );
add_shortcode( 'pod_after_template', 'frontier_template_blocks' );
add_shortcode( 'pod_before_template', 'frontier_template_blocks' );
add_shortcode( 'pod_if_field', 'frontier_if_block' );

/**
 * Return array of valid frontier type shortcode tags
 *
 * @return array
 */
function frontier_get_shortcodes() {

	$shortcodes = array(
		'each',
		'pod_sub_template',
		'once',
		'pod_once_template',
		'before',
		'pod_before_template',
		'after',
		'pod_after_template',
		'if',
		'pod_if_field',
	);

	return $shortcodes;
}

/**
 * @param $content
 *
 * @return string
 * @since 2.4.3
 */
function frontier_do_shortcode( $content ) {
	// Run only Pods template shortcodes (each, once, before, after, if, and else).
	return pods_do_shortcode( $content, frontier_get_shortcodes() );
}

/**
 * @param $content
 *
 * @return string
 * @since 2.8.6
 */
function frontier_do_other_shortcodes( $content ) {
	// Run all other shortcodes but ignore the Pods template shortcodes (each, once, before, after, if, and else).
	return pods_do_shortcode( $content, [], frontier_get_shortcodes() );
}

/**
 * decodes a like nested shortcode based template
 *
 * @param string encoded template to be decoded
 * @param array attributed provided from parent
 *
 * @return string
 * @since 2.4.0
 */
function frontier_decode_template( $code, $atts ) {
	$code = base64_decode( $code );

	if ( isset( $atts['pod'] ) ) {
		$code = str_replace( '{@pod}', $atts['pod'], $code );
	}
	if ( isset( $atts['id'] ) ) {
		$code = str_replace( '{@EntryID}', $atts['id'], $code );
	}
	if ( isset( $atts['index'] ) ) {
		$code = str_replace( '{_index}', $atts['index'], $code );
	}

	return $code;
}

/**
 * processes if condition within a template
 *
 * @param array  $attributes attributes from template
 * @param string $code encoded template to be decoded
 *
 * @return string
 * @since 2.4.0
 */
function frontier_if_block( $attributes, $code ) {
	$attributes = array_merge( [
		'pod'     => null,
		'id'      => null,
		'field'   => null,
		'value'   => null,
		'compare' => null,
		'index'   => null,
	], $attributes );

	$pod = Pods_Templates::get_obj( $attributes['pod'], $attributes['id'] );

	if ( ! $pod || ! $pod->exists() ) {
		return '';
	}

	$code = explode( '[else]', frontier_decode_template( $code, $attributes ) );

	// field data
	$field_data = null;
	$field_type = 'text';

	if ( ! empty( $attributes['field'] ) ) {
		$supported_calculations = [
			'_zebra'          => 'number',
			'_position'       => 'number',
			'_total'          => 'number',
			'_total_found'    => 'number',
			'_total_all_rows' => 'number',
			'_total_pages'    => 'number',
			'_current_page'   => 'number',
		];

		if ( isset( $supported_calculations[ $attributes['field'] ] ) ) {
			// Support [if field="_position" value="2"] and other calculation value handlers.
			$field_data = $pod->field( $attributes['field'] );
			$field_type = $supported_calculations[ $attributes['field'] ];
		} elseif ( '_index' === $attributes['field'] ) {
			$field_data = pods_v( 'index', $attributes );
		} else {
			$field_data = $pod->field( $attributes['field'] );
			$field_type = $pod->fields( $attributes['field'], 'type' );
		}
	}

	$is_empty = true;

	if ( null !== $field_data ) {
		if ( empty( $field_type ) ) {
			$field_type = 'text';
		}

		$is_empty = PodsForm::field_method( $field_type, 'values_are_empty', $field_data );
	}

	$has_value_compare_attribute = null !== $attributes['value'] || null !== $attributes['compare'];

	if ( ! $is_empty || $has_value_compare_attribute ) {
		// Check if we do not have a value to compare with.
		if ( ! $has_value_compare_attribute ) {
			$template = $code[0];

			// Maybe run any shortcode.
			if ( defined( 'PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES' ) && PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES ) {
				$template = frontier_do_other_shortcodes( $template );
			}

			// Field exists and is not empty, use [IF] content
			$template = $pod->do_magic_tags( $template );

			return frontier_do_shortcode( $template );
		}

		$first_character = substr( $attributes['value'], 0, 1 );

		// check if + or - are present
		if ( '+' === $first_character ) {
			// is greater
			$attributes['value']   = (float) substr( $attributes['value'], 1 ) + 1;
			$attributes['compare'] = '>';
		} elseif ( '-' === $first_character ) {
			// is smaller
			$attributes['value']   = (float) substr( $attributes['value'], 1 ) - 1;
			$attributes['compare'] = '<';
		}

		if ( empty( $attributes['compare'] ) ) {
			$attributes['compare'] = '=';
		}

		$attributes['compare'] = html_entity_decode( $attributes['compare'] );
		$attributes['compare'] = strtoupper( $attributes['compare'] );

		// Normalize the compare.
		$comparisons = [
			'=',
			'!=',
			'IN',
			'NOT IN',
			'EXISTS',
			'NOT EXISTS',
			'>',
			'>=',
			'<',
			'<=',
			'LIKE',
			'NOT LIKE',
			'EMPTY',
			'NOT EMPTY',
		];

		// Comparison not supported, assume it does not match.
		if ( ! in_array( $attributes['compare'], $comparisons, true ) ) {
			return '';
		}

		$pass = false;

		$maybe_array = is_array( $field_data ) || is_array( $attributes['value'] );

		// Handle comparison.
		if ( '=' === $attributes['compare'] ) {
			if ( $maybe_array ) {
				$pass = in_array( (string) $attributes['value'], (array) $field_data, false );
			} else {
				$pass = (string) $field_data === (string) $attributes['value'];
			}
		} elseif ( '!=' === $attributes['compare'] ) {
			if ( $maybe_array ) {
				$pass = ! in_array( (string) $attributes['value'], (array) $field_data, false );
			} else {
				$pass = (string) $field_data !== (string) $attributes['value'];
			}
		} elseif ( 'EXISTS' === $attributes['compare'] ) {
			$pass = null !== $field_data && [] !== $field_data;
		} elseif ( 'NOT EXISTS' === $attributes['compare'] ) {
			$pass = null === $field_data || [] === $field_data;
		} elseif ( $maybe_array ) {
			// We do not support comparisons for array values beyond equals.
			$pass = false;
		} elseif ( 'IN' === $attributes['compare'] ) {
			$pass = in_array( (string) $field_data, explode( ',', $attributes['value'] ), false );
		} elseif ( 'NOT IN' === $attributes['compare'] ) {
			$pass = ! in_array( (string) $field_data, explode( ',', $attributes['value'] ), false );
		} elseif ( '>' === $attributes['compare'] ) {
			$pass = (float) $field_data > (float) $attributes['value'];
		} elseif ( '>=' === $attributes['compare'] ) {
			$pass = (float) $field_data >= (float) $attributes['value'];
		} elseif ( '<' === $attributes['compare'] ) {
			$pass = (float) $field_data < (float) $attributes['value'];
		} elseif ( '<=' === $attributes['compare'] ) {
			$pass = (float) $field_data <= (float) $attributes['value'];
		} elseif ( 'LIKE' === $attributes['compare'] || 'NOT LIKE' === $attributes['compare'] ) {
			$field_data = (string) $field_data;

			$attributes['value'] = (string) $attributes['value'];

			if ( false !== strpos( $attributes['value'], '%' ) ) {
				// Handle % LIKE values.
				$attributes['value'] = str_replace( '%', '.*', preg_quote( $attributes['value'], '/' ) );

				$found = preg_match( '/^' . $attributes['value'] . '$/Uim', $field_data );

				if ( 0 === $found ) {
					$found = false;
				}
			} else {
				$found = stripos( $field_data, $attributes['value'] );
			}

			if ( 'LIKE' === $attributes['compare'] ) {
				// Check if the string contains the match.
				$pass = false !== $found;
			} elseif ( 'NOT LIKE' === $attributes['compare'] ) {
				// Check if the string does not contain the match.
				$pass = false === $found;
			}
		} elseif ( 'EMPTY' === $attributes['compare'] ) {
			$pass = $is_empty;
		} elseif ( 'NOT EMPTY' === $attributes['compare'] ) {
			$pass = ! $is_empty;
		}

		if ( $pass ) {
			$template = $code[0];

			// Maybe run any shortcode.
			if ( defined( 'PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES' ) && PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES ) {
				$template = frontier_do_other_shortcodes( $template );
			}

			// IF statement true, use [IF] content as template.
			$template = $pod->do_magic_tags( $template );

			return frontier_do_shortcode( $template );
		}

		if ( isset( $code[1] ) ) {
			$template = $code[1];

			// Maybe run any shortcode.
			if ( defined( 'PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES' ) && PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES ) {
				$template = frontier_do_other_shortcodes( $template );
			}

			// There is an [ELSE] tag
			$template = $pod->do_magic_tags( $template );

			return frontier_do_shortcode( $template );
		}

		// Value did not match (and no [ELSE]), nothing should be displayed.
		return '';
	}

	if ( isset( $code[1] ) ) {
		$template = $code[1];

		// Maybe run any shortcode.
		if ( defined( 'PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES' ) && PODS_TEMPLATES_ALLOW_OTHER_SHORTCODES ) {
			$template = frontier_do_other_shortcodes( $template );
		}

		// No value or field is empty and there is an [ELSE] tag. Use [ELSE].
		$template = $pod->do_magic_tags( $template );

		return frontier_do_shortcode( $template );
	}

	// No match at all for the format we support.
	return '';
}

/**
 * processes before and after template blocks
 *
 * @param array attributes from template
 * @param string encoded template to be decoded
 * @param string shortcode slug used to process
 *
 * @return null
 * @since 2.4.0
 */
function frontier_template_blocks( $atts, $code, $slug ) {

	global $template_post_blocks;
	if ( ! isset( $template_post_blocks ) ) {
		$template_post_blocks = array(
			'before' => null,
			'after'  => null,
		);
	}
	if ( $slug === 'pod_before_template' ) {
		if ( ! isset( $template_post_blocks['before'][ $atts['pod'] ] ) ) {
			$template_post_blocks['before'][ $atts['pod'] ] = pods_do_shortcode(
				frontier_decode_template( $code, $atts ), array(
					'if',
					'else',
				)
			);
		}
	} elseif ( $slug === 'pod_after_template' ) {
		if ( ! isset( $template_post_blocks['after'][ $atts['pod'] ] ) ) {
			$template_post_blocks['after'][ $atts['pod'] ] = pods_do_shortcode(
				frontier_decode_template( $code, $atts ), array(
					'if',
					'else',
				)
			);
		}
	}

	return null;
}

/**
 * processes once blocks
 *
 * @param array attributes from template
 * @param string encoded template to be decoded
 *
 * @return string template code
 * @since 2.4.0
 */
function frontier_template_once_blocks( $atts, $code ) {

	global $frontier_once_hashes;

	if ( ! isset( $frontier_once_hashes ) ) {
		$frontier_once_hashes = array();
	}

	$blockhash = md5( $code . $atts['id'] );
	if ( in_array( $blockhash, $frontier_once_hashes, true ) ) {
		return '';
	}
	$frontier_once_hashes[] = $blockhash;

	return pods_do_shortcode( frontier_decode_template( $code, $atts ), frontier_get_shortcodes() );
}

/**
 * processes template code within an each command from the base template
 *
 * @param array attributes from template
 * @param string template to be processed
 *
 * @return null
 * @since 2.4.0
 */
function frontier_do_subtemplate( $atts, $content ) {
	$out        = '';
	$field_name = $atts['field'];

	$pod = Pods_Templates::get_obj( $atts['pod'], $atts['id'] );

	if ( ! $pod || ! $pod->exists() ) {
		return '';
	}

	$field = $pod->fields( $field_name );

	$is_repeatable_field = $field && $field->is_repeatable();

	$entries = $pod->field( [
		'name'                         => $field_name,
		'display'                      => $is_repeatable_field,
		'display_process_individually' => $is_repeatable_field,
	] );

	if ( $field && ! empty( $entries ) ) {
		$entries = (array) $entries;

		// Force array even for single items since the logic below is using loops.
		if (
			(
				$is_repeatable_field
				|| 'single' === $field->get_single_multi()
			)
			&& ! isset( $entries[0] )
		) {
			$entries = array( $entries );
		}

		// Object types that could be Pods
		$object_types = array( 'post_type', 'pod' );

		if ( $is_repeatable_field ) {
			foreach ( $entries as $key => $entry ) {
				$template = frontier_decode_template( $content, $atts );

				$template = str_replace( '{_key}', '{@_index}', $template );
				$template = str_replace( '{@_key}', '{@_index}', $template );
				$template = str_replace( '{_index}', '{@_index}', $template );

				$entry = array(
					'_index' => $key,
					'_value' => $entry,
				);

				$out .= frontier_pseudo_magic_tags( $template, $entry, $pod, true );
			}
		}
		/**
		 * Note on the change below for issue #3018:
		 * ... || 'taxonomy' == $pod->fields[ $atts[ 'field' ] ][ 'type' ]
		 *
		 * calling field() above for a taxonomy object field will populate
		 * $pod->fields[ $field_name ] for the object field's data, in this
		 * case a taxonomy object field. Without calling
		 * $pod->field( $field_name ), it would not normally be available in
		 * the $pod->fields array and is something to not expect to be there in
		 * 3.0 as this was unintentional.
		 */
		elseif ( 'taxonomy' === $field['type'] || in_array( $field['pick_object'], $object_types, true ) ) {
			// Match any Pod object or taxonomy
			foreach ( $entries as $key => $entry ) {
				$subpod = pods_get_instance( $field['pick_val'] );

				if ( ! $subpod || ! $subpod->valid() ) {
					continue;
				}

				$subatts = array(
					'id'  => $entry[ $subpod->pod_data['field_id'] ],
					'pod' => $field['pick_val'],
				);

				$template = frontier_decode_template( $content, array_merge( $atts, $subatts ) );
				$template = str_replace( '{_key}', $key, $template );
				$template = str_replace( '{@_key}', $key, $template );
				$template = str_replace( '{_index}', $key, $template );
				$template = str_replace( '{@' . $field_name . '.', '{@', $template );

				// Kludge to work with taxonomies, pending a better solution: see issue #3018
				$target_id = null;
				if ( isset( $entry['ID'] ) ) {
					$target_id = $entry['ID'];
				} elseif ( isset( $entry['id'] ) ) {
					// Advanced Content Types have lowercase 'id'
					$target_id = $entry['id'];
				} elseif ( isset( $entry['term_id'] ) ) {
					$target_id = $entry['term_id'];
				}

				$out .= pods_shortcode(
					array(
						'name'  => $field['pick_val'],
						'slug'  => $target_id,
						'index' => $key,
					), $template
				);

			}//end foreach
		} elseif ( 'file' === $field['type'] ) {
			$template  = frontier_decode_template( $content, $atts );
			$media_pod = pods( 'media' );

			foreach ( $entries as $key => $entry ) {
				$template = str_replace( '{_key}', $key, $template );
				$template = str_replace( '{@_key}', $key, $template );
				$template = str_replace( '{_index}', $key, $template );

				if ( $media_pod && $media_pod->valid() && $media_pod->fetch( $entry['ID'] ) ) {
					$template   = str_replace( '{@' . $field_name . '.', '{@', $template );

					$entry_pod = $media_pod;
				} else {
					$template = str_replace( '{@_img', '{@image_attachment.' . $entry['ID'], $template );
					$template = str_replace( '{@_src', '{@image_attachment_url.' . $entry['ID'], $template );
					$template = str_replace( '{@' . $field_name . '}', '{@image_attachment.' . $entry['ID'] . '}', $template );

					// Fix for lowercase ID's.
					$entry['id'] = $entry['ID'];

					// Allow array-like tags.
					$template = frontier_pseudo_magic_tags( $template, $entry, $pod, true );

					// Fallback to parent Pod so above tags still work.
					$entry_pod = $pod;
				}

				$out .= pods_do_shortcode( $entry_pod->do_magic_tags( $template ), frontier_get_shortcodes() );
			}
		} elseif ( isset( $field['table_info'], $field['table_info']['pod'] ) ) {
			// Relationship to something that is extended by Pods
			$entries = $pod->field( array( 'name' => $field_name, 'output' => 'pod' ) );
			foreach ( $entries as $key => $entry ) {
				$subatts = array(
					'id' => $entry->id,
					'pod' => $entry->pod,
					'index' => $key,
				);

				$template = frontier_decode_template( $content, array_merge( $atts, $subatts ) );

				$template = str_replace( '{_key}', $key, $template );
				$template = str_replace( '{@_key}', $key, $template );
				$template = str_replace( '{_index}', $key, $template );
				$template = str_replace( '{@' . $field_name . '.', '{@', $template );

				$out .= pods_do_shortcode( $entry->do_magic_tags( $template ), frontier_get_shortcodes() );
			}
		} else {
			$template = frontier_decode_template( $content, $atts );

			// Relationship to something other than a Pod (ie: user)
			foreach ( $entries as $key => $entry ) {
				$template = frontier_decode_template( $content, $atts );

				$template = str_replace( '{_key}', '{@_index}', $template );
				$template = str_replace( '{@_key}', '{@_index}', $template );
				$template = str_replace( '{_index}', '{@_index}', $template );

				if ( ! is_array( $entry ) ) {
					$entry = array(
						'_index' => $key,
						'_value' => $entry,
					);
				}

				$out .= pods_do_shortcode( frontier_pseudo_magic_tags( $template, $entry, $pod ), frontier_get_shortcodes() );
			}
		}//end if
	}//end if

	return pods_do_shortcode( $out, frontier_get_shortcodes() );
}

/**
 * Search and replace like Pods magic tags but with an array of data instead of a Pod
 *
 * @param Pod     $pod
 * @param string  $template
 * @param array   $data
 * @param boolean $skip_unknown If true then values not in $data will not be touched
 *
 * @return string
 * @since 2.7.0
 */
function frontier_pseudo_magic_tags( $template, $data, $pod = null, $skip_unknown = false ) {
	return preg_replace_callback(
		'/({@(.*?)})/m', function ( $tag ) use ( $pod, $data, $skip_unknown ) {

			// This is essentially Pods->process_magic_tags() but with the Pods specific code ripped out
			if ( is_array( $tag ) ) {
				if ( ! isset( $tag[2] ) && strlen( trim( $tag[2] ) ) < 1 ) {
					return '';
				}

				$original_tag = $tag[0];
				$tag          = $tag[2];
			}

			$tag = trim( $tag, ' {@}' );
			$tag = explode( ',', $tag );

			if ( empty( $tag ) || ! isset( $tag[0] ) || strlen( trim( $tag[0] ) ) < 1 ) {
				return '';
			}

			foreach ( $tag as $k => $v ) {
				$tag[ $k ] = trim( $v );
			}

			$field_name = $tag[0];

			$helper_name = '';
			$before      = '';
			$after       = '';

			if ( isset( $data[ $field_name ] ) ) {
				$value = $data[ $field_name ];
				if ( isset( $tag[1] ) && ! empty( $tag[1] ) ) {
					$helper_name = $tag[1];

					if ( isset( $pod ) ) {
						$value = $pod->helper( $helper_name, $value, $field_name );
					}
				}
			} else {
				if ( $skip_unknown ) {
					return $original_tag;
				}
				$value = '';
			}

			if ( isset( $tag[2] ) && ! empty( $tag[2] ) ) {
				$before = $tag[2];
			}

			if ( isset( $tag[3] ) && ! empty( $tag[3] ) ) {
				$after = $tag[3];
			}

			$value = apply_filters( 'pods_do_magic_tags', $value, $field_name, $helper_name, $before, $after );

			if ( is_array( $value ) ) {
				$value = pods_serial_comma(
					$value, array(
						'field'  => $field_name,
						'fields' => $pod->fields,
					)
				);
			}

			if ( null !== $value && false !== $value ) {
				return $before . $value . $after;
			}

			return '';
		}, $template
	);
}

/**
 * processes template code within an each command from the base template
 *
 * @param array  $code     The code to filter.
 * @param string $template The template to be processed.
 * @param Pods   $pod      The Pods object.
 *
 * @return null
 * @since 2.4.0
 */
function frontier_prefilter_template( $code, $template, $pod ) {

	global $frontier_once_tags;

	$commands = array(
		'each'   => 'pod_sub_template',
		'once'   => 'pod_once_template',
		'before' => 'pod_before_template',
		'after'  => 'pod_after_template',
		'if'     => 'pod_if_field',
	);

	$commands = array_merge( $commands, get_option( 'pods_frontier_extra_commands', array() ) );

	/**
	 * Add additional control blocks to Pods templates
	 *
	 * Can also be use to remove each/once/before/after/if functionality from Pods Templates
	 *
	 * @param array $commands The control blocks in the form of 'tag' => 'shortcode'
	 *
	 * @return array An array of control blocks, and shortcodes used to power them.
	 *
	 * @since 1.0.0
	 */
	$commands = apply_filters( 'pods_frontier_template_commands', $commands );

	$aliases = array();
	foreach ( $commands as $command => $shortcode ) {
		//preg_match_all( '/(\[' . $command . '(?<attributes>.*?)]|\[\/' . $command . '\])/m', $code, $matches );
		preg_match_all( '/('
            . '\[' . preg_quote( $command, '/' ) . '\s*'
            . '(?<field_attr>field="(?<field>[^"]*)")*' . '\s*'
            . '(?<value_attr>value="(?<value>[^"]*)")*' . '\s*'
            . '(?<compare_attr>compare="(?<compare>[^"]*)")*' . '\s*'
            . '(?<other_attributes>[^\]]*)'
            . ']|\[\/' . preg_quote( $command, '/' ) . '\]'
            . ')/m', $code, $matches );

		if ( ! empty( $matches[0] ) ) {
			// holder for found tags.
			$tags       = array();
			$indexCount = 0;
			foreach ( $matches[0] as $key => $tag ) {
				if ( false !== strpos( $tag, '[/' ) ) {
					// close tag
					$indexCount --;
					$newclose = $tags[ $indexCount ];
					$code     = preg_replace( '/(' . preg_quote( $tag, '/' ) . ')/m', '[/' . $newclose . ']', $code, 1 );

					continue;
				}//end if

				// Handle open tags.

				$field   = null;
				$value   = null;
				$compare = null;

				$pod_name = '{@pod}';
				$ID       = '{@EntryID}';

				if ( '' !== $matches['field_attr'][ $key ] ) {
					$field = $matches['field'][ $key ];

					if ( '' !== $matches['value_attr'][ $key ] ) {
						$value = $matches['value'][ $key ];
					}

					if ( '' !== $matches['compare_attr'][ $key ] ) {
						$compare = $matches['compare'][ $key ];
					}
				} elseif ( '' !== $matches['other_attributes'][ $key ] ) {
					// get atts if any
					// $atts = shortcode_parse_atts(str_replace('.', '____', $matches[2][$key]));
					$pattern = '/(?<field>[\w\.\_\-]+)\s*=\s*"(?<value>[^"]*)"(?:\s|$)/';
					$field   = trim( $matches['other_attributes'][ $key ] );
					$text    = preg_replace( "/[\x{00a0}\x{200b}]+/u", ' ', $field );

					if ( preg_match_all( $pattern, $text, $field_value_match, PREG_SET_ORDER ) ) {
						$field = $field_value_match[0]['field'];
						$value = $field_value_match[0]['value'];
					}
				}//end if

				if ( $field && false !== strpos( $field, '.' ) ) {
					// Take the last element off of the array and use the ID.
					$field_path = explode( '.', $field );
					$last_field = array_pop( $field_path );
					$field_path = implode( '.', $field_path );

					$related_field = $pod->pod_data->get_field( $field_path );

					if ( $related_field instanceof Field && 1 === $related_field->get_limit() ) {
						$related_pod = $related_field->get_related_object();

						if ( $related_pod instanceof Pod ) {
							$table_field_id = $related_pod->get_arg( 'field_id' );

							if ( $table_field_id ) {
								// Use the other pod.
								$pod_name = $related_pod->get_name();

								// Rebuild the ID used for the lookup.
								$ID = '{@' . $field_path . '.' . $table_field_id . '}';

								// Override the field to use.
								$field = $last_field;
							}
						}
					}
				}

				$atts  = ' pod="' . esc_attr( $pod_name ) . '"';
				$atts .= ' id="' . esc_attr( $ID ) . '"';

				if ( $field ) {
					$atts .= ' field="' . esc_attr( $field ) . '"';
				}

				if ( null !== $value ) {
					$atts .= ' value="' . esc_attr( $value ) . '"';
				}

				if ( null !== $compare ) {
					$atts .= ' compare="' . esc_attr( $compare ) . '"';
				}

				$newtag              = $shortcode . '__' . $key;
				$tags[ $indexCount ] = $newtag;
				$aliases[]           = $newtag;

				$code = preg_replace( '/(' . preg_quote( $tag, '/' ) . ')/m', '[' . $newtag . $atts . ' index="{_index}"]', $code, 1 );

				$indexCount ++;
			}//end foreach
		}//end if
	}//end foreach
	// get new aliased shortcodes
	if ( ! empty( $aliases ) ) {
		$code = frontier_backtrack_template( $code, $aliases );
	}
	$code = str_replace( '{@pod}', $pod->pod, $code );
	$code = str_replace( '{@EntryID}', '{@' . $pod->pod_data['field_id'] . '}', $code );

	return $code;
}

/**
 * @param $code
 * @param $aliases
 *
 * @return mixed
 */
function frontier_backtrack_template( $code, $aliases ) {

	$regex = frontier_get_regex( $aliases );
	preg_match_all( '/' . $regex . '/s', $code, $used );

	if ( ! empty( $used[2] ) ) {
		foreach ( $used[2] as $key => $alias ) {
			$shortcodes = explode( '__', $alias );
			$content    = $used[5][ $key ];
			$atts       = shortcode_parse_atts( $used[3][ $key ] );

			$new_shortcode_name = $shortcodes[0];

			$new_shortcode_atts_data = [
				'seq' => $shortcodes[1],
			];

			if ( ! empty( $atts ) ) {
				if ( ! empty( $atts['field'] ) && false !== strpos( $atts['field'], '.' ) ) {
					$content = str_replace( $atts['field'] . '.', '', $content );
				}

				preg_match_all( '/' . $regex . '/s', $content, $subused );

				if ( ! empty( $subused[2] ) ) {
					$content = frontier_backtrack_template( $content, $aliases );
				}

				$new_shortcode_atts_data[] = trim( $used[3][ $key ] );
			}

			$new_shortcode_atts = '';

			foreach ( $new_shortcode_atts_data as $new_shortcode_att_key => $new_shortcode_att_data ) {
				if ( is_int( $new_shortcode_att_key ) ) {
					$new_shortcode_atts .= ' ' . $new_shortcode_att_data;
				} else {
					$new_shortcode_atts .= ' ' . $new_shortcode_att_key . '="' . esc_attr( $new_shortcode_att_data ) . '"';
				}
			}

			// Build the new shortcode.
			$new_shortcode = sprintf(
				'[%1$s %2$s]%3$s[/%1$s]',
				$new_shortcode_name,
				$new_shortcode_atts,
				base64_encode( $content )
			);

			$code = str_replace( $used[0][ $key ], $new_shortcode, $code );
		}
	}//end if

	return $code;
}

/**
 * @param $codes
 *
 * @return string
 */
function frontier_get_regex( $codes ) {

	// A custom version of the shortcode regex as to only use podsfrontier codes.
	// this makes it easier to cycle through and get the used codes for inclusion
	$validcodes = join( '|', array_map( 'preg_quote', $codes ) );

	$regex_pieces = array(
		// Opening bracket
		'\\[',
		// 1: Optional second opening bracket for escaping shortcodes: [[tag]]
		'(\\[?)',
		// 2: selected codes only
		"($validcodes)",
		// Word boundary
		'\\b',
		// 3: Unroll the loop: Inside the opening shortcode tag
		'(',
		// Not a closing bracket or forward slash
		'[^\\]\\/]*',
		// A forward slash not followed by a closing bracket
		'(?:' . '\\/(?!\\])',
		// Not a closing bracket or forward slash
		'[^\\]\\/]*',
		// 4: Self closing tag ...
		')*?' . ')' . '(?:' . '(\\/)',
		// ... and closing bracket
		'\\]',
		// Closing bracket
		'|' . '\\]',
		// 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
		'(?:' . '(',
		// Not an opening bracket
		'[^\\[]*+',
		// An opening bracket not followed by the closing shortcode tag
		'(?:' . '\\[(?!\\/\\2\\])',
		// Not an opening bracket
		'[^\\[]*+',
		// Closing shortcode tag
		')*+' . ')' . '\\[\\/\\2\\]',
		// 6: Optional second closing brocket for escaping shortcodes: [[tag]]
		')?' . ')' . '(\\]?)',
	);

	return implode( '', $regex_pieces );
}

/**
 * @param $code
 * @param $base
 * @param $template
 * @param $pod
 *
 * @return string
 */
function frontier_end_template( $code, $base, $template, $pod ) {

	global $template_post_blocks;

	if ( ! empty( $template_post_blocks['before'][ $pod->pod ] ) ) {
		$code = $template_post_blocks['before'][ $pod->pod ] . $code;

		unset( $template_post_blocks['before'][ $pod->pod ] );
	}

	if ( ! empty( $template_post_blocks['after'][ $pod->pod ] ) ) {
		$code .= $template_post_blocks['after'][ $pod->pod ];

		unset( $template_post_blocks['after'][ $pod->pod ] );
	}

	return pods_do_shortcode( $code, frontier_get_shortcodes() );
}
