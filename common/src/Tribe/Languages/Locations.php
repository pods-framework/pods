<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class Locations
 *
 * Localized lists of locations, like countries and states.
 */
class Tribe__Languages__Locations {

	/**
	 * Returns an array of countries and their codes.
	 *
	 * Adds array to object cache to speed up subsequent retrievals.
	 *
	 * @since 4.13.0 add $escape param.
	 *
	 * @param bool $escape Weather to escape for translations or not.
	 *
	 * @return array {
	 *      List of countries
	 *
	 *      @type string $country_code Country name.
	 * }
	 */
	public function get_countries( $escape = false ) {
		/**
		 * @var Tribe__Cache $cache
		 */
		$cache     = tribe( 'cache' );
		$cache_key = 'tribe_country_list' . ( $escape ? '-escaped' : '' );
		$countries = $cache->get( $cache_key , '', null );

		if ( null === $countries ) {
			$countries = $this->build_country_array();

			if ( $escape ) {
				$countries = array_map( static function( $country ) {
					return html_entity_decode( $country, ENT_QUOTES );
				}, $countries );
			}

			// Actually set the cache in case it's not in place.
			$cache->set( $cache_key, $countries );
		}

		return $countries;
	}

	/**
	 * Returns an array of countries and their codes.
	 *
	 * Adds array to object cache to speed up subsequent retrievals.
	 *
	 * @return array {
	 *      List of countries
	 *
	 *      @type string $country_code Country name.
	 * }
	 */
	public function get_us_states() {
		return tribe( 'cache' )->get( 'tribe_us_states_list', '', [ $this, 'build_us_states_array' ] );
	}

	/**
	 * Get a translated array of countries.
	 *
	 * @return array {
	 *      List of countries
	 *
	 *      @type string $country_code Country name.
	 * }
	 */
	public function build_country_array() {
		$countries = [
			'US' => esc_html__( 'United States', 'tribe-common' ),
			'AF' => esc_html__( 'Afghanistan', 'tribe-common' ),
			'AX' => esc_html__( '&Aring;land Islands', 'tribe-common' ),
			'AL' => esc_html__( 'Albania', 'tribe-common' ),
			'DZ' => esc_html__( 'Algeria', 'tribe-common' ),
			'AS' => esc_html__( 'American Samoa', 'tribe-common' ),
			'AD' => esc_html__( 'Andorra', 'tribe-common' ),
			'AO' => esc_html__( 'Angola', 'tribe-common' ),
			'AI' => esc_html__( 'Anguilla', 'tribe-common' ),
			'AQ' => esc_html__( 'Antarctica', 'tribe-common' ),
			'AG' => esc_html__( 'Antigua and Barbuda', 'tribe-common' ),
			'AR' => esc_html__( 'Argentina', 'tribe-common' ),
			'AM' => esc_html__( 'Armenia', 'tribe-common' ),
			'AW' => esc_html__( 'Aruba', 'tribe-common' ),
			'AU' => esc_html__( 'Australia', 'tribe-common' ),
			'AT' => esc_html__( 'Austria', 'tribe-common' ),
			'AZ' => esc_html__( 'Azerbaijan', 'tribe-common' ),
			'BS' => esc_html__( 'Bahamas', 'tribe-common' ),
			'BH' => esc_html__( 'Bahrain', 'tribe-common' ),
			'BD' => esc_html__( 'Bangladesh', 'tribe-common' ),
			'BB' => esc_html__( 'Barbados', 'tribe-common' ),
			'BY' => esc_html__( 'Belarus', 'tribe-common' ),
			'BE' => esc_html__( 'Belgium', 'tribe-common' ),
			'BZ' => esc_html__( 'Belize', 'tribe-common' ),
			'BJ' => esc_html__( 'Benin', 'tribe-common' ),
			'BM' => esc_html__( 'Bermuda', 'tribe-common' ),
			'BT' => esc_html__( 'Bhutan', 'tribe-common' ),
			'BO' => esc_html__( 'Bolivia', 'tribe-common' ),
			'BA' => esc_html__( 'Bosnia and Herzegovina', 'tribe-common' ),
			'BW' => esc_html__( 'Botswana', 'tribe-common' ),
			'BV' => esc_html__( 'Bouvet Island', 'tribe-common' ),
			'BR' => esc_html__( 'Brazil', 'tribe-common' ),
			'IO' => esc_html__( 'British Indian Ocean Territory', 'tribe-common' ),
			'BN' => esc_html__( 'Brunei Darussalam', 'tribe-common' ),
			'BG' => esc_html__( 'Bulgaria', 'tribe-common' ),
			'BF' => esc_html__( 'Burkina Faso', 'tribe-common' ),
			'BI' => esc_html__( 'Burundi', 'tribe-common' ),
			'KH' => esc_html__( 'Cambodia', 'tribe-common' ),
			'CM' => esc_html__( 'Cameroon', 'tribe-common' ),
			'CA' => esc_html__( 'Canada', 'tribe-common' ),
			'CV' => esc_html__( 'Cape Verde', 'tribe-common' ),
			'KY' => esc_html__( 'Cayman Islands', 'tribe-common' ),
			'CF' => esc_html__( 'Central African Republic', 'tribe-common' ),
			'TD' => esc_html__( 'Chad', 'tribe-common' ),
			'CL' => esc_html__( 'Chile', 'tribe-common' ),
			'CN' => esc_html__( 'China', 'tribe-common' ),
			'CX' => esc_html__( 'Christmas Island', 'tribe-common' ),
			'CC' => esc_html__( 'Cocos (Keeling) Islands', 'tribe-common' ),
			'MF' => esc_html__( 'Collectivity of Saint Martin', 'tribe-common' ),
			'CO' => esc_html__( 'Colombia', 'tribe-common' ),
			'KM' => esc_html__( 'Comoros', 'tribe-common' ),
			'CG' => esc_html__( 'Congo', 'tribe-common' ),
			'CD' => esc_html__( 'Congo, Democratic Republic of the', 'tribe-common' ),
			'CK' => esc_html__( 'Cook Islands', 'tribe-common' ),
			'CR' => esc_html__( 'Costa Rica', 'tribe-common' ),
			'CI' => esc_html__( "C&ocirc;te d'Ivoire", 'tribe-common' ),
			'HR' => esc_html__( 'Croatia (Local Name: Hrvatska)', 'tribe-common' ),
			'CU' => esc_html__( 'Cuba', 'tribe-common' ),
			'CW' => esc_html__( 'Cura&ccedil;ao', 'tribe-common' ),
			'CY' => esc_html__( 'Cyprus', 'tribe-common' ),
			'CZ' => esc_html__( 'Czech Republic', 'tribe-common' ),
			'DK' => esc_html__( 'Denmark', 'tribe-common' ),
			'DJ' => esc_html__( 'Djibouti', 'tribe-common' ),
			'DM' => esc_html__( 'Dominica', 'tribe-common' ),
			'DO' => esc_html__( 'Dominican Republic', 'tribe-common' ),
			'TP' => esc_html__( 'East Timor', 'tribe-common' ),
			'EC' => esc_html__( 'Ecuador', 'tribe-common' ),
			'EG' => esc_html__( 'Egypt', 'tribe-common' ),
			'SV' => esc_html__( 'El Salvador', 'tribe-common' ),
			'GQ' => esc_html__( 'Equatorial Guinea', 'tribe-common' ),
			'ER' => esc_html__( 'Eritrea', 'tribe-common' ),
			'EE' => esc_html__( 'Estonia', 'tribe-common' ),
			'ET' => esc_html__( 'Ethiopia', 'tribe-common' ),
			'FK' => esc_html__( 'Falkland Islands (Malvinas)', 'tribe-common' ),
			'FO' => esc_html__( 'Faroe Islands', 'tribe-common' ),
			'FJ' => esc_html__( 'Fiji', 'tribe-common' ),
			'FI' => esc_html__( 'Finland', 'tribe-common' ),
			'FR' => esc_html__( 'France', 'tribe-common' ),
			'GF' => esc_html__( 'French Guiana', 'tribe-common' ),
			'PF' => esc_html__( 'French Polynesia', 'tribe-common' ),
			'TF' => esc_html__( 'French Southern Territories', 'tribe-common' ),
			'GA' => esc_html__( 'Gabon', 'tribe-common' ),
			'GM' => esc_html__( 'Gambia', 'tribe-common' ),
			'GE' => esc_html_x( 'Georgia', 'The country', 'tribe-common' ),
			'DE' => esc_html__( 'Germany', 'tribe-common' ),
			'GH' => esc_html__( 'Ghana', 'tribe-common' ),
			'GI' => esc_html__( 'Gibraltar', 'tribe-common' ),
			'GR' => esc_html__( 'Greece', 'tribe-common' ),
			'GL' => esc_html__( 'Greenland', 'tribe-common' ),
			'GD' => esc_html__( 'Grenada', 'tribe-common' ),
			'GP' => esc_html__( 'Guadeloupe', 'tribe-common' ),
			'GU' => esc_html__( 'Guam', 'tribe-common' ),
			'GT' => esc_html__( 'Guatemala', 'tribe-common' ),
			'GN' => esc_html__( 'Guinea', 'tribe-common' ),
			'GW' => esc_html__( 'Guinea-Bissau', 'tribe-common' ),
			'GY' => esc_html__( 'Guyana', 'tribe-common' ),
			'HT' => esc_html__( 'Haiti', 'tribe-common' ),
			'HM' => esc_html__( 'Heard and McDonald Islands', 'tribe-common' ),
			'VA' => esc_html__( 'Holy See (Vatican City State)', 'tribe-common' ),
			'HN' => esc_html__( 'Honduras', 'tribe-common' ),
			'HK' => esc_html__( 'Hong Kong', 'tribe-common' ),
			'HU' => esc_html__( 'Hungary', 'tribe-common' ),
			'IS' => esc_html__( 'Iceland', 'tribe-common' ),
			'IN' => esc_html__( 'India', 'tribe-common' ),
			'ID' => esc_html__( 'Indonesia', 'tribe-common' ),
			'IR' => esc_html__( 'Iran, Islamic Republic of', 'tribe-common' ),
			'IQ' => esc_html__( 'Iraq', 'tribe-common' ),
			'IE' => esc_html__( 'Ireland', 'tribe-common' ),
			'IL' => esc_html__( 'Israel', 'tribe-common' ),
			'IT' => esc_html__( 'Italy', 'tribe-common' ),
			'JM' => esc_html__( 'Jamaica', 'tribe-common' ),
			'JP' => esc_html__( 'Japan', 'tribe-common' ),
			'JO' => esc_html__( 'Jordan', 'tribe-common' ),
			'KZ' => esc_html__( 'Kazakhstan', 'tribe-common' ),
			'KE' => esc_html__( 'Kenya', 'tribe-common' ),
			'KI' => esc_html__( 'Kiribati', 'tribe-common' ),
			'KP' => esc_html__( "Korea, Democratic People's Republic of", 'tribe-common' ),
			'KR' => esc_html__( 'Korea, Republic of', 'tribe-common' ),
			'KW' => esc_html__( 'Kuwait', 'tribe-common' ),
			'KG' => esc_html__( 'Kyrgyzstan', 'tribe-common' ),
			'LA' => esc_html__( "Lao People's Democratic Republic", 'tribe-common' ),
			'LV' => esc_html__( 'Latvia', 'tribe-common' ),
			'LB' => esc_html__( 'Lebanon', 'tribe-common' ),
			'LS' => esc_html__( 'Lesotho', 'tribe-common' ),
			'LR' => esc_html__( 'Liberia', 'tribe-common' ),
			'LY' => esc_html__( 'Libya', 'tribe-common' ),
			'LI' => esc_html__( 'Liechtenstein', 'tribe-common' ),
			'LT' => esc_html__( 'Lithuania', 'tribe-common' ),
			'LU' => esc_html__( 'Luxembourg', 'tribe-common' ),
			'MO' => esc_html__( 'Macau', 'tribe-common' ),
			'MG' => esc_html__( 'Madagascar', 'tribe-common' ),
			'MW' => esc_html__( 'Malawi', 'tribe-common' ),
			'MY' => esc_html__( 'Malaysia', 'tribe-common' ),
			'MV' => esc_html__( 'Maldives', 'tribe-common' ),
			'ML' => esc_html__( 'Mali', 'tribe-common' ),
			'MT' => esc_html__( 'Malta', 'tribe-common' ),
			'MH' => esc_html__( 'Marshall Islands', 'tribe-common' ),
			'MQ' => esc_html__( 'Martinique', 'tribe-common' ),
			'MR' => esc_html__( 'Mauritania', 'tribe-common' ),
			'MU' => esc_html__( 'Mauritius', 'tribe-common' ),
			'YT' => esc_html__( 'Mayotte', 'tribe-common' ),
			'MX' => esc_html__( 'Mexico', 'tribe-common' ),
			'FM' => esc_html__( 'Micronesia, Federated States of', 'tribe-common' ),
			'MD' => esc_html__( 'Moldova, Republic of', 'tribe-common' ),
			'MC' => esc_html__( 'Monaco', 'tribe-common' ),
			'MN' => esc_html__( 'Mongolia', 'tribe-common' ),
			'ME' => esc_html__( 'Montenegro', 'tribe-common' ),
			'MS' => esc_html__( 'Montserrat', 'tribe-common' ),
			'MA' => esc_html__( 'Morocco', 'tribe-common' ),
			'MZ' => esc_html__( 'Mozambique', 'tribe-common' ),
			'MM' => esc_html__( 'Myanmar', 'tribe-common' ),
			'NA' => esc_html__( 'Namibia', 'tribe-common' ),
			'NR' => esc_html__( 'Nauru', 'tribe-common' ),
			'NP' => esc_html__( 'Nepal', 'tribe-common' ),
			'NL' => esc_html__( 'Netherlands', 'tribe-common' ),
			'NC' => esc_html__( 'New Caledonia', 'tribe-common' ),
			'NZ' => esc_html__( 'New Zealand', 'tribe-common' ),
			'NI' => esc_html__( 'Nicaragua', 'tribe-common' ),
			'NE' => esc_html__( 'Niger', 'tribe-common' ),
			'NG' => esc_html__( 'Nigeria', 'tribe-common' ),
			'NU' => esc_html__( 'Niue', 'tribe-common' ),
			'NF' => esc_html__( 'Norfolk Island', 'tribe-common' ),
			'MK' => esc_html__( 'North Macedonia', 'tribe-common' ),
			'MP' => esc_html__( 'Northern Mariana Islands', 'tribe-common' ),
			'NO' => esc_html__( 'Norway', 'tribe-common' ),
			'OM' => esc_html__( 'Oman', 'tribe-common' ),
			'PK' => esc_html__( 'Pakistan', 'tribe-common' ),
			'PW' => esc_html__( 'Palau', 'tribe-common' ),
			'PA' => esc_html__( 'Panama', 'tribe-common' ),
			'PG' => esc_html__( 'Papua New Guinea', 'tribe-common' ),
			'PY' => esc_html__( 'Paraguay', 'tribe-common' ),
			'PE' => esc_html__( 'Peru', 'tribe-common' ),
			'PH' => esc_html__( 'Philippines', 'tribe-common' ),
			'PN' => esc_html__( 'Pitcairn', 'tribe-common' ),
			'PL' => esc_html__( 'Poland', 'tribe-common' ),
			'PT' => esc_html__( 'Portugal', 'tribe-common' ),
			'PR' => esc_html__( 'Puerto Rico', 'tribe-common' ),
			'QA' => esc_html__( 'Qatar', 'tribe-common' ),
			'RE' => esc_html__( 'Reunion', 'tribe-common' ),
			'RO' => esc_html__( 'Romania', 'tribe-common' ),
			'RU' => esc_html__( 'Russian Federation', 'tribe-common' ),
			'RW' => esc_html__( 'Rwanda', 'tribe-common' ),
			'BL' => esc_html__( 'Saint Barth&eacute;lemy', 'tribe-common' ),
			'SH' => esc_html__( 'Saint Helena', 'tribe-common' ),
			'KN' => esc_html__( 'Saint Kitts and Nevis', 'tribe-common' ),
			'LC' => esc_html__( 'Saint Lucia', 'tribe-common' ),
			'PM' => esc_html__( 'Saint Pierre and Miquelon', 'tribe-common' ),
			'VC' => esc_html__( 'Saint Vincent and The Grenadines', 'tribe-common' ),
			'WS' => esc_html__( 'Samoa', 'tribe-common' ),
			'SM' => esc_html__( 'San Marino', 'tribe-common' ),
			'ST' => esc_html__( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'tribe-common' ),
			'SA' => esc_html__( 'Saudi Arabia', 'tribe-common' ),
			'SN' => esc_html__( 'Senegal', 'tribe-common' ),
			'RS' => esc_html__( 'Serbia', 'tribe-common' ),
			'SC' => esc_html__( 'Seychelles', 'tribe-common' ),
			'SL' => esc_html__( 'Sierra Leone', 'tribe-common' ),
			'SG' => esc_html__( 'Singapore', 'tribe-common' ),
			'SX' => esc_html__( 'Sint Maarten', 'tribe-common' ),
			'SK' => esc_html__( 'Slovakia (Slovak Republic)', 'tribe-common' ),
			'SI' => esc_html__( 'Slovenia', 'tribe-common' ),
			'SB' => esc_html__( 'Solomon Islands', 'tribe-common' ),
			'SO' => esc_html__( 'Somalia', 'tribe-common' ),
			'ZA' => esc_html__( 'South Africa', 'tribe-common' ),
			'GS' => esc_html__( 'South Georgia, South Sandwich Islands', 'tribe-common' ),
			'ES' => esc_html__( 'Spain', 'tribe-common' ),
			'LK' => esc_html__( 'Sri Lanka', 'tribe-common' ),
			'SD' => esc_html__( 'Sudan', 'tribe-common' ),
			'SR' => esc_html__( 'Suriname', 'tribe-common' ),
			'SJ' => esc_html__( 'Svalbard and Jan Mayen Islands', 'tribe-common' ),
			'SZ' => esc_html__( 'Swaziland', 'tribe-common' ),
			'SE' => esc_html__( 'Sweden', 'tribe-common' ),
			'CH' => esc_html__( 'Switzerland', 'tribe-common' ),
			'SY' => esc_html__( 'Syrian Arab Republic', 'tribe-common' ),
			'TW' => esc_html__( 'Taiwan', 'tribe-common' ),
			'TJ' => esc_html__( 'Tajikistan', 'tribe-common' ),
			'TZ' => esc_html__( 'Tanzania, United Republic of', 'tribe-common' ),
			'TH' => esc_html__( 'Thailand', 'tribe-common' ),
			'TG' => esc_html__( 'Togo', 'tribe-common' ),
			'TK' => esc_html__( 'Tokelau', 'tribe-common' ),
			'TO' => esc_html__( 'Tonga', 'tribe-common' ),
			'TT' => esc_html__( 'Trinidad and Tobago', 'tribe-common' ),
			'TN' => esc_html__( 'Tunisia', 'tribe-common' ),
			'TR' => esc_html__( 'Turkey', 'tribe-common' ),
			'TM' => esc_html__( 'Turkmenistan', 'tribe-common' ),
			'TC' => esc_html__( 'Turks and Caicos Islands', 'tribe-common' ),
			'TV' => esc_html__( 'Tuvalu', 'tribe-common' ),
			'UG' => esc_html__( 'Uganda', 'tribe-common' ),
			'UA' => esc_html__( 'Ukraine', 'tribe-common' ),
			'AE' => esc_html__( 'United Arab Emirates', 'tribe-common' ),
			'GB' => esc_html__( 'United Kingdom', 'tribe-common' ),
			'UM' => esc_html__( 'United States Minor Outlying Islands', 'tribe-common' ),
			'UY' => esc_html__( 'Uruguay', 'tribe-common' ),
			'UZ' => esc_html__( 'Uzbekistan', 'tribe-common' ),
			'VU' => esc_html__( 'Vanuatu', 'tribe-common' ),
			'VE' => esc_html__( 'Venezuela', 'tribe-common' ),
			'VN' => esc_html__( 'Viet Nam', 'tribe-common' ),
			'VG' => esc_html__( 'Virgin Islands (British)', 'tribe-common' ),
			'VI' => esc_html__( 'Virgin Islands (U.S.)', 'tribe-common' ),
			'WF' => esc_html__( 'Wallis and Futuna Islands', 'tribe-common' ),
			'EH' => esc_html__( 'Western Sahara', 'tribe-common' ),
			'YE' => esc_html__( 'Yemen', 'tribe-common' ),
			'ZM' => esc_html__( 'Zambia', 'tribe-common' ),
			'ZW' => esc_html__( 'Zimbabwe', 'tribe-common' ),
		];

		// Perform a natural sort, ensures the countries are in the expected order even once translated.
		natsort( $countries );

		/**
		 * Filter that allows to change the list and the output of the countries names.
		 *
		 * @since 4.7.12
		 *
		 * @param array associative array with: Country Code => Country Name
		 */
		return (array) apply_filters( 'tribe_countries', $countries );
	}

	/**
	 * Get a translated array of US States.
	 *
	 * @return array {
	 *      List of States
	 *
	 *      @type string $state_abbreviation State.
	 * }
	 */
	public function build_us_states_array() {
		$states = [
			'AL' => esc_html__( 'Alabama', 'tribe-common' ),
			'AK' => esc_html__( 'Alaska', 'tribe-common' ),
			'AZ' => esc_html__( 'Arizona', 'tribe-common' ),
			'AR' => esc_html__( 'Arkansas', 'tribe-common' ),
			'CA' => esc_html__( 'California', 'tribe-common' ),
			'CO' => esc_html__( 'Colorado', 'tribe-common' ),
			'CT' => esc_html__( 'Connecticut', 'tribe-common' ),
			'DE' => esc_html__( 'Delaware', 'tribe-common' ),
			'DC' => esc_html__( 'District of Columbia', 'tribe-common' ),
			'FL' => esc_html__( 'Florida', 'tribe-common' ),
			'GA' => esc_html_x( 'Georgia', 'The US state Georgia', 'tribe-common' ),
			'HI' => esc_html__( 'Hawaii', 'tribe-common' ),
			'ID' => esc_html__( 'Idaho', 'tribe-common' ),
			'IL' => esc_html__( 'Illinois', 'tribe-common' ),
			'IN' => esc_html__( 'Indiana', 'tribe-common' ),
			'IA' => esc_html__( 'Iowa', 'tribe-common' ),
			'KS' => esc_html__( 'Kansas', 'tribe-common' ),
			'KY' => esc_html__( 'Kentucky', 'tribe-common' ),
			'LA' => esc_html__( 'Louisiana', 'tribe-common' ),
			'ME' => esc_html__( 'Maine', 'tribe-common' ),
			'MD' => esc_html__( 'Maryland', 'tribe-common' ),
			'MA' => esc_html__( 'Massachusetts', 'tribe-common' ),
			'MI' => esc_html__( 'Michigan', 'tribe-common' ),
			'MN' => esc_html__( 'Minnesota', 'tribe-common' ),
			'MS' => esc_html__( 'Mississippi', 'tribe-common' ),
			'MO' => esc_html__( 'Missouri', 'tribe-common' ),
			'MT' => esc_html__( 'Montana', 'tribe-common' ),
			'NE' => esc_html__( 'Nebraska', 'tribe-common' ),
			'NV' => esc_html__( 'Nevada', 'tribe-common' ),
			'NH' => esc_html__( 'New Hampshire', 'tribe-common' ),
			'NJ' => esc_html__( 'New Jersey', 'tribe-common' ),
			'NM' => esc_html__( 'New Mexico', 'tribe-common' ),
			'NY' => esc_html__( 'New York', 'tribe-common' ),
			'NC' => esc_html__( 'North Carolina', 'tribe-common' ),
			'ND' => esc_html__( 'North Dakota', 'tribe-common' ),
			'OH' => esc_html__( 'Ohio', 'tribe-common' ),
			'OK' => esc_html__( 'Oklahoma', 'tribe-common' ),
			'OR' => esc_html__( 'Oregon', 'tribe-common' ),
			'PA' => esc_html__( 'Pennsylvania', 'tribe-common' ),
			'RI' => esc_html__( 'Rhode Island', 'tribe-common' ),
			'SC' => esc_html__( 'South Carolina', 'tribe-common' ),
			'SD' => esc_html__( 'South Dakota', 'tribe-common' ),
			'TN' => esc_html__( 'Tennessee', 'tribe-common' ),
			'TX' => esc_html__( 'Texas', 'tribe-common' ),
			'UT' => esc_html__( 'Utah', 'tribe-common' ),
			'VT' => esc_html__( 'Vermont', 'tribe-common' ),
			'VA' => esc_html__( 'Virginia', 'tribe-common' ),
			'WA' => esc_html__( 'Washington', 'tribe-common' ),
			'WV' => esc_html__( 'West Virginia', 'tribe-common' ),
			'WI' => esc_html__( 'Wisconsin', 'tribe-common' ),
			'WY' => esc_html__( 'Wyoming', 'tribe-common' ),
		];

		// Perform a natural sort, ensures the states are in the expected order even once translated.
		natsort( $states );

		/**
		 * Filter that allows to change the names of US states before output.
		 *
		 * @since 4.7.12
		 *
		 * @param array Associative array with the format: State Code => State Name
		 */
		return (array) apply_filters( 'tribe_us_states', $states );
	}
}
