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
) => {
	// Turn empty string to 0.
	if ( '' === newValue ) {
		return 0;
	}

	// If we get a float value, we don't need to do anything.
	if ( 'number' === typeof newValue ) {
		return newValue;
	}

	const thousands = getThousandsSeparatorFromPodsFormat( format );
	const dot = getDecimalSeparatorFromPodsFormat( format );

	// Remove the thousands separators and change the decimal separator to a period,
	// so that parseFloat can handle the rest.
	return parseFloat(
		newValue.split( thousands ).join( '' ).split( dot ).join( '.' ),
	);
};

export const formatNumberWithPodsFormat = (
	newValue,
	format,
	trimZeroDecimals = false,
	decimalHandling = 'none',
	decimals = 'auto',
) => {
	// Skip empty strings or undefined.
	if ( '' === newValue || undefined === newValue || null === newValue ) {
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

	const formattedNumber = isNaN( floatNewValue )
		? undefined
		: formatNumber( floatNewValue, decimals, dotSeparator, thousands );

	// We may need to trim decimals
	if ( undefined === formattedNumber || ( ! trimZeroDecimals && 'none' === decimalHandling ) ) {
		return formattedNumber;
	}

	const formattedNumberParts = formattedNumber.split( dotSeparator );

	const decimalValue = 1 < formattedNumberParts.length ? parseInt( formattedNumberParts[ 1 ], 10 ) : 0;

	const isZero = 0 === parseInt( formattedNumberParts[ 0 ], 10 );
	const isDecimalZero = 0 === decimalValue;

	if ( ! isDecimalZero ) {
		return formattedNumber;
	}

	switch ( decimalHandling ) {
		case 'remove':
		case 'dash':
			return formattedNumberParts[ 0 ] ?? '0';

		case 'remove_only_zero':
		case 'dash_only_zero':
		case 'dash_whole_zero':
			if ( isZero ) {
				return formattedNumberParts[ 0 ] ?? '0';
			}
			break;

		default:
			return formattedNumber;
	}

	return formattedNumber;
};
