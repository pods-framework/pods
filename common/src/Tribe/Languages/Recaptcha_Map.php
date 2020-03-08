<?php


/**
 * Class Tribe__Languages__Recaptcha_Map
 *
 * Converts WordPress format language codes to language codes supported by Recaptcha.
 */
class Tribe__Languages__Recaptcha_Map implements Tribe__Languages__Map_Interface {

	/**
	 * Gets all the languages supported by this language map.
	 *
	 * @return array An associative array in the format
	 *               [ <slug> => <name> ]
	 *               e.g. [ 'pt-BR' => 'Portuguese (Brazil)' ]
	 */
	public function get_supported_languages() {
		return array(
			'ar'     => 'Arabic',
			'af'     => 'Afrikaans',
			'am'     => 'Amharic',
			'hy'     => 'Armenian',
			'az'     => 'Azerbaijani',
			'eu'     => 'Basque',
			'bn'     => 'Bengali',
			'bg'     => 'Bulgarian',
			'ca'     => 'Catalan',
			'zh-HK'  => 'Chinese (Hong Kong)',
			'zh-CN'  => 'Chinese (Simplified)',
			'zh-TW'  => 'Chinese (Traditional)',
			'hr'     => 'Croatian',
			'cs'     => 'Czech',
			'da'     => 'Danish',
			'nl'     => 'Dutch',
			'en-GB'  => 'English (UK)',
			'en'     => 'English (US)',
			'et'     => 'Estonian',
			'fil'    => 'Filipino',
			'fi'     => 'Finnish',
			'fr'     => 'French',
			'fr-CA'  => 'French (Canadian)',
			'gl'     => 'Galician',
			'ka'     => 'Georgian',
			'de'     => 'German',
			'de-AT'  => 'German (Austria)',
			'de-CH'  => 'German (Switzerland)',
			'el'     => 'Greek',
			'gu'     => 'Gujarati',
			'iw'     => 'Hebrew',
			'hi'     => 'Hindi',
			'hu'     => 'Hungarain',
			'is'     => 'Icelandic',
			'id'     => 'Indonesian',
			'it'     => 'Italian',
			'ja'     => 'Japanese',
			'kn'     => 'Kannada',
			'ko'     => 'Korean',
			'lo'     => 'Laothian',
			'lv'     => 'Latvian',
			'lt'     => 'Lithuanian',
			'ms'     => 'Malay',
			'ml'     => 'Malayalam',
			'mr'     => 'Marathi',
			'mn'     => 'Mongolian',
			'no'     => 'Norwegian',
			'fa'     => 'Persian',
			'pl'     => 'Polish',
			'pt'     => 'Portuguese',
			'pt-BR'  => 'Portuguese (Brazil)',
			'pt-PT'  => 'Portuguese (Portugal)',
			'ro'     => 'Romanian',
			'ru'     => 'Russian',
			'sr'     => 'Serbian',
			'si'     => 'Sinhalese',
			'sk'     => 'Slovak',
			'sl'     => 'Slovenian',
			'es'     => 'Spanish',
			'es-419' => 'Spanish (Latin America)',
			'sw'     => 'Swahili',
			'sv'     => 'Swedish',
			'ta'     => 'Tamil',
			'te'     => 'Telugu',
			'th'     => 'Thai',
			'tr'     => 'Turkish',
			'uk'     => 'Ukrainian',
			'ur'     => 'Urdu',
			'vi'     => 'Vietnamese',
			'zu'     => 'Zulu',
		);
	}

	/**
	 * Checks whether a language code is supported by the language map or not.
	 *
	 * @param string $language_code
	 *
	 * @return bool Whether a language code is supported by the language map or not.
	 */
	public function is_supported( $language_code ) {
		return (bool) $this->convert_language_code( $language_code );
	}

	/**
	 * Converts a language code from the format used by WP to the one used by the language map.
	 *
	 * @param string $language_code A language code in the format used by WP; e.g. `en_US`.
	 *
	 * @return string|false The converted language code or `false` if the language code is not supported.
	 */
	public function convert_language_code( $language_code ) {
		$converted_code = $language_code;
		if ( strlen( $language_code ) > 2 ) {
			// go from `en_US` to `en-US`
			$converted_code = str_replace( '_', '-', $language_code );
			$exists         = array_key_exists( $converted_code, $this->get_supported_languages() );
			// try with just the two first chars
			$converted_code = $exists ? $converted_code : $this->convert_language_code( substr( $language_code, 0, 2 ) );
		} else {
			$converted_code = array_key_exists( $language_code, $this->get_supported_languages() ) ? $converted_code : false;
		}

		return $converted_code ? $converted_code : false;
	}
}
