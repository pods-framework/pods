<?php

/**
 * @package Pods
 */
class PodsUIFieldData {

	private $export_data = array();

	/**
	 * PodsFieldData constructor.
	 *
	 * @param string $field_type
	 * @param array  $data
	 */
	public function __construct( $field_type, $data ) {

		$data[ 'field_type' ] = $field_type;
		$this->export_data    = $data;
	}

	/**
	 * Sends direct output
	 */
	public function emit_script() { ?>
		<script type="application/json" class="data"><?php echo self::json_encode_hex_tag( $this->export_data ); ?></script>
	<?php }

	/**
	 * Provides PHP 5.2 support for the JSON_HEX_TAG param with json_encode
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	private static function json_encode_hex_tag( $data ) {

		$search  = array( '<', '>' );
		$replace = array( '\u003C', '\u003E' );

		if ( defined( 'JSON_HEX_TAG' ) ) {
			$string = json_encode( $data, JSON_HEX_TAG );
		} else {
			$string = json_encode( $data );
			$string = str_replace( $search, $replace, $string );
		}

		return $string;
	}
}
