<?php
/**
 * Utility function for processesing fromtier based templates
 * @package Pods_Frontier_Template_Editor\view_template
 */

// add filters
add_filter( "pods_templates_post_template", "frontier_end_template", 25, 4 );
add_filter( "pods_templates_do_template", "frontier_do_shortcode", 25, 1 );

// template shortcode handlers
add_shortcode( "pod_sub_template", "frontier_do_subtemplate" );
add_shortcode( "pod_once_template", "frontier_template_once_blocks" );
add_shortcode( "pod_after_template", "frontier_template_blocks" );
add_shortcode( "pod_before_template", "frontier_template_blocks" );
add_shortcode( "pod_if_field", "frontier_if_block" );

/**
 * @param $content
 *
 * @return string
 * @since 2.4.3
 */
function frontier_do_shortcode( $content ) {

	$content = pods_do_shortcode( $content, array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );

	return $content;

}

/**
 * decodes a like nested shortcode based template
 *
 *
 * @param string encoded template to be decoded
 * @param array attributed provided from parent
 *
 * @return string
 * @since 2.4
 */
function frontier_decode_template( $code, $atts ) {

	$code = base64_decode( $code );
	$pod = pods( $atts[ 'pod' ] );

	if ( isset( $atts[ 'pod' ] ) ) {
		$code = str_replace( '@pod', $atts[ 'pod' ], $code );
	}
	if ( isset( $atts[ 'id' ] ) ) {
		$code = str_replace( '{@EntryID}', $atts[ 'id' ], $code );
	}
	if ( isset( $atts[ 'index' ] ) ) {
		$code = str_replace( '{_index}', $atts[ 'index' ], $code );
	}

	return $code;
}

/**
 * processes if condition within a template
 *
 *
 * @param array attributes from template
 * @param string encoded template to be decoded
 *
 * @return string
 * @since 2.4
 */
function frontier_if_block( $atts, $code ) {

	$pod = pods( $atts[ 'pod' ], $atts[ 'id' ] );
	$code = explode( '[else]', frontier_decode_template( $code, $atts ) );

	$template = pods_do_shortcode( $pod->do_magic_tags( $code[ 0 ] ), array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );

	// sysvals
	$system_values = array(
		'_index',
	);

	// field data
	$field_data = null;

	if ( in_array( $atts[ 'field' ], $system_values) ) {
		switch ( $atts[ 'field' ] ){
			case '_index':
				$field_data = $atts['index'];
				break;
		}
	}
	else{

		$field_data = $pod->field( $atts[ 'field' ] );

	}

	if ( ! empty( $field_data ) || isset( $atts[ 'value' ] ) ) {
		// theres a field - let go deeper
		if ( isset( $atts[ 'value' ] ) ) {

			// check if + or - are present
			if( substr( $atts[ 'value' ], 0, 1) === '+' ){
				// is greater
				$atts[ 'value' ] = (float) substr( $atts[ 'value' ], 1) + 1;
				if(  (float) $field_data > $atts[ 'value' ] ){
					// is greater - set it the same to allow
					$atts[ 'value' ] = $field_data;
				}

			}elseif( substr( $atts[ 'value' ], 0, 1) === '-' ){
				// is smaller
				$atts[ 'value' ] = (float) substr( $atts[ 'value' ], 1) - 1;
				if( (float) $field_data <  $atts[ 'value' ] ){
					// is greater - set it the same to allow
					$atts[ 'value' ] = $field_data;
				}

			}

			if ( $field_data == $atts[ 'value' ] ) {
				return pods_do_shortcode( $template, array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );
			} else {
				if ( isset( $code[ 1 ] ) ) {
					$template = pods_do_shortcode( $pod->do_magic_tags( $code[ 1 ] ), array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );

					return pods_do_shortcode( $template, array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );
				} else {
					// Value did not match, nothing should be displayed
					return '';
				}
			}
		}

		return pods_do_shortcode( $template, array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );
	}
	else {
		if ( isset( $code[ 1 ] ) ) {
			$template = pods_do_shortcode( $pod->do_magic_tags( $code[ 1 ] ), array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );

			return $template;
		}
	}

}

/**
 * processes before and after template blocks
 *
 *
 * @param array attributes from template
 * @param string encoded template to be decoded
 * @param string shortcode slug used to process
 *
 * @return null
 * @since 2.4
 */
function frontier_template_blocks( $atts, $code, $slug ) {

	global $template_post_blocks;
	if ( !isset( $template_post_blocks ) ) {
		$template_post_blocks = array(
			'before' => null,
			'after' => null,
		);
	}
	if ( $slug === 'pod_before_template' ) {
		if ( !isset( $template_post_blocks[ 'before' ][ $atts[ 'pod' ] ] ) ) {
			$template_post_blocks[ 'before' ][ $atts[ 'pod' ] ] = pods_do_shortcode( frontier_decode_template( $code, $atts ), array(
				'if',
				'else'
			) );
		}

	}
	elseif ( $slug === 'pod_after_template' ) {
		if ( !isset( $template_post_blocks[ 'after' ][ $atts[ 'pod' ] ] ) ) {
			$template_post_blocks[ 'after' ][ $atts[ 'pod' ] ] = pods_do_shortcode( frontier_decode_template( $code, $atts ), array(
				'if',
				'else'
			) );
		}
	}

	return null;
}

/**
 * processes once blocks
 *
 *
 * @param array attributes from template
 * @param string encoded template to be decoded
 *
 * @return string template code
 * @since 2.4
 */
function frontier_template_once_blocks( $atts, $code ) {

	global $frontier_once_hashes;

	if ( !isset( $frontier_once_hashes ) ) {
		$frontier_once_hashes = array();
	}

	$blockhash = md5( $code . $atts[ 'id' ] );
	if ( in_array( $blockhash, $frontier_once_hashes ) ) {
		return '';
	}
	$frontier_once_hashes[ ] = $blockhash;

	return pods_do_shortcode( frontier_decode_template( $code, $atts ), array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );
}

/**
 * processes template code within an each command from the base template
 *
 *
 * @param array attributes from template
 * @param string template to be processed
 *
 * @return null
 * @since 2.4
 */
function frontier_do_subtemplate( $atts, $content ) {

	$out = null;
	$pod = pods( $atts[ 'pod' ], $atts[ 'id' ] );

	$params = array(
		'name' => $atts[ 'field' ],
	);

	$entries = $pod->field( $atts[ 'field' ] );
	if ( ! empty( $entries ) ) {

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
		if ( ! empty( $pod->fields[ $atts[ 'field' ] ][ 'table_info' ] ) || 'taxonomy' == $pod->fields[ $atts[ 'field' ] ][ 'type' ] ) {
			foreach ( $entries as $key => $entry ) {
				$subpod = pods( $pod->fields[ $atts[ 'field' ] ][ 'pick_val' ] );

				$subatts = array(
					'id'  => $entry[ $subpod->api->pod_data[ 'field_id' ] ],
					'pod' => $pod->fields[ $atts[ 'field' ] ][ 'pick_val' ]
				);

				$template = frontier_decode_template( $content, array_merge( $atts, $subatts ) );
				$template = str_replace( '{_index}', $key, $template );
				$template = str_replace( '{@' . $atts[ 'field' ] . '.', '{@', $template );

				// Kludge to work with taxonomies, pending a better solution: see issue #3018
				$target_id = null;
				if ( isset( $entry[ 'ID' ] ) ) {
					$target_id = $entry[ 'ID' ];
				} elseif ( isset( $entry[ 'term_id' ] ) ) {
					$target_id = $entry[ 'term_id' ];
				}

				$out .= pods_shortcode( array(
					'name'  => $pod->fields[ $atts[ 'field' ] ][ 'pick_val' ],
					'slug'  => $target_id,
					'index' => $key
				), $template );

			}
		} elseif ( 'file' == $pod->fields[ $atts[ 'field' ] ][ 'type' ] && 'attachment' == $pod->fields[ $atts[ 'field' ] ][ 'options' ][ 'file_uploader' ] && 'multi' == $pod->fields[ $atts[ 'field' ] ][ 'options' ][ 'file_format_type' ] ) {
			$template = frontier_decode_template( $content, $atts );
			foreach ( $entries as $key => $entry ) {
				$content = str_replace( '{_index}', $key, $template );
				$content = str_replace( '{@_img', '{@image_attachment.' . $entry[ 'ID' ], $content );
				$content = str_replace( '{@_src', '{@image_attachment_url.' . $entry[ 'ID' ], $content );
				$content = str_replace( '{@' . $atts[ 'field' ] . '}', '{@image_attachment.' . $entry[ 'ID' ] . '}', $content );

				$out .= pods_do_shortcode( $pod->do_magic_tags( $content ), array(
					'each',
					'pod_sub_template',
					'once',
					'pod_once_template',
					'before',
					'pod_before_template',
					'after',
					'pod_after_template',
					'if',
					'pod_if_field'
				) );
			}

		}
	}

	return pods_do_shortcode( $out, array(
		'each',
		'pod_sub_template',
		'once',
		'pod_once_template',
		'before',
		'pod_before_template',
		'after',
		'pod_after_template',
		'if',
		'pod_if_field'
	) );
}

/**
 * processes template code within an each command from the base template
 *
 *
 * @param array attributes from template
 * @param string template to be processed
 *
 * @return null
 * @since 2.4
 */
function frontier_prefilter_template( $code, $template, $pod ) {

	global $frontier_once_tags;

	$commands = array(
		'each' => 'pod_sub_template',
		'once' => 'pod_once_template',
		'before' => 'pod_before_template',
		'after' => 'pod_after_template',
		'if' => 'pod_if_field',
	);

	$commands = array_merge( $commands, get_option( 'pods_frontier_extra_commands', array()  ) );

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
		preg_match_all( "/(\[" . $command . "(.*?)]|\[\/" . $command . "\])/m", $code, $matches );
		if ( !empty( $matches[ 0 ] ) ) {
			// holder for found blocks.
			$blocks = array();
			$indexCount = 0;
			foreach ( $matches[ 0 ] as $key => $tag ) {
				if ( false === strpos( $tag, '[/' ) ) {
					// open tag
					$field = null;
					$value = null;
					$ID = '{@EntryID}';
					$atts = ' pod="@pod" id="' . $ID . '"';
					if ( !empty( $matches[ 2 ][ $key ] ) ) {
						// get atts if any
						//$atts = shortcode_parse_atts(str_replace('.', '____', $matches[2][$key]));
						$atts = array();
						$pattern = '/(\w.+)\s*=\s*"([^"]*)"(?:\s|$)/';
						$text = preg_replace( "/[\x{00a0}\x{200b}]+/u", " ", $matches[ 2 ][ $key ] );
						if ( preg_match_all( $pattern, $text, $match, PREG_SET_ORDER ) ) {
							$field = $match[ 0 ][ 1 ];
							$value = $match[ 0 ][ 2 ];
						}
						else {
							$field = trim( $matches[ 2 ][ $key ] );
						}
						if ( false !== strpos( $field, '.' ) ) {
							$path = explode( '.', $field );
							$field = array_pop( $path );
							$ID = '{@' . implode( '.', $path ) . '.' . $pod->api->pod_data[ 'field_id' ] . '}';
						}
						$atts = ' id="' . $ID . '" pod="@pod" field="' . $field . '"';
						if ( !empty( $value ) ) {
							$atts .= ' value="' . $value . '"';
						}
					}

					$newtag = $shortcode . '__' . $key;
					$tags[ $indexCount ] = $newtag;
					$aliases[ ] = $newtag;
					$code = preg_replace( "/(" . preg_quote( $tag ) . ")/m", "[" . $newtag . $atts . " index=\"{_index}\"]", $code, 1 );
					$indexCount++;
				}
				else {
					// close tag
					$indexCount--;
					$newclose = $tags[ $indexCount ];
					$code = preg_replace( "/(" . preg_quote( $tag, '/' ) . ")/m", "[/" . $newclose . "]", $code, 1 );

				}
			}
			if ( $command == 'if' ) {
				//dump($pod);
			}
		}
	}
	// get new aliased shotcodes

	if ( !empty( $aliases ) ) {
		$code = frontier_backtrack_template( $code, $aliases );
	}
	$code = str_replace( '@pod', $pod->pod, $code );
	$code = str_replace( '@EntryID', '@' . $pod->api->pod_data[ 'field_id' ], $code );

	return $code;
}

function frontier_backtrack_template( $code, $aliases ) {

	$regex = frontier_get_regex( $aliases );
	preg_match_all( '/' . $regex . '/s', $code, $used );

	if ( !empty( $used[ 2 ] ) ) {
		foreach ( $used[ 2 ] as $key => $alias ) {
			$shortcodes = explode( '__', $alias );
			$content = $used[ 5 ][ $key ];
			$atts = shortcode_parse_atts( $used[ 3 ][ $key ] );
			if ( !empty( $atts ) ) {
				if ( !empty( $atts[ 'field' ] ) ) {
					$content = str_replace( $atts[ 'field' ] . '.', '', $content );
				}
				preg_match_all( '/' . $regex . '/s', $content, $subused );
				if ( !empty( $subused[ 2 ] ) ) {
					$content = frontier_backtrack_template( $content, $aliases );
				}
				$codecontent = "[" . $shortcodes[ 0 ] . " " . trim( $used[ 3 ][ $key ] ) . " seq=\"" . $shortcodes[ 1 ] . "\"]" . base64_encode( $content ) . "[/" . $shortcodes[ 0 ] . "]";
			}
			else {
				$codecontent = "[" . $shortcodes[ 0 ] . " seq=\"" . $shortcodes[ 1 ] . "\"]" . base64_encode( $content ) . "[/" . $shortcodes[ 0 ] . "]";
			}
			$code = str_replace( $used[ 0 ][ $key ], $codecontent, $code );
		}
	}

	return $code;
}

function frontier_get_regex( $codes ) {

	// A custom version of the shortcode regex as to only use podsfrontier codes.
	// this makes it easier to cycle through and get the used codes for inclusion
	$validcodes = join( '|', array_map( 'preg_quote', $codes ) );

	return '\\[' // Opening bracket
	. '(\\[?)' // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
	. "($validcodes)" // 2: selected codes only
	. '\\b' // Word boundary
	. '(' // 3: Unroll the loop: Inside the opening shortcode tag
	. '[^\\]\\/]*' // Not a closing bracket or forward slash
	. '(?:' . '\\/(?!\\])' // A forward slash not followed by a closing bracket
	. '[^\\]\\/]*' // Not a closing bracket or forward slash
	. ')*?' . ')' . '(?:' . '(\\/)' // 4: Self closing tag ...
	. '\\]' // ... and closing bracket
	. '|' . '\\]' // Closing bracket
	. '(?:' . '(' // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
	. '[^\\[]*+' // Not an opening bracket
	. '(?:' . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
	. '[^\\[]*+' // Not an opening bracket
	. ')*+' . ')' . '\\[\\/\\2\\]' // Closing shortcode tag
	. ')?' . ')' . '(\\]?)'; // 6: Optional second closing brocket for escaping shortcodes: [[tag]]

}

function frontier_end_template( $code, $base, $template, $pod ) {

	global $template_post_blocks;

	if ( !empty( $template_post_blocks[ 'before' ][ $pod->pod ] ) ) {
		$code = $template_post_blocks[ 'before' ][ $pod->pod ] . $code;

		unset( $template_post_blocks[ 'before' ][ $pod->pod ] );
	}

	if ( !empty( $template_post_blocks[ 'after' ][ $pod->pod ] ) ) {
		$code .= $template_post_blocks[ 'after' ][ $pod->pod ];

		unset( $template_post_blocks[ 'after' ][ $pod->pod ] );
	}

	return pods_do_shortcode( $code, array( 'each', 'pod_sub_template', 'once', 'pod_once_template', 'before', 'pod_before_template', 'after', 'pod_after_template', 'if', 'pod_if_field' ) );
}
