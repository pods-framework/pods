import formatNumber from 'dfv/src/helpers/formatNumber';

const formatNumericString = (
	newValue,
	decimals,
	format
) => {
	// Only handle string values.
	if ( 'string' !== typeof newValue || '' === newValue ) {
		return undefined;
	}

	// eslint-disable-next-line camelcase
	const numberFormatOptions = window?.podsDFVConfig?.wp_locale?.number_format || {};

	let {
		thousands_sep: thousands,
		decimal_point: dot,
	} = numberFormatOptions;

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

	// Remove the thousands separators and change the decimal separator to a period,
	// so that parseFloat can handle the rest.
	const floatNewValue = parseFloat(
		newValue.replace( thousands, '' ).replace( dot, '.' )
	);

	return isNaN( floatNewValue )
		? undefined
		: formatNumber( floatNewValue, decimals, dot, thousands );
};

export default formatNumericString;
