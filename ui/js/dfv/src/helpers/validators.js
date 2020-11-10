import { __, sprintf } from '@wordpress/i18n';

import {
	parseFloatWithPodsFormat,
	formatNumberWithPodsFormat,
	getThousandsSeparatorFromPodsFormat,
	getDecimalSeparatorFromPodsFormat,
} from 'dfv/src/helpers/formatNumberWithPodsFormat';

export const requiredValidator = ( fieldLabel ) => ( value ) => {
	if ( ! value ) {
		// translators: %s is the Field label of the required field.
		throw sprintf( __( '%s is required.', 'pods' ), fieldLabel );
	}

	return true;
};

export const maxValidator = ( maxValue ) => ( value ) => {
	if ( parseFloat( value ) > parseFloat( maxValue ) ) {
		// translators: %s is the maximum number value allowed.
		throw sprintf( __( 'Exceeds the maximum value of %s.', 'pods' ), maxValue );
	}

	return true;
};

export const minValidator = ( minValue ) => ( value ) => {
	if ( parseFloat( value ) < parseFloat( minValue ) ) {
		// translators: %s is the minimum number value allowed.
		throw sprintf( __( 'Below the minimum value of %s.', 'pods' ), minValue );
	}

	return true;
};

export const emailValidator = () => ( value ) => {
	const EMAIL_REGEX = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

	if ( ! value || ! value.match( EMAIL_REGEX ) ) {
		throw __( 'Invalid email address format.', 'pods' );
	}

	return true;
};

export const numberValidator = (
	digitMaxLength,
	decimalMaxLength,
	format,
) => ( value ) => {
	const formattedNumber = formatNumberWithPodsFormat( value, format, false );

	if ( ! formattedNumber ) {
		return true;
	}

	// Split apart the formatted string.
	const thousandsSeparator = getThousandsSeparatorFromPodsFormat( format );
	const dotSeparator = getDecimalSeparatorFromPodsFormat( format );

	const parts = formattedNumber.split( dotSeparator );

	// Check the number of digits
	const digits = parts[ 0 ].replace( new RegExp( thousandsSeparator, 'g' ), '' );

	const integerMaxDigits = parseInt( digitMaxLength, 10 ) || -1;

	if (
		-1 !== integerMaxDigits &&
		digits.length > integerMaxDigits
	) {
		throw __( 'Exceeded maximum digit length.', 'pods' );
	}

	// Check number of decimals
	const decimals = parts[ 1 ] || '';

	const integerMaxDecimals = parseInt( decimalMaxLength, 10 ) || -1;

	console.log( parts, parts[ 1 ], decimals, decimals.length, integerMaxDecimals );

	if (
		-1 !== integerMaxDecimals &&
		decimals.length > integerMaxDecimals
	) {
		throw __( 'Exceeded maximum decimal length.', 'pods' );
	}

	return true;
};
