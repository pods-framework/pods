<?php

/**
 * @package Pods
 */
class PodsMigrate {

	/**
	 * @var null|string
	 */
	public $type = 'php';

	/**
	 * @var array
	 */
	public $types = array( 'php', 'json', 'sv', 'xml' );

	/**
	 * @var array
	 */
	public $mimes = array(
		'json' => 'application/json',
		'csv'  => 'text/csv',
		'tsv'  => 'text/tsv',
		'xml'  => 'text/xml',
	);

	/**
	 * @var null|string
	 */
	public $delimiter = ',';

	/**
	 * @var null
	 */
	public $data = array(
		'items'   => array(),
		'columns' => array(),
		'fields'  => array(),
		'single'  => false,
	);

	/**
	 * @var null
	 */
	public $input;

	/**
	 * @var
	 */
	public $parsed;

	/**
	 * @var
	 */
	public $built;

	/**
	 * Migrate Data to and from Pods
	 *
	 * @param string $type      Export Type (php, json, sv, xml)
	 * @param string $delimiter Delimiter for export type 'sv'
	 * @param array  $data      Array of data settings
	 *
	 * @return \PodsMigrate
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	public function __construct( $type = null, $delimiter = null, $data = null ) {

		if ( ! empty( $type ) ) {
			if ( 'csv' === $type ) {
				$type = 'sv';

				if ( null === $delimiter ) {
					$delimiter = ',';
				}
			} elseif ( 'tsv' === $type ) {
				$type = 'sv';

				if ( null === $delimiter ) {
					$delimiter = "\t";
				}
			}

			if ( in_array( $type, $this->types, true ) ) {
				$this->type = $type;
			}
		}

		if ( ! empty( $delimiter ) ) {
			$this->delimiter = $delimiter;
		}

		if ( ! empty( $data ) ) {
			$this->set_data( $data );
		}
	}

	/**
	 * @param $data
	 */
	public function set_data( $data ) {

		$defaults = array(
			'items'   => array(),
			'columns' => array(),
			'fields'  => array(),
		);

		$this->data = array_merge( $defaults, (array) $data );
	}

	/**
	 * Importing / Parsing / Validating Code
	 *
	 * @param array  $data      Array of data
	 * @param string $type      Export Type (php, json, sv, xml)
	 * @param string $delimiter Delimiter for export type 'sv'
	 *
	 * @return bool
	 */
	public function import( $data = null, $type = null, $delimiter = null ) {

		$this->parse( $data, $type, $delimiter );

		return $this->import_pod_items();

	}

	/**
	 * @param array  $data Array of data
	 * @param string $type Export Type (php, json, sv, xml)
	 *
	 * @return bool
	 */
	public function import_pod_items( $data = null, $type = null ) {

		if ( ! empty( $data ) ) {
			$this->input = $data;
		}

		if ( ! empty( $type ) && in_array( $type, $this->types, true ) ) {
			$this->type = $type;
		}

		return false;
	}

	/**
	 * @param array $data       Array of data
	 * @param string $type      Parse Type (php, json, sv, xml)
	 * @param string $delimiter Delimiter for export type 'sv'
	 *
	 * @return null
	 */
	public function parse( $data = null, $type = null, $delimiter = null ) {

		if ( ! empty( $data ) ) {
			$this->input = $data;
		}

		if ( ! empty( $type ) && in_array( $type, $this->types, true ) ) {
			$this->type = $type;
		}

		if ( !empty( $delimiter ) )
			$this->delimiter = $delimiter;

		if ( method_exists( $this, "parse_{$this->type}" ) ) {
			return call_user_func( array( $this, 'parse_' . $this->type ) );
		}

		return $this->parsed;
	}

	/**
	 * @param array $data Array of data
	 *
	 * @return bool
	 */
	public function parse_json( $data = null ) {

		if ( ! empty( $data ) ) {
			$this->input = $data;
		}

		$items = @json_decode( $this->input, true );

		if ( ! is_array( $items ) ) {
			return false;
		}

		// Only export to a basic object if building for a single item.
		if ( ! empty( $this->data['single'] ) ) {
			$data = $items;
		} else {
			$data = array(
				'columns' => array(),
				'items'   => array(),
				'fields'  => array(),
			);

			foreach ( $items as $key => $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}

				foreach ( $item as $column => $value ) {
					if ( ! in_array( $column, $data['columns'], true ) ) {
						$data['columns'][] = $column;
					}
				}

				$data['items'][ $key ] = $item;
			}
		}

		$this->parsed = $data;

		return $this->parsed;
	}

	/**
	 * @param array  $data      Array of data
	 * @param string $delimiter Delimiter for export type 'sv'
	 *
	 * @return bool
	 */
	public function parse_sv( $data = null, $delimiter = null ) {

		if ( ! empty( $data ) ) {
			$this->input = $data;
		}

		if ( ! empty( $delimiter ) ) {
			$this->delimiter = $delimiter;
		}

		$rows = $this->str_getcsv( $this->input, '\n' );

		if ( empty( $rows ) || 2 > count( $rows ) ) {
			return false;
		}

		$data = array(
			'columns' => array(),
			'items'   => array(),
		);

		foreach ( $rows as $key => $row ) {
			if ( 0 === $key ) {
				$data['columns'] = $this->str_getcsv( $row, $this->delimiter );
			} else {
				$row = $this->str_getcsv( $row, $this->delimiter );

				$data['items'][ $key ] = array();

				foreach ( $data['columns'] as $ckey => $column ) {
					$data['items'][ $key ][ $column ] = ( isset( $row[ $ckey ] ) ? $row[ $ckey ] : '' );

					if ( 'NULL' === $data['items'][ $key ][ $column ] ) {
						$data['items'][ $key ][ $column ] = null;
					}
				}
			}
		}

		$this->parsed = $data;

		return $this->parsed;
	}

	/**
	 * Handle str_getcsv for cases where it's not set
	 *
	 * @param        $line
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param string $escape
	 *
	 * @return array|mixed
	 */
	public function str_getcsv( $line, $delimiter = ',', $enclosure = '"', $escape = '\\' ) {

		$line = str_replace( "\r\n", "\n", $line );
		$line = str_replace( "\r", "\n", $line );

		if ( '\n' !== $delimiter && function_exists( 'str_getcsv' ) ) {
			return str_getcsv( $line, $delimiter, $enclosure, $escape );
		}

		$delimiter = str_replace( '/', '\/', $delimiter );
		$enclosure = preg_quote( $enclosure, '/' );

		$split = "/{$delimiter}(?=(?:[^{$enclosure}]*{$enclosure}[^{$enclosure}]*{$enclosure})*(?![^{$enclosure}]*{$enclosure}))/";

		$data = preg_split( $split, trim( $line ), - 1, PREG_SPLIT_NO_EMPTY );

		if ( '\n' !== $delimiter ) {
			$data = preg_replace( "/^{$enclosure}(.*){$enclosure}$/s", '$1', $data );
		}

		return $data;
	}

	/**
	 * @param array $data Array of data
	 *
	 * @return bool
	 */
	public function parse_xml( $data = null ) {

		if ( ! empty( $data ) ) {
			$this->input = $data;
		}

		$xml = new SimpleXMLElement( $this->input );

		if ( ! isset( $xml->items ) ) {
			return false;
		}

		$data = array(
			'columns' => array(),
			'items'   => array(),
		);

		/**
		 * @var $child      SimpleXMLElement
		 * @var $item_child SimpleXMLElement
		 * @var $data_child SimpleXMLElement
		 */

		if ( isset( $xml->columns ) ) {
			foreach ( $xml->columns->children() as $child ) {
				$sub = $child->getName();

				if ( empty( $sub ) || 'column' !== $sub ) {
					continue;
				}

				if ( isset( $child->name ) ) {
					if ( is_array( $child->name ) ) {
						$column = $child->name[0];
					} else {
						$column = $child->name;
					}

					$data['columns'][] = $column;
				}
			}
		}

		foreach ( $xml->items->children() as $child ) {
			$sub = $child->getName();

			if ( empty( $sub ) || 'item' !== $sub ) {
				continue;
			}

			$item = array();

			$attributes = $child->attributes();

			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $column => $value ) {
					if ( ! in_array( $column, $data['columns'], true ) ) {
						$data['columns'][] = $column;
					}

					$item[ $column ] = $value;
				}
			}

			$item_child = $child->children();

			if ( ! empty( $item_child ) ) {
				foreach ( $item_child->children() as $data_child ) {
					$column = $data_child->getName();

					if ( ! in_array( $column, $data['columns'], true ) ) {
						$data['columns'][] = $column;
					}

					$item[ $column ] = $item_child->$column;
				}
			}

			if ( ! empty( $item ) ) {
				$data['items'][] = $item;
			}
		}//end foreach

		$this->parsed = $data;

		return $this->parsed;
	}

	/**
	 * @param array $data Array of data
	 *
	 * @return mixed
	 *
	 * @todo For much much later
	 */
	public function parse_sql( $data = null ) {

		if ( ! empty( $data ) ) {
			$this->input = $data;
		}

		$this->parsed = $data;

		return $this->parsed;
	}

	/**
	 * Exporting / Building Code
	 *
	 * @param array  $data      Array of data
	 * @param string $type      Export Type (php, json, sv, xml)
	 * @param string $delimiter Delimiter for export type 'sv'
	 *
	 * @return mixed
	 */
	public function export( $data = null, $type = null, $delimiter = null ) {

		if ( ! empty( $data ) ) {
			$this->set_data( $data );
		}

		if ( ! empty( $type ) && in_array( $type, $this->types, true ) ) {
			$this->type = $type;
		}

		if ( ! empty( $delimiter ) ) {
			$this->delimiter = $delimiter;
		}

		if ( method_exists( $this, "build_{$this->type}" ) ) {
			call_user_func( array( $this, 'build_' . $this->type ) );
		}

		return $this->built;
	}

	/**
	 * @param array $data Array of data
	 */
	public function export_pod_items( $data = null ) {

		if ( ! empty( $data ) ) {
			$this->set_data( $data );
		}
	}

	/**
	 * @param array  $data Array of data
	 * @param string $type Export Type (php, json, sv, xml)
	 *
	 * @return null
	 */
	public function build( $data = null, $type = null ) {

		if ( ! empty( $data ) ) {
			$this->set_data( $data );
		}

		if ( ! empty( $type ) && in_array( $type, $this->types, true ) ) {
			$this->type = $type;
		}

		if ( method_exists( $this, "build_{$this->type}" ) ) {
			call_user_func( array( $this, 'build_' . $this->type ) );
		}

		return $this->data;
	}

	/**
	 * @param array $data Array of data
	 *
	 * @return bool
	 */
	public function build_json( $data = null ) {

		if ( ! empty( $data ) ) {
			$this->set_data( $data );
		}

		if ( empty( $this->data ) || ! is_array( $this->data ) ) {
			return false;
		}

		// Only export to a basic object if building for a single item.
		if ( ! empty( $this->data['single'] ) ) {
			$data = $this->data['items'];
		} else {
			$data = array(
				'items' => array(
					'count' => count( $this->data['items'] ),
					'item'  => array(),
				),
			);

			foreach ( $this->data['items'] as $item ) {
				$row = array();

				foreach ( $this->data['columns'] as $column => $label ) {
					if ( is_numeric( $column ) && ( ( is_object( $item ) && ! isset( $item->$column ) ) || ( is_array( $item ) && ! isset( $item[ $column ] ) ) ) ) {
						$column = $label;
					}

					$value = '';

					if ( is_object( $item ) ) {
						if ( ! isset( $item->$column ) ) {
							$item->$column = '';
						}

						$value = $item->$column;
					} elseif ( is_array( $item ) ) {
						if ( ! isset( $item[ $column ] ) ) {
							$item[ $column ] = '';
						}

						$value = $item[ $column ];
					}

					$row[ $column ] = $value;
				}//end foreach

				$data['items']['item'][] = $row;
			}//end foreach
		}

		$this->built = @json_encode( $data );

		return $this->built;
	}

	/**
	 * @param array  $data      Array of data
	 * @param string $delimiter Delimiter for export type 'sv'
	 *
	 * @return bool
	 */
	public function build_sv( $data = null, $delimiter = null ) {

		if ( ! empty( $data ) ) {
			$this->set_data( $data );
		}

		if ( ! empty( $delimiter ) ) {
			$this->delimiter = $delimiter;
		}

		if ( empty( $this->data ) || ! is_array( $this->data ) ) {
			return false;
		}

		$head  = '';
		$lines = '';

		foreach ( $this->data['columns'] as $column => $label ) {
			$head .= '"' . $label . '"' . $this->delimiter;
		}

		$head = substr( $head, 0, - 1 );

		foreach ( $this->data['items'] as $item ) {
			$line = '';

			foreach ( $this->data['columns'] as $column => $label ) {
				if ( is_numeric( $column ) && ( ( is_object( $item ) && ! isset( $item->$column ) ) || ( is_array( $item ) && ! isset( $item[ $column ] ) ) ) ) {
					$column = $label;
				}

				$value = '';

				if ( is_object( $item ) ) {
					if ( ! isset( $item->$column ) ) {
						$item->$column = '';
					}

					$value = $item->$column;
				} elseif ( is_array( $item ) ) {
					if ( ! isset( $item[ $column ] ) ) {
						$item[ $column ] = '';
					}

					$value = $item[ $column ];
				}

				if ( is_array( $value ) || is_object( $value ) ) {
					$value = pods_serial_comma(
						$value, array(
							'field'  => $column,
							'fields' => pods_var_raw( $column, $this->data['fields'] ),
							'and'    => '',
						)
					);
				}

				$value = str_replace( array( '"', "\r\n", "\r", "\n" ), array( '\\"', "\n", "\n", '\n' ), $value );

				$line .= '"' . $value . '"' . $this->delimiter;
			}//end foreach

			$lines .= substr( $line, 0, - 1 ) . "\n";
		}//end foreach

		if ( ! empty( $lines ) ) {
			$lines = "\n" . substr( $lines, 0, - 1 );
		}

		$this->built = $head . $lines;

		return $this->built;
	}

	/**
	 * @param array $data Array of data
	 *
	 * @return bool
	 */
	public function build_xml( $data = null ) {

		if ( ! empty( $data ) ) {
			$this->set_data( $data );
		}

		if ( empty( $this->data ) || ! is_array( $this->data ) ) {
			return false;
		}

		$head  = '<' . '?' . 'xml version="1.0" encoding="utf-8" ' . '?' . '>' . "\r\n<items count=\"" . count( $this->data['items'] ) . "\">\r\n";
		$lines = '';

		foreach ( $this->data['items'] as $item ) {
			$line = "\t<item>\r\n";

			foreach ( $this->data['columns'] as $column => $label ) {
				if ( is_numeric( $column ) && ( ( is_object( $item ) && ! isset( $item->$column ) ) || ( is_array( $item ) && ! isset( $item[ $column ] ) ) ) ) {
					$column = $label;
				}

				$line .= $this->build_xml_level( $item, $column );
			}

			$line  .= "\t</item>\r\n";
			$lines .= $line;
		}

		$foot = '</items>';

		$this->built = $head . $lines . $foot;

		return $this->built;
	}

	/**
	 * @param array|object $item
	 * @param string       $column
	 * @param int          $level
	 * @param string       $column_name
	 *
	 * @return string
	 */
	public function build_xml_level( $item, $column, $level = 2, $column_name = '' ) {

		$column = pods_clean_name( $column, false, false );

		$line = '';

		$value = '';

		if ( is_object( $item ) ) {
			if ( ! isset( $item->$column ) ) {
				$item->$column = '';
			}

			$value = $item->$column;
		} elseif ( is_array( $item ) ) {
			if ( ! isset( $item[ $column ] ) ) {
				$item[ $column ] = '';
			}

			$value = $item[ $column ];
		}

		if ( ! empty( $column_name ) ) {
			$column = $column_name;
		}

		$tabs = str_repeat( "\t", $level );

		$line .= $tabs . "<{$column}>";

		if ( is_array( $value ) || is_object( $value ) ) {
			if ( is_object( $value ) ) {
				$value = get_object_vars( $value );
			}

			foreach ( $value as $k => $v ) {
				if ( is_int( $k ) ) {
					$line .= $this->build_xml_level( $value, $k, $level + 1, 'value' );
				} else {
					$line .= $this->build_xml_level( $value, $k, $level + 1 );
				}
			}
		} elseif ( false !== strpos( $value, '<' ) ) {
			$value = str_replace( array( '<![CDATA[', ']]>' ), array( '&lt;![CDATA[', ']]&gt;' ), $value );

			$line .= '<![CDATA[' . $value . ']]>';
		} else {
			$line .= str_replace( '&', '&amp;', $value );
		}

		$line .= "</{$column}>\r\n";

		return $line;
	}

	/**
	 * @param array $data Array of data
	 *
	 * @return mixed
	 */
	public function build_sql( $data = null ) {

		if ( ! empty( $data ) ) {
			$this->set_data( $data );
		}

		$this->built = $data;

		return $this->built;
	}

	/**
	 * Save export to a file.
	 *
	 * @param array $params Additional options for saving.
	 *
	 * @return string The URL of the saved file, a path if not attached.
	 */
	public function save( $params = array() ) {

		$defaults = array(
			'file'   => null,
			'path'   => null,
			'attach' => false,
		);

		$params = array_merge( $defaults, $params );

		$extension = 'txt';

		if ( ! empty( $params['file'] ) ) {
			$export_file = $params['file'];

			if ( false !== strpos( $export_file, '.' ) ) {
				$extension = explode( '.', $export_file );
				$extension = end( $extension );
			}
		} else {
			if ( 'sv' === $this->type ) {
				if ( ',' === $this->delimiter ) {
					$extension = 'csv';
				} elseif ( "\t" === $this->delimiter ) {
					$extension = 'tsv';
				}
			} else {
				$extension = $this->type;
			}

			$export_file = sprintf(
				'pods_export_%s.%s',
				wp_create_nonce( date_i18n( 'm-d-Y_h-i-sa' ) ),
				$extension
			);
		}

		if ( ! empty( $params['path'] ) ) {
			$new_file = sprintf(
				'%s/%s',
				untrailingslashit( $params['path'] ),
				$export_file
			);

			$filename = $export_file;
		} else {
			$uploads = wp_upload_dir( current_time( 'mysql' ) );

			if ( ! $uploads || false !== $uploads['error'] ) {
				return pods_error( __( 'There was an issue saving the export file in your uploads folder.', 'pods' ), true );
			}

			// Generate unique file name
			$filename = wp_unique_filename( $uploads['path'], $export_file );

			// move the file to the uploads dir
			$new_file = $uploads['path'] . '/' . $filename;
		}

		file_put_contents( $new_file, $this->built );

		// Set correct file permissions
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@chmod( $new_file, $perms );

		// Only attach if we want to and don't have a custom path.
		if ( $params['attach'] && empty( $params['path'] ) ) {
			// Get the file type
			$wp_filetype = wp_check_filetype( $filename, $this->mimes );

			// construct the attachment array
			$attachment = array(
				'post_mime_type' => 'text/' . $extension,
				'guid'           => $uploads['url'] . '/' . $filename,
				'post_parent'    => null,
				'post_title'     => 'Pods Export (' . $export_file . ')',
				'post_content'   => '',
				'post_status'    => 'private'
			);

			if ( $wp_filetype['type'] ) {
				$attachment['post_mime_type'] = $wp_filetype['type'];
			}

			// insert attachment
			$attachment_id = wp_insert_attachment( $attachment, $new_file );

			// error!
			if ( is_wp_error( $attachment_id ) ) {
				return pods_error( __( 'There was an issue saving the export file in your uploads folder.', 'pods' ), true );
			}

			$url = $attachment['guid'];
		} else {
			$url = $new_file;
		}

		return $url;

	}

	/*
	The real enchilada!

	EXAMPLES
	//// minimal import (if your fields match on both your pods and tables)
	$import = array('my_pod' => array('table' => 'my_table')); // if your table name doesn't match the pod name
	$import = array('my_pod'); // if your table name matches your pod name

	//// advanced import
	$import = array();
	$import['my_pod'] = array();
	$import['my_pod']['fields']['this_field'] = 'field_name_in_table'; // if the field name doesn't match on table and pod
	$import['my_pod']['fields'][] = 'that_field'; // if the field name matches on table and pod
	$import['my_pod']['fields']['this_other_field'] = array('filter' => 'wpautop'); // if you want the value to be different than is provided, set a filter function to use [filter uses = filter_name($value,$rowdata)]
	$import['my_pod']['fields']['another_field'] = array('field' => 'the_real_field_in_table','filter' => 'my_custom_function'); // if you want the value to be filtered, and the field name doesn't match on the table and pod
	$import[] = 'my_other_pod'; // if your table name matches your pod name
	$import['another_pod'] = array('update_on' => 'main_field'); // you can update a pod item if the value of this field is the same on both tables
	$import['another_pod'] = array('reset' => true); // you can choose to reset all data in a pod before importing

	//// run import
	pods_import_it($import);
	*/
	/**
	 * @param      $import
	 * @param bool   $output
	 */
	public function heres_the_beef( $import, $output = true ) {

		global $wpdb;

		$api = pods_api();

		for ( $i = 0; $i < 40000; $i ++ ) {
			echo "  \t";
			// extra spaces
		}

		$default_data = array(
			'pod'            => null,
			'table'          => null,
			'reset'          => null,
			'update_on'      => null,
			'where'          => null,
			'fields'         => array(),
			'row_filter'     => null,
			'pre_save'       => null,
			'post_save'      => null,
			'sql'            => null,
			'sort'           => null,
			'limit'          => null,
			'page'           => null,
			'output'         => null,
			'page_var'       => 'ipg',
			'bypass_helpers' => false,
		);

		$default_field_data = array(
			'field'  => null,
			'filter' => null,
		);

		if ( ! is_array( $import ) ) {
			$import = array( $import );
		} elseif ( empty( $import ) ) {
			die( '<h1 style="color:red;font-weight:bold;">ERROR: No imports configured</h1>' );
		}

		$import_counter = 0;
		$total_imports  = count( $import );
		$paginated      = false;
		$avg_time       = - 1;
		$total_time     = 0;
		$counter        = 0;
		$avg_unit       = 100;
		$avg_counter    = 0;

		foreach ( $import as $datatype => $data ) {
			$import_counter ++;

			flush();
			@ob_end_flush();
			usleep( 50000 );

			if ( ! is_array( $data ) ) {
				$datatype = $data;
				$data     = array( 'table' => $data );
			}

			if ( isset( $data[0] ) ) {
				$data = array( 'table' => $data[0] );
			}

			$data = array_merge( $default_data, $data );

			if ( null === $data['pod'] ) {
				$data['pod'] = array( 'name' => $datatype );
			}

			if ( false !== $output ) {
				echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - <em>' . $data['pod']['name'] . '</em> - <strong>Loading Pod: ' . $data['pod']['name'] . "</strong>\n";
			}

			if ( 2 > count( $data['pod'] ) ) {
				$data['pod'] = $api->load_pod( array( 'name' => $data['pod']['name'] ) );
			}

			if ( empty( $data['pod']['fields'] ) ) {
				continue;
			}

			if ( null === $data['table'] ) {
				$data['table'] = $data['pod']['name'];
			}

			if ( $data['reset'] === true ) {
				if ( false !== $output ) {
					echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . "</em> - <strong style='color:blue;'>Resetting Pod: " . $data['pod']['name'] . "</strong>\n";
				}

				$api->reset_pod(
					array(
						'id'   => $data['pod']['id'],
						'name' => $data['pod']['name'],
					)
				);
			}

			if ( null === $data['sort'] && null !== $data['update_on'] && isset( $data['fields'][ $data['update_on'] ] ) ) {
				if ( isset( $data['fields'][ $data['update_on'] ]['field'] ) ) {
					$data['sort'] = $data['fields'][ $data['update_on'] ]['field'];
				} else {
					$data['sort'] = $data['update_on'];
				}
			}

			$page = 1;

			if ( false !== $data['page_var'] && isset( $_GET[ $data['page_var'] ] ) ) {
				$page = absval( $_GET[ $data['page_var'] ] );
			}

			if ( null === $data['sql'] ) {
				$data['sql'] = "SELECT * FROM {$data['table']}" . ( null !== $data['where'] ? " WHERE {$data['where']}" : '' ) . ( null !== $data['sort'] ? " ORDER BY {$data['sort']}" : '' ) . ( null !== $data['limit'] ? ' LIMIT ' . ( 1 < $page ? ( ( $page - 1 ) * $data['limit'] ) . ',' : '' ) . "{$data['limit']}" : '' );
			}

			if ( false !== $output ) {
				echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Getting Results: ' . $data['pod']['name'] . "\n";
			}

			if ( false !== $output ) {
				echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Using Query: <small><code>' . $data['sql'] . "</code></small>\n";
			}

			$result = $wpdb->get_results( $data['sql'], ARRAY_A );

			if ( false !== $output ) {
				echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Results Found: ' . count( $result ) . "\n";
			}

			$avg_time     = - 1;
			$total_time   = 0;
			$counter      = 0;
			$avg_unit     = 100;
			$avg_counter  = 0;
			$result_count = count( $result );
			$paginated    = false;

			if ( false !== $data['page_var'] && $result_count === $data['limit'] ) {
				$paginated = "<input type=\"button\" onclick=\"document.location=\'" . pods_ui_var_update( array( $data['page_var'] => $page + 1 ), false, false ) . "\';\" value=\"  Continue Import &raquo;  \" />";
			}

			if ( $result_count < $avg_unit && 5 < $result_count ) {
				$avg_unit = number_format( $result_count / 5, 0, '', '' );
			} elseif ( 2000 < $result_count && 10 < count( $data['pod']['fields'] ) ) {
				$avg_unit = 40;
			}

			$data['count'] = $result_count;
			timer_start();

			if ( false !== $output && 1 === $import_counter ) {
				echo "<div style='width:50%;background-color:navy;padding:10px 10px 30px 10px;color:#FFF;position:absolute;top:10px;left:25%;text-align:center;'><p id='progress_status' align='center'>" . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Running Importer..</p><br /><small>This will automatically update every ' . $avg_unit . " rows</small></div>\n";
			}

			foreach ( $result as $k => $row ) {
				flush();
				@ob_end_flush();
				usleep( 50000 );

				if ( false !== $output ) {
					echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Processing Row #' . ( $k + 1 ) . "\n";
				}

				if ( null !== $data['row_filter'] && function_exists( $data['row_filter'] ) ) {
					if ( false !== $output ) {
						echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Filtering <strong>' . $data['row_filter'] . '</strong> on Row #' . ( $k + 1 ) . "\n";
					}

					$row = $data['row_filter']( $row, $data );
				}

				if ( ! is_array( $row ) ) {
					continue;
				}

				$params = array(
					'datatype'       => $data['pod']['name'],
					'columns'        => array(),
					'bypass_helpers' => $data['bypass_helpers'],
				);

				foreach ( $data['pod']['fields'] as $fk => $field_info ) {
					$field = $field_info['name'];

					if ( ! empty( $data['fields'] ) && ! isset( $data['fields'][ $field ] ) && ! in_array( $field, $data['fields'], true ) ) {
						continue;
					}

					if ( isset( $data['fields'][ $field ] ) ) {
						if ( is_array( $data['fields'][ $field ] ) ) {
							$field_data = $data['fields'][ $field ];
						} else {
							$field_data = array( 'field' => $data['fields'][ $field ] );
						}
					} else {
						$field_data = array();
					}

					if ( ! is_array( $field_data ) ) {
						$field      = $field_data;
						$field_data = array();
					}

					$field_data = array_merge( $default_field_data, $field_data );

					if ( null === $field_data['field'] ) {
						$field_data['field'] = $field;
					}

					$data['fields'][ $field ] = $field_data;
					$value                    = '';

					if ( isset( $row[ $field_data['field'] ] ) ) {
						$value = $row[ $field_data['field'] ];
					}

					if ( null !== $field_data['filter'] ) {
						if ( function_exists( $field_data['filter'] ) ) {
							if ( false !== $output ) {
								echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Filtering <strong>' . $field_data['filter'] . '</strong> on Field: ' . $field . "\n";
							}

							$value = $field_data['filter']( $value, $row, $data );
						} else {
							$value = '';
						}
					}

					if ( 1 > strlen( $value ) && 1 === $field_info['required'] ) {
						die( '<h1 style="color:red;font-weight:bold;">ERROR: Field Required for <strong>' . $field . '</strong></h1>' );
					}

					$params['columns'][ $field ] = $value;

					unset( $value, $field_data, $field_info, $fk );
				}//end foreach

				if ( empty( $params['columns'] ) ) {
					continue;
				}

				$params['columns'] = pods_sanitize( $params['columns'] );

				if ( null !== $data['update_on'] && isset( $params['columns'][ $data['update_on'] ] ) ) {
					if ( false !== $output ) {
						echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . "</em> - Checking for Existing Item\n";
					}

					$check = new Pod( $data['pod']['name'] );
					$check->findRecords(
						array(
							'orderby' => 't.id',
							'limit'   => 1,
							'where'   => "t.{$data['update_on']} = '{$params['columns'][$data['update_on']]}'",
							'search'  => false,
							'page'    => 1,
						)
					);

					if ( 0 < $check->getTotalRows() ) {
						$check->fetchRecord();

						$params['tbl_row_id'] = $check->get_field( 'id' );
						$params['pod_id']     = $check->get_pod_id();

						if ( false !== $output ) {
							echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Found Existing Item w/ ID: ' . $params['tbl_row_id'] . "\n";
						}

						unset( $check );
					}

					if ( ! isset( $params['tbl_row_id'] ) && false !== $output ) {
						echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . "</em> - Existing item not found - Creating New\n";
					}
				}//end if

				if ( null !== $data['pre_save'] && function_exists( $data['pre_save'] ) ) {
					if ( false !== $output ) {
						echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Running Pre Save <strong>' . $data['pre_save'] . '</strong> on ' . $data['pod']['name'] . "\n";
					}

					$params = $data['pre_save']( $params, $row, $data );
				}

				$id = $api->save_pod_item( $params );

				if ( false !== $output ) {
					echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - <strong>Saved Row #' . ( $k + 1 ) . ' w/ ID: ' . $id . "</strong>\n";
				}

				$params['tbl_row_id'] = $id;

				if ( null !== $data['post_save'] && function_exists( $data['post_save'] ) ) {
					if ( false !== $output ) {
						echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - Running Post Save <strong>' . $data['post_save'] . '</strong> on ' . $data['pod']['name'] . "\n";
					}

					$data['post_save']( $params, $row, $data );
				}

				unset( $params, $result[ $k ], $row );

				wp_cache_flush();
				$wpdb->queries = array();

				$avg_counter ++;
				$counter ++;

				if ( $avg_counter === $avg_unit && false !== $output ) {
					$avg_counter         = 0;
					$avg_time            = timer_stop( 0, 10 );
					$total_time         += $avg_time;
					$rows_left           = $result_count - $counter;
					$estimated_time_left = ( ( $total_time / $counter ) * $rows_left ) / 60;
					$percent_complete    = 100 - ( ( $rows_left * 100 ) / $result_count );

					echo "<script type='text/javascript'>document.getElementById('progress_status').innerHTML = '" . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em><br /><strong>' . $percent_complete . '% Complete</strong><br /><strong>Estimated Time Left:</strong> ' . $estimated_time_left . ' minute(s) or ' . ( $estimated_time_left / 60 ) . ' hours(s)<br /><strong>Time Spent:</strong> ' . ( $total_time / 60 ) . ' minute(s)<br /><strong>Rows Done:</strong> ' . ( $result_count - $rows_left ) . '/' . $result_count . '<br /><strong>Rows Left:</strong> ' . $rows_left . "';</script>\n";
					echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . '</em> - <strong>Updated Status:</strong> ' . $percent_complete . "% Complete</strong>\n";
				}
			}//end foreach

			if ( false !== $output ) {
				$avg_counter         = 0;
				$avg_time            = timer_stop( 0, 10 );
				$total_time         += $avg_time;
				$rows_left           = $result_count - $counter;
				$estimated_time_left = ( ( $total_time / $counter ) * $rows_left ) / 60;
				$percent_complete    = 100 - ( ( $rows_left * 100 ) / $result_count );

				echo "<script type='text/javascript'>document.getElementById('progress_status').innerHTML = '" . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . "</em><br /><strong style=\'color:green;\'>100% Complete</strong><br /><br /><strong>Time Spent:</strong> " . ( $total_time / 60 ) . ' minute(s)<br /><strong>Rows Imported:</strong> ' . $result_count . ( false !== $paginated ? '<br /><br />' . $paginated : '' ) . "';</script>\n";
				echo '<br />' . date( 'Y-m-d h:i:sa' ) . ' - <em>' . $data['pod']['name'] . "</em> - <strong style='color:green;'>Done Importing: " . $data['pod']['name'] . "</strong>\n";
			}

			unset( $result, $import[ $datatype ], $datatype, $data );

			wp_cache_flush();
			$wpdb->queries = array();
		}//end foreach

		if ( false !== $output ) {
			$avg_counter = 0;
			$avg_time    = timer_stop( 0, 10 );
			$total_time += $avg_time;
			$rows_left   = $result_count - $counter;

			echo "<script type='text/javascript'>document.getElementById('progress_status').innerHTML = '" . date( 'Y-m-d h:i:sa' ) . " - <strong style=\'color:green;\'>Import Complete</strong><br /><br /><strong>Time Spent:</strong> " . ( $total_time / 60 ) . ' minute(s)<br /><strong>Rows Imported:</strong> ' . $result_count . ( false !== $paginated ? '<br /><br />' . $paginated : '' ) . "';</script>\n";
			echo '<br />' . date( 'Y-m-d h:i:sa' ) . " - <strong style='color:green;'>Import Complete</strong>\n";
		}
	}

	/**
	 * Export data to a file.
	 *
	 * @param string $file   File to export to.
	 * @param array  $data   Data to export.
	 * @param bool   $single Whether this is a single item export.
	 *
	 * @return mixed
	 */
	public static function export_data_to_file( $file, $data, $single = false ) {

		$path = ABSPATH;

		// Detect path if it is set in the file param.
		if ( false !== strpos( $file, '/' ) ) {
			$path = dirname( $file );
			$file = basename( $file );
		}

		$format = 'json';

		// Detect the export format.
		if ( false !== strpos( $file, '.' ) ) {
			$format = explode( '.', $file );
			$format = end( $format );
		}

		$migrate_data = array(
			'items'  => array( $data ),
			'single' => $single,
		);

		$migrate = new self( $format, null, $migrate_data );

		// Handle processing the data into the format needed.
		$migrate->export();

		$save_params = array(
			'path'   => $path,
			'file'   => $file,
			'attach' => true,
		);

		return $migrate->save( $save_params );

	}

	/**
	 * Get data from a file.
	 *
	 * @param string $file   File to get data from.
	 * @param bool   $single Whether this is a single item.
	 *
	 * @return mixed
	 */
	public static function get_data_from_file( $file, $single = false ) {

		$path = ABSPATH;

		// Detect path if it is set in the file param.
		if ( false !== strpos( $file, DIRECTORY_SEPARATOR ) ) {
			$path = dirname( $file );
			$file = basename( $file );
		}

		$format = 'json';

		// Detect the export format.
		if ( false !== strpos( $file, '.' ) ) {
			$format = explode( '.', $file );
			$format = end( $format );
		}

		$migrate_data = array(
			'single' => $single,
		);

		$migrate = new self( $format, null, $migrate_data );

		$raw_data = file_get_contents( $path . DIRECTORY_SEPARATOR . $file );

		// Handle processing the raw data from the format needed.
		$data = $migrate->parse( $raw_data );

		return $data;

	}

}
