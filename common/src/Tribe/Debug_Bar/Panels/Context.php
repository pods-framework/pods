<?php
use Tribe__Utils__Array as Arr;

/**
 * The Debug Bar panel that will display tribe context informations.
 *
 * @since 4.9.5
 */

class Tribe__Debug_Bar__Panels__Context extends Debug_Bar_Panel {

	/**
	 * Returns the Panel name.
	 *
	 * @since 4.9.5
	 *
	 * @param null $title The panel input title.
	 *
	 * @return string The panel title
	 */
	public function title( $title = null ) {
		return __( 'The Events Calendar Context', 'tribe-common' );
	}

	/**
	 * Renders the panel contents.
	 *
	 * @since 4.9.5
	 */
	public function render() {
		$html = '<style>
			#mt-debug-bar .mt-debug-bar-title {
				margin-bottom: 1em;
			}
			#mt-debug-bar .mt-debug-bar-context-table {
				width: 100%;
				font-size: 120%;
			}
			#mt-debug-bar .mt-debug-bar-context-table td {
				padding: .5em .5em .5em 1em;
				border: black solid 1px;
			}
			#mt-debug-bar .mt-debug-bar-context-table th {
				padding: 1em;
				border: black solid 1px;
			}
			</style>';
		$html .= '<div id="mt-debug-bar" class="mt-debug-bar-context">';

		$html .= '<header class="mt-debug-bar-title"><h2>' . esc_html__( 'The Events Calendar Context', 'tribe-common' ) . '</h2></header>';

		$html .= '<section>';
		$html .= '<header class="mt-debug-bar-section-header"><h3>' . esc_html__( 'PHP Render Context', 'tribe-common' ) . '</h3></header>';
		$html .= '<table class="mt-debug-bar-context-table"
			align="left"
			cellspacing="0"
			cellpadding="10px"
			style="width: 100%; font-size: 120%; border: slategray solid 1px">';

		$html .= '<thead><tr>';
		$html .= '<th class="col1 key">' . __( 'Key', 'tribe-common' ) . '</th>';
		$html .= '<th class="col2 value">' . __( 'Value', 'tribe-common' ) . '</th>';
		$html .= '<th class="col3 orm-arg">' . __( 'ORM arg', 'tribe-common' ) . '</th>';
		$html .= '<th class="col4 reads">' . __( 'Reads', 'tribe-common' ) . '</th>';
		$html .= '<th class="col5 writes">' . __( 'Writes', 'tribe-common' ) . '</th>';
		$html .= '</tr></thead>';
		$html .= '<tbody>';

		$locations = tribe_context()->get_locations();
		$context   = tribe_context()->to_array();
		$orm_args = tribe_context()->get_orm_args();

		foreach ( $locations as $key => $rw_data ) {
			$orm_arg_key = Arr::get( $locations, [ $key, 'orm_arg' ], $key );
			$orm_arg_value = Arr::get( $orm_args, $orm_arg_key, null );

			$html .= '<tr>';
			$html .= '<td><code>' . $key . '</code></td>';
			$html .= '<td><code>' . ( isset( $context[ $key ] ) ? print_r( $context[ $key ], true ) : 'undefined' ) . '</code></td>';
			$html .= '<td>' . ( false !== $orm_arg_key ? '<code>' . print_r( $orm_arg_key, true )  . ' => ' . print_r( $orm_arg_value, true ) . '</code>' : '' ) . '</td>';
			$html .= '<td><code>' . ( isset( $locations[ $key ]['read'] ) ? 'yes' : 'no' ) . '</code></td>';
			$html .= '<td><code>' . ( isset( $locations[ $key ]['write'] ) ? 'yes' : 'no' ) . '</code></td>';
			$html .= '</tr>';
		}

		$html .= '</table>';
		$html .= '</section>';
		$html .= '</br>';

		$state = tribe_context()->get_state();
		$html  .= '<section><header class="mt-debug-bar-section-header"><h3>' . esc_attr__( 'State',
				'tribe-common' ) . '</h3></header>';
		$html  .= '<code>' . json_encode( $state, JSON_PRETTY_PRINT ) . '</code></section>';

		$html .= '</div>';

		echo $html;
	}
}
