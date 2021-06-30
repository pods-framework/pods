<?php
/**
 * JSON-LD information Debug Bar panel.
 *
 * @since   4.11.2
 *
 * @package Tribe\Debug_Bar\Panels
 */

class Tribe__Debug_Bar__Panels__Json_Ld extends Debug_Bar_Panel {
	/**
	 * Returns the Panel name.
	 *
	 * @since 4.11.2
	 *
	 * @param null $title The panel input title.
	 *
	 * @return string The panel title
	 */
	public function title( $title = null ) {
		return __( 'The Events Calendar JSON-LD Data', 'tribe-common' );
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
			#mt-debug-bar .mt-debug-bar-section {
				padding: .5em .5em .5em 1em;
			}
			</style>';
		$html .= '<div id="mt-debug-bar" class="mt-debug-bar-json-ld">';

		$html .= '<header class="mt-debug-bar-title"><h2>' . esc_html__( 'The Events Calendar JSON-LD Data',
				'tribe-common' ) . '</h2></header>';


		$json_ld_data = array_filter( (array) tribe_cache()['json-ld-data'] );

		if ( ! empty( $json_ld_data ) ) {
			$html .= '<div class="mt-debug-bar-section">';
			$html .= sprintf(
				'<header>The request produced %d JSON-LD data %s.</header><br>',
				count( $json_ld_data ),
				count( $json_ld_data ) > 1 ? 'scripts' : 'script'
			);

			$html .= '<p>Copy the code below and paste it into ' .
			         '<a href="https://search.google.com/structured-data/testing-tool/u/0/" target="_blank">' .
			         'Google\'s Structured Data Testing Tool' .
			         '</a>' .
			         ' to test it using the Code Snippet option.</p><br>';

			foreach ( $json_ld_data as $full_entry ) {
				preg_match(
					'/(?<open>^\\s*<script[^>]*?>\\s*)(?<json>.*)(?<close>\\s<\\/script>)$/uism',
					$full_entry,
					$frags
				);

				if ( isset( $frags['open'], $frags['json'], $frags['close'] ) ) {
					// Let's try and format it if we've got all the pieces.
					$full_entry = $frags['open']
					              . json_encode( json_decode( $frags['json'], true ), JSON_PRETTY_PRINT )
					              . $frags['close'];
				}

				$html .= sprintf( '<pre><code>%s</code></pre>', esc_html( $full_entry ) );
			}

			$html .= '</div>';
		}

		$html .= '</div>';

		echo $html;
	}
}
