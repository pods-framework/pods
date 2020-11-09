import formatNumber from 'dfv/src/helpers/formatNumber';

export const parseFloatWithPodsFormat = (
	newValue,
	format
) => {
	// Turn empty string to 0.
	if ( '' === newValue ) {
		return 0;
	}

	// If we get a float value, we don't need to do anything.
	if ( 'number' === typeof newValue ) {
		return newValue;
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
	return parseFloat(
		newValue.replace( thousands, '' ).replace( dot, '.' )
	);
};

export const formatNumberWithPodsFormat = (
	newValue,
	decimals,
	format,
	trimZeroDecimals = false,
) => {
	// Skip empty strings or undefined.
	if ( '' === newValue || undefined === newValue || null === newValue ) {
		return '0';
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

	// A string has to be parsed, but a float does not.
	const floatNewValue = ( 'string' === typeof newValue )
		? parseFloatWithPodsFormat( newValue, format )
		: newValue;

	const formattedNumber = isNaN( floatNewValue )
		? undefined
		: formatNumber( floatNewValue, decimals, dot, thousands );

	// We may need to trim decimals
	if ( ! trimZeroDecimals || 0 === decimals || undefined === formattedNumber ) {
		return formattedNumber;
	}

	const decimalValue = parseInt( formattedNumber.split( ',' ).pop(), 10 );

	// Don't cut off an actual decimal value.
	if ( 0 !== decimalValue ) {
		return formattedNumber;
	}

	const charactersToTrim = -1 * ( parseInt( decimals, 10 ) + 1 );

	return formattedNumber.slice( 0, charactersToTrim );
};
