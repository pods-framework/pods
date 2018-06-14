<?php
/**
 * Utility function for processesing fromtier based templates
 *
 * @package Pods_Frontier_Template_Editor\view_template
 */

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

	$content = pods_do_shortcode( $content, frontier_get_shortcodes() );

	return $content;

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
	$pod  = pods( $atts['pod'] );

	if ( isset( $atts['pod'] ) ) {
		$code = str_replace( '@pod', $atts['pod'], $code );
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
 * @param array  $atts attributes from template
 * @param string $code encoded template to be decoded
 *
 * @return string
 * @since 2.4.0
 */
function frontier_if_block( $atts, $code ) {

	$pod = pods( $atts['pod'], $atts['id'] );

	if ( ! $pod || ! $pod->valid() || ! $pod->exists() ) {
		return '';
	}

	$code = explode( '[else]', frontier_decode_template( $code, $atts ) );

	// sysvals
	$system_values = array(
		'_index',
	);

	// field data
	$field_data = null;
	$field_type = null;

	if ( in_array( $atts['field'], $system_values, true ) ) {
		switch ( $atts['field'] ) {
			case '_index':
				$field_data = $atts['index'];

				break;
		}
	} else {
		$field_data = $pod->field( $atts['field'] );

		$field_type = $pod->fields( $atts['field'], 'type' );
	}

	$is_empty = true;

	if ( null !== $field_data ) {
		if ( empty( $field_type ) ) {
			$field_type = 'text';
		}

		$is_empty = PodsForm::field_method( $field_type, 'values_are_empty', $field_data );
	}

	$output = '';

	if ( ! $is_empty || isset( $atts['value'] ) ) {
		// theres a field - let go deeper
		if ( isset( $atts['value'] ) ) {

			// check if + or - are present
			if ( '+' === substr( $atts['value'], 0, 1 ) ) {
				// is greater
				$atts['value'] = (float) substr( $atts['value'], 1 ) + 1;

				if ( (float) $field_data > $atts['value'] ) {
					// is greater - set it the same to allow
					$atts['value'] = $field_data;
				}
			} elseif ( substr( $atts['value'], 0, 1 ) === '-' ) {
				// is smaller
				$atts['value'] = (float) substr( $atts['value'], 1 ) - 1;

				if ( (float) $field_data < $atts['value'] ) {
					// is greater - set it the same to allow
					$atts['value'] = $field_data;
				}
			}

			if ( (string) $field_data === (string) $atts['value'] ) {
				// IF statement true, use [IF] content as template
				$template = $pod->do_magic_tags( $code[0] );
			} else {
				// No 'field' value (or value false), switch to [else] content
				if ( isset( $code[1] ) ) {
					// There is an [ELSE] tag
					$template = $pod->do_magic_tags( $code[1] );
				} else {
					// Value did not match (and no [ELSE]), nothing should be displayed
					$template = '';
				}
			}
		} else {
			// Field exists and is not empty, use [IF] content
			$template = $pod->do_magic_tags( $code[0] );
		}//end if
	} elseif ( isset( $code[1] ) ) {
		// No value or field is empty and there is an [ELSE] tag.  Use [ELSE]
		$template = $pod->do_magic_tags( $code[1] );
	} else {
		$template = '';
	}//end if

	return do_shortcode( $template );
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

	$out        = null;
	$pod        = pods( $atts['pod'], $atts['id'] );
	$field_name = $atts['field'];

	$entries = $pod->field( $field_name );
	if ( ! empty( $entries ) ) {
		$entries = (array) $entries;

		$field = $pod->fields[ $field_name ];
		// Object types that could be Pods
		$object_types = array( 'post_type', 'pod' );

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
		if ( in_array( $field['pick_object'], $object_types, true ) || 'taxonomy' == $field['type'] ) {
			// Match any Pod object or taxonomy
			foreach ( $entries as $key => $entry ) {
				$subpod = pods( $field['pick_val'] );

				$subatts = array(
					'id'  => $entry[ $subpod->api->pod_data['field_id'] ],
					'pod' => $field['pick_val'],
				);

				$template = frontier_decode_template( $content, array_merge( $atts, $subatts ) );
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
		} elseif ( 'file' == $field['type'] && 'attachment' == $field['options']['file_uploader'] && 'multi' == $field['options']['file_format_type'] ) {
			$template = frontier_decode_template( $content, $atts );
			foreach ( $entries as $key => $entry ) {
				$content = str_replace( '{_index}', $key, $template );
				$content = str_replace( '{@_img', '{@image_attachment.' . $entry['ID'], $content );
				$content = str_replace( '{@_src', '{@image_attachment_url.' . $entry['ID'], $content );
				$content = str_replace( '{@' . $field_name . '}', '{@image_attachment.' . $entry['ID'] . '}', $content );
				$content = frontier_pseudo_magic_tags( $content, $entry, $pod, true );

				$out .= pods_do_shortcode( $pod->do_magic_tags( $content ), frontier_get_shortcodes() );
			}
		} elseif ( isset( $field['table_info'], $field['table_info']['pod'] ) ) {
			// Relationship to something that is extended by Pods
			$entries = $pod->field( array( 'name' => $field_name, 'output' => 'pod' ) );
			foreach ( $entries as $key => $entry ) {
				$subatts = array(
					'id' => $entry->id,
					'pod' => $entry->pod,
				);

				$template = frontier_decode_template( $content, array_merge( $atts, $subatts ) );
				$template = str_replace( '{_index}', $key, $template );
				$template = str_replace( '{@' . $field_name . '.', '{@', $template );

				$out .= pods_do_shortcode( $entry->do_magic_tags( $template ), frontier_get_shortcodes() );
			}
		} else {
			// Relationship to something other than a Pod (ie: user)
			foreach ( $entries as $key => $entry ) {
				$template = frontier_decode_template( $content, $atts );
				$template = str_replace( '{_index}', $key, $template );
				if ( ! is_array( $entry ) ) {
					$entry = array(
						'_key'   => $key,
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
 *
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
						'fields' => $this->fields,
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
 * @param array attributes from template
 * @param string template to be processed
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
		preg_match_all( '/(\[' . $command . '(.*?)]|\[\/' . $command . '\])/m', $code, $matches );
		if ( ! empty( $matches[0] ) ) {
			// holder for found blocks.
			$blocks     = array();
			$indexCount = 0;
			foreach ( $matches[0] as $key => $tag ) {
				if ( false === strpos( $tag, '[/' ) ) {
					// open tag
					$field = null;
					$value = null;
					$ID    = '{@EntryID}';
					$atts  = ' pod="@pod" id="' . $ID . '"';
					if ( ! empty( $matches[2][ $key ] ) ) {
						// get atts if any
						// $atts = shortcode_parse_atts(str_replace('.', '____', $matches[2][$key]));
						$atts    = array();
						$pattern = '/(\w.+)\s*=\s*"([^"]*)"(?:\s|$)/';
						$text    = preg_replace( "/[\x{00a0}\x{200b}]+/u", ' ', $matches[2][ $key ] );
						if ( preg_match_all( $pattern, $text, $match, PREG_SET_ORDER ) ) {
							$field = $match[0][1];
							$value = $match[0][2];
						} else {
							$field = trim( $matches[2][ $key ] );
						}
						if ( false !== strpos( $field, '.' ) ) {
							$path  = explode( '.', $field );
							$field = array_pop( $path );
							$ID    = '{@' . implode( '.', $path ) . '.' . $pod->api->pod_data['field_id'] . '}';
						}
						$atts = ' id="' . $ID . '" pod="@pod" field="' . $field . '"';
						if ( ! empty( $value ) ) {
							$atts .= ' value="' . $value . '"';
						}
					}//end if

					$newtag              = $shortcode . '__' . $key;
					$tags[ $indexCount ] = $newtag;
					$aliases[]           = $newtag;
					$code                = preg_replace( '/(' . preg_quote( $tag ) . ')/m', '[' . $newtag . $atts . ' index="{_index}"]', $code, 1 );
					$indexCount ++;
				} else {
					// close tag
					$indexCount --;
					$newclose = $tags[ $indexCount ];
					$code     = preg_replace( '/(' . preg_quote( $tag, '/' ) . ')/m', '[/' . $newclose . ']', $code, 1 );

				}//end if
			}//end foreach
			if ( $command == 'if' ) {
				// dump($pod);
			}
		}//end if
	}//end foreach
	// get new aliased shotcodes
	if ( ! empty( $aliases ) ) {
		$code = frontier_backtrack_template( $code, $aliases );
	}
	$code = str_replace( '@pod', $pod->pod, $code );
	$code = str_replace( '@EntryID', '@' . $pod->api->pod_data['field_id'], $code );

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
			if ( ! empty( $atts ) ) {
				if ( ! empty( $atts['field'] ) && false !== strpos( $atts['field'], '.' ) ) {
					$content = str_replace( $atts['field'] . '.', '', $content );
				}
				preg_match_all( '/' . $regex . '/s', $content, $subused );
				if ( ! empty( $subused[2] ) ) {
					$content = frontier_backtrack_template( $content, $aliases );
				}
				$codecontent = '[' . $shortcodes[0] . ' ' . trim( $used[3][ $key ] ) . ' seq="' . $shortcodes[1] . '"]' . base64_encode( $content ) . '[/' . $shortcodes[0] . ']';
			} else {
				$codecontent = '[' . $shortcodes[0] . ' seq="' . $shortcodes[1] . '"]' . base64_encode( $content ) . '[/' . $shortcodes[0] . ']';
			}
			$code = str_replace( $used[0][ $key ], $codecontent, $code );
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
