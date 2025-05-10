import formatNumber from 'dfv/src/helpers/formatNumber';

export const getThousandsSeparatorFromPodsFormat = ( format ) => {
	// eslint-disable-next-line camelcase
	const numberFormatOptions = window?.podsDFVConfig?.wp_locale?.number_format || {};

	let { thousands_sep: thousands } = numberFormatOptions;

	switch ( format ) {
		case '9,999.99':
			thousands = ',';
			break;
		case '9999.99':
			thousands = '';
			break;
		case '9.999,99':
			thousands = '.';
			break;
		case '9999,99':
			thousands = '';
			break;
		case '9\'999.99':
			thousands = '\'';
			break;
		case '9 999,99':
			thousands = ' ';
			break;
		case 'i18n':
		// fall through to default
		default:
			break;
	}

	return thousands;
};

export const getDecimalSeparatorFromPodsFormat = ( format ) => {
	// eslint-disable-next-line camelcase
	const numberFormatOptions = window?.podsDFVConfig?.wp_locale?.number_format || {};

	let { decimal_point: dot } = numberFormatOptions;

	switch ( format ) {
		case '9,999.99':
			dot = '.';
			break;
		case '9999.99':
			dot = '.';
			break;
		case '9.999,99':
			dot = ',';
			break;
		case '9999,99':
			dot = ',';
			break;
		case '9\'999.99':
			dot = '.';
			break;
		case '9 999,99':
			dot = ',';
			break;
		case 'i18n':
		// fall through to default
		default:
			break;
	}

	return dot;
};

export const parseFloatWithPodsFormat = (
	newValue,
	format,
	keepLeadingZeroes = false,
) => {
	// Turn empty string to 0.
	if ( '' === newValue || undefined === newValue || null === newValue || false === newValue ) {
		return 0;
	}

	// If we get a float value, we don't need to do anything.
	if ( 'number' === typeof newValue ) {
		return newValue;
	}

	let prefix = null;

	if ( keepLeadingZeroes ) {
		prefix = ( newValue.match( /^0+/ ) || [ '' ] )[ 0 ];
	}

	const thousands = getThousandsSeparatorFromPodsFormat( format );
	const dot = getDecimalSeparatorFromPodsFormat( format );

	// Remove the thousands separators and change the decimal separator to a period,
	// so that parseFloat can handle the rest.
	let formattedValue = parseFloat(
		newValue.split( thousands ).join( '' ).split( dot ).join( '.' ),
	);

	if ( keepLeadingZeroes && prefix ) {
		formattedValue = prefix + formattedValue.toString();
	}

	return formattedValue;
};

export const formatNumberWithPodsFormat = (
	newValue,
	format,
	trimZeroDecimals = false,
	keepLeadingZeroes = false,
) => {
	// Skip empty strings or undefined.
	if ( '' === newValue || undefined === newValue || null === newValue || false === newValue ) {
		return '0';
	}

	const thousands = getThousandsSeparatorFromPodsFormat( format );
	const dotSeparator = getDecimalSeparatorFromPodsFormat( format );

	// A string has to be parsed, but a float does not.
	const floatNewValue = (
		'string' === typeof newValue
	)
		? parseFloatWithPodsFormat( newValue, format )
		: newValue;

	if ( isNaN( floatNewValue ) ) {
		return undefined;
	}

	let formattedNumber = formatNumber( floatNewValue, 'auto', dotSeparator, thousands );
	let prefix = null;

	if ( keepLeadingZeroes ) {
		prefix = ( newValue.match( /^0+/ ) || [ '' ] )[ 0 ];
	}

	if ( keepLeadingZeroes && prefix ) {
		formattedNumber = prefix + formattedNumber.toString();
	}

	// We may need to trim decimals
	if ( ! trimZeroDecimals ) {
		return formattedNumber;
	}

	const decimalValue = parseInt( formattedNumber.split( dotSeparator ).pop(), 10 );

	// Don't cut off an actual decimal value.
	if ( 0 !== decimalValue ) {
		return formattedNumber;
	}

	const charactersToTrim = -1 * (
		parseInt( (
			'' + decimalValue
		).length, 10 ) + 1
	);

	return formattedNumber.slice( 0, charactersToTrim );
};
