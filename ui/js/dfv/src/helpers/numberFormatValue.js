import numberFormat from 'dfv/src/helpers/numberFormat';

const numberFormatValue = (
	newValue,
	decimals,
	format
) => {
	if ( 'undefined' === typeof newValue ) {
		newValue = 0;
	}

	// @todo use the config from podsDFVConfig.wp_locale.number_format.thousands_sep
	let thousands = ',';
	// @todo use the config from podsDFVConfig.wp_locale.number_format.decimal_point
	let dot = '.';

	switch ( format ) {
		case '9,999.99':
			thousands = ',';
			dot = '.';
			break;
		case '9999.99':
			thousands = '';
			dot = '.';
			break;
		case '9.999,99':
			thousands = '.';
			dot = ',';
			break;
		case '9999,99':
			thousands = '';
			dot = ',';
			break;
		case "9'999.99":
			thousands = '\'';
			dot = '.';
			break;
		case '9 999,99':
			thousands = ' ';
			dot = ',';
			break;
		case 'i18n':
		// fall through to default
		default:
			break;
	}

	return numberFormat( parseFloat( newValue ), decimals, dot, thousands );
};

export default numberFormatValue;
