<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_misc
 */
class Test_Misc extends Pods_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_test_mode() {
		$this->assertFalse( pods_is_test_mode() );
	}

	public function test_guest_checkout() {
		$this->assertFalse( pods_no_guest_checkout() );
	}

	public function test_logged_in_only() {
		$this->assertFalse( pods_logged_in_only() );
	}

	public function test_straight_to_checkout() {
		$this->assertFalse( pods_straight_to_checkout() );
	}

	public function test_no_redownload() {
		$this->assertFalse( pods_no_redownload() );
	}

	public function test_is_cc_verify_enabled() {
		$this->assertTrue( pods_is_cc_verify_enabled() );
	}

	public function test_is_odd() {
		$this->assertTrue( pods_is_odd( 3 ) );
		$this->assertFalse( pods_is_odd( 4 ) );
	}

	public function test_get_file_extension() {
		$this->assertEquals( 'php', pods_get_file_extension( 'file.php' ) );
	}

	public function test_string_is_image_url() {
		$this->assertTrue( pods_string_is_image_url( 'jpg' ) );
		$this->assertFalse( pods_string_is_image_url( 'php' ) );
	}

	public function test_get_ip() {
		$this->assertEquals( '10.0.0.0', pods_get_ip() );
	}

	public function test_get_currencies() {
		$expected = array(
			'USD'  => __( 'US Dollars (&#36;)', 'pods' ),
			'EUR'  => __( 'Euros (&euro;)', 'pods' ),
			'GBP'  => __( 'Pounds Sterling (&pound;)', 'pods' ),
			'AUD'  => __( 'Australian Dollars (&#36;)', 'pods' ),
			'BRL'  => __( 'Brazilian Real (R&#36;)', 'pods' ),
			'CAD'  => __( 'Canadian Dollars (&#36;)', 'pods' ),
			'CZK'  => __( 'Czech Koruna', 'pods' ),
			'DKK'  => __( 'Danish Krone', 'pods' ),
			'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'pods' ),
			'HUF'  => __( 'Hungarian Forint', 'pods' ),
			'ILS'  => __( 'Israeli Shekel (&#8362;)', 'pods' ),
			'JPY'  => __( 'Japanese Yen (&yen;)', 'pods' ),
			'MYR'  => __( 'Malaysian Ringgits', 'pods' ),
			'MXN'  => __( 'Mexican Peso (&#36;)', 'pods' ),
			'NZD'  => __( 'New Zealand Dollar (&#36;)', 'pods' ),
			'NOK'  => __( 'Norwegian Krone', 'pods' ),
			'PHP'  => __( 'Philippine Pesos', 'pods' ),
			'PLN'  => __( 'Polish Zloty', 'pods' ),
			'SGD'  => __( 'Singapore Dollar (&#36;)', 'pods' ),
			'SEK'  => __( 'Swedish Krona', 'pods' ),
			'CHF'  => __( 'Swiss Franc', 'pods' ),
			'TWD'  => __( 'Taiwan New Dollars', 'pods' ),
			'THB'  => __( 'Thai Baht (&#3647;)', 'pods' ),
			'INR'  => __( 'Indian Rupee (&#8377;)', 'pods' ),
			'TRY'  => __( 'Turkish Lira (&#8378;)', 'pods' ),
			'RIAL' => __( 'Iranian Rial (&#65020;)', 'pods' ),
			'RUB'  => __( 'Russian Rubles', 'pods' )
		);

		$this->assertEquals( $expected, pods_get_currencies() );

	}

	public function test_get_countries() {
		$expected = array(
			'US' => 'United States',
			'CA' => 'Canada',
			'GB' => 'United Kingdom',
			'AD' => 'Andorra',
			'AE' => 'United Arab Emirates',
			'AF' => 'Afghanistan',
			'AG' => 'Antigua and Barbuda',
			'AI' => 'Anguilla',
			'AL' => 'Albania',
			'AM' => 'Armenia',
			'AN' => 'Netherlands Antilles',
			'AO' => 'Angola',
			'AQ' => 'Antarctica',
			'AR' => 'Argentina',
			'AS' => 'American Samoa',
			'AT' => 'Austria',
			'AU' => 'Australia',
			'AW' => 'Aruba',
			'AZ' => 'Azerbaijan',
			'BA' => 'Bosnia and Herzegovina',
			'BB' => 'Barbados',
			'BD' => 'Bangladesh',
			'BE' => 'Belgium',
			'BF' => 'Burkina Faso',
			'BG' => 'Bulgaria',
			'BH' => 'Bahrain',
			'BI' => 'Burundi',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BN' => 'Brunei Darrussalam',
			'BO' => 'Bolivia',
			'BR' => 'Brazil',
			'BS' => 'Bahamas',
			'BT' => 'Bhutan',
			'BV' => 'Bouvet Island',
			'BW' => 'Botswana',
			'BY' => 'Belarus',
			'BZ' => 'Belize',
			'CC' => 'Cocos Islands',
			'CD' => 'Congo, Democratic People\'s Republic',
			'CF' => 'Central African Republic',
			'CG' => 'Congo, Republic of',
			'CH' => 'Switzerland',
			'CI' => 'Cote d\'Ivoire',
			'CK' => 'Cook Islands',
			'CL' => 'Chile',
			'CM' => 'Cameroon',
			'CN' => 'China',
			'CO' => 'Colombia',
			'CR' => 'Costa Rica',
			'CU' => 'Cuba',
			'CV' => 'Cape Verde',
			'CX' => 'Christmas Island',
			'CY' => 'Cyprus Island',
			'CZ' => 'Czech Republic',
			'DE' => 'Germany',
			'DJ' => 'Djibouti',
			'DK' => 'Denmark',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'DZ' => 'Algeria',
			'EC' => 'Ecuador',
			'EE' => 'Estonia',
			'EG' => 'Egypt',
			'EH' => 'Western Sahara',
			'ER' => 'Eritrea',
			'ES' => 'Spain',
			'ET' => 'Ethiopia',
			'FI' => 'Finland',
			'FJ' => 'Fiji',
			'FK' => 'Falkland Islands',
			'FM' => 'Micronesia',
			'FO' => 'Faroe Islands',
			'FR' => 'France',
			'GA' => 'Gabon',
			'GD' => 'Grenada',
			'GE' => 'Georgia',
			'GF' => 'French Guiana',
			'GG' => 'Guernsey',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GM' => 'Gambia',
			'GN' => 'Guinea',
			'GP' => 'Guadeloupe',
			'GQ' => 'Equatorial Guinea',
			'GR' => 'Greece',
			'GS' => 'South Georgia',
			'GT' => 'Guatemala',
			'GU' => 'Guam',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HK' => 'Hong Kong',
			'HM' => 'Heard and McDonald Islands',
			'HN' => 'Honduras',
			'HR' => 'Croatia/Hrvatska',
			'HT' => 'Haiti',
			'HU' => 'Hungary',
			'ID' => 'Indonesia',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IM' => 'Isle of Man',
			'IN' => 'India',
			'IO' => 'British Indian Ocean Territory',
			'IQ' => 'Iraq',
			'IR' => 'Iran',
			'IS' => 'Iceland',
			'IT' => 'Italy',
			'JE' => 'Jersey',
			'JM' => 'Jamaica',
			'JO' => 'Jordan',
			'JP' => 'Japan',
			'KE' => 'Kenya',
			'KG' => 'Kyrgyzstan',
			'KH' => 'Cambodia',
			'KI' => 'Kiribati',
			'KM' => 'Comoros',
			'KN' => 'Saint Kitts and Nevis',
			'KP' => 'South Korea',
			'KR' => 'North Korea',
			'KW' => 'Kuwait',
			'KY' => 'Cayman Islands',
			'KZ' => 'Kazakhstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LB' => 'Lebanon',
			'LC' => 'Saint Lucia',
			'LI' => 'Liechtenstein',
			'LK' => 'Sri Lanka',
			'LR' => 'Liberia',
			'LS' => 'Lesotho',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'LV' => 'Latvia',
			'LY' => 'Libyan Arab Jamahiriya',
			'MA' => 'Morocco',
			'MC' => 'Monaco',
			'ME' => 'Montenegro',
			'MD' => 'Moldova, Republic of',
			'MG' => 'Madagascar',
			'MH' => 'Marshall Islands',
			'MK' => 'Macedonia',
			'ML' => 'Mali',
			'MM' => 'Myanmar',
			'MN' => 'Mongolia',
			'MO' => 'Macau',
			'MP' => 'Northern Mariana Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MS' => 'Montserrat',
			'MT' => 'Malta',
			'MU' => 'Mauritius',
			'Mv' => 'Maldives',
			'MW' => 'Malawi',
			'MX' => 'Mexico',
			'MY' => 'Malaysia',
			'MZ' => 'Mozambique',
			'NA' => 'Namibia',
			'NC' => 'New Caledonia',
			'NE' => 'Niger',
			'NF' => 'Norfolk Island',
			'NG' => 'Nigeria',
			'NI' => 'Nicaragua',
			'NL' => 'Netherlands',
			'NO' => 'Norway',
			'NP' => 'Nepal',
			'NR' => 'Nauru',
			'NU' => 'Niue',
			'NZ' => 'New Zealand',
			'OM' => 'Oman',
			'PA' => 'Panama',
			'PE' => 'Peru',
			'PF' => 'French Polynesia',
			'PG' => 'Papua New Guinea',
			'PH' => 'Phillipines',
			'PK' => 'Pakistan',
			'PL' => 'Poland',
			'PM' => 'Saint Pierre and Miquelon',
			'PN' => 'Pitcairn Island',
			'PR' => 'Puerto Rico',
			'PS' => 'Palestinian Territories',
			'PT' => 'Portugal',
			'PW' => 'Palau',
			'PY' => 'Paraguay',
			'QA' => 'Qatar',
			'RE' => 'Reunion Island',
			'RO' => 'Romania',
			'RS' => 'Serbia',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'SA' => 'Saudi Arabia',
			'SB' => 'Solomon Islands',
			'SC' => 'Seychelles',
			'SD' => 'Sudan',
			'SE' => 'Sweden',
			'SG' => 'Singapore',
			'SH' => 'Saint Helena',
			'SI' => 'Slovenia',
			'SJ' => 'Svalbard and Jan Mayen Islands',
			'SK' => 'Slovak Republic',
			'SL' => 'Sierra Leone',
			'SM' => 'San Marino',
			'SN' => 'Senegal',
			'SO' => 'Somalia',
			'SR' => 'Suriname',
			'ST' => 'Sao Tome and Principe',
			'SV' => 'El Salvador',
			'SY' => 'Syrian Arab Republic',
			'SZ' => 'Swaziland',
			'TC' => 'Turks and Caicos Islands',
			'TD' => 'Chad',
			'TF' => 'French Southern Territories',
			'TG' => 'Togo',
			'TH' => 'Thailand',
			'TJ' => 'Tajikistan',
			'TK' => 'Tokelau',
			'TM' => 'Turkmenistan',
			'TN' => 'Tunisia',
			'TO' => 'Tonga',
			'TP' => 'East Timor',
			'TR' => 'Turkey',
			'TT' => 'Trinidad and Tobago',
			'TV' => 'Tuvalu',
			'TW' => 'Taiwan',
			'TZ' => 'Tanzania',
			'UA' => 'Ukraine',
			'UG' => 'Uganda',
			'UM' => 'US Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VA' => 'Holy See (City Vatican State)',
			'VC' => 'Saint Vincent and the Grenadines',
			'VE' => 'Venezuela',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (USA)',
			'VN' => 'Vietnam',
			'VU' => 'Vanuatu',
			'WF' => 'Wallis and Futuna Islands',
			'WS' => 'Western Samoa',
			'YE' => 'Yemen',
			'YT' => 'Mayotte',
			'YU' => 'Yugoslavia',
			'ZA' => 'South Africa',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
			'*'  => 'Choose'
		);

		$this->assertEquals( $expected, pods_get_country_list() );
	}

	public function test_states_list() {
		$expected = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
			'AS' => 'American Samoa',
			'CZ' => 'Canal Zone',
			'CM' => 'Commonwealth of the Northern Mariana Islands',
			'FM' => 'Federated States of Micronesia',
			'GU' => 'Guam',
			'MH' => 'Marshall Islands',
			'MP' => 'Northern Mariana Islands',
			'PW' => 'Palau',
			'PI' => 'Philippine Islands',
			'PR' => 'Puerto Rico',
			'TT' => 'Trust Territory of the Pacific Islands',
			'VI' => 'Virgin Islands',
			'AA' => 'Armed Forces - Americas',
			'AE' => 'Armed Forces - Europe, Canada, Middle East, Africa',
			'AP' => 'Armed Forces - Pacific'
		);

		$this->assertEquals( $expected, pods_get_states_list() );
	}

	public function test_provinces_list() {
		$expected = array(
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland and Labrador',
			'NS' => 'Nova Scotia',
			'NT' => 'Northwest Territories',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon'
		);

		$this->assertEquals( $expected, pods_get_provinces_list() );
	}

	public function test_month_num_to_name() {
		$this->assertEquals( 'Jan', pods_month_num_to_name( 1 ) );
	}

	public function test_get_php_arg_separator_output() {
		$this->assertEquals( '&', pods_get_php_arg_separator_output() );
	}

	public function test_let_to_num() {
		$this->assertEquals( 0, pods_let_to_num( WP_MEMORY_LIMIT ) / ( 1024*1024 ) );
	}

	/**
	 * @covers ::pods_get_symlink_dir
	 */
	public function test_get_symlink_url() {
		$this->assertEquals( 'http://example.org/wp-content/uploads/pods/symlinks', pods_get_symlink_url() );
	}

	public function test_use_skus() {
		$this->assertFalse( pods_use_skus() );
	}
}
