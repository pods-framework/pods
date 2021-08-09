<?php


interface Tribe__Languages__Map_Interface {

	/**
	 * Gets all the languages supported by this language map.
	 *
	 * @return array An associative array in the format
	 *               [ <slug> => <name> ]
	 *               e.g. [ 'pt-BR' => 'Portuguese (Brazil)' ]
	 */
	public function get_supported_languages();

	/**
	 * Checks whether a language code is supported by the language map or not.
	 *
	 * @param string $language_code
	 *
	 * @return bool Whether a language code is supported by the language map or not.
	 */
	public function is_supported( $language_code );

	/**
	 * Converts a language code from the format used by WP to the one used by the language map.
	 *
	 * @param string $language_code A language code in the format used by WP; e.g. `en_US`.
	 *
	 * @return string|false The converted language code or `false` if the language code is not supported.
	 */
	public function convert_language_code( $language_code );
}
