<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * A class for outputting a multidimensional array as a straightforward HTML table
 *
 * @todo Remove this in version 5 if it does not see much usage
 */
class Tribe__Simple_Table {

	public $thead;
	public $tbody;

	/*
	 * Arrays containing HTML attributes for the table elements
	 *
	 * Example: $table_attributes = array( 'class' => 'tribe_table', 'border' => '0' );
	 */
	public $table_attributes;
	public $tr_attributes;
	public $th_attributes;
	public $td_attributes;

	/**
	 * Will HTML escape all table cells
	 *
	 * @var bool
	 */
	public $html_escape_td_values = true;

	/**
	 * Tribe__Simple_Table constructor.
	 *
	 * @param array $tbody Multidimension array containing table rows/columns
	 * @param array $thead Single dimension array containing table headings
	 */
	public function __construct( $tbody, $thead = [] ) {
		$this->thead = $thead;
		$this->tbody = $tbody;
	}

	/**
	 * @param bool $vertical Whether heading appears vertically (above) data or horizontally (to the side)
	 *
	 * @return string HTML table
	 */
	public function output_table( $vertical = true ) {

		if ( $vertical ) {
			return $this->output_table_vertical();
		} else {
			return $this->output_table_horizontal();
		}
	}

	/**
	 * Outputs table with heading above data
	 *
	 * @return string HTML table
	 */
	private function output_table_vertical() {

		$table_contents = '';

		// Create thead
		if ( ! empty( $this->thead ) ) {
			foreach ( $this->thead as $th ) {
				$table_contents .= $this->output_element( 'th', $th, $this->th_attributes );
			}
			$table_contents = $this->output_element( 'tr', $table_contents, $this->tr_attributes );
		}

		// Create tbody
		foreach ( $this->tbody as $tr ) {
			$tr_str = '';

			foreach ( $tr as $td ) {
				$tr_str .= $this->output_element( 'td', $td, $this->td_attributes );
			}

			$table_contents .= $this->output_element( 'tr', $tr_str, $this->tr_attributes );
		}

		// Wrap it all up in a table
		$output = $this->output_element( 'table', $table_contents, $this->table_attributes );

		return $output;
	}

	/**
	 * Outputs table with heading to the left of the data
	 *
	 * @return string HTML table
	 */
	private function output_table_horizontal() {

		$table_contents = '';

		// Finds the table row with the most columns
		$max_col = isset( $this->thead ) ? count( $this->thead ) : 1;
		foreach ( $this->tbody as $table_item ) {
			if ( $max_col < count( $table_item ) ) {
				$max_col = count( $table_item );
			}
		}

		// Create table rows
		for ( $i = 0; $i < $max_col; $i++ ) {
			$tr_contents = '';

			// row heading
			if ( isset( $this->thead[ $i ] ) ) {
				$tr_contents .= $this->output_element( 'th', $this->thead[ $i ], $this->th_attributes );
			}

			// columns
			foreach ( $this->tbody as $table_item ) {
				if ( ! isset( $table_item[ $i ] ) ) {
					continue;
				}
				$tr_contents .= $this->output_element( 'td', $table_item[ $i ], $this->td_attributes );
			}

			$table_contents .= $this->output_element( 'tr', $tr_contents, $this->tr_attributes );
		}

		// Wrap it all up in a table
		$output = $this->output_element( 'table', $table_contents, $this->table_attributes );

		return $output;
	}

	/**
	 * Outputs an HTML element, mostly useful for elements that have attributes
	 *
	 * @param string $html_tag   HTML element name. Example: 'table'
	 * @param string $data       Text/HTML contained inside of the element
	 * @param array  $attributes HTML attributes for element
	 *
	 * @return string HTML element
	 */
	private function output_element( $html_tag, $data = null, $attributes = [] ) {
		$output = '<' . tag_escape( $html_tag );

		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $att => $val ) {
				$output .= ' ' . $att . '="' . esc_attr( $val ) . '"';
			}
		}

		if ( is_string( $data ) ) {
			$output .= '>';
			$output .= ( 'td' === $html_tag && $this->html_escape_td_values ) ? esc_html( $data ) : $data;
			$output .= '</' . tag_escape( $html_tag ) . '>';
		} else {
			$output .= ' />';
		}

		return $output;
	}

}
