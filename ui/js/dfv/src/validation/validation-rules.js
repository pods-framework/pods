import { __, sprintf } from '@wordpress/i18n';

export const required = ( value, fieldLabel ) => {
	return {
		facts: { value },
		conditions: { value: { equal: '' } },
		event: {
			// translators: Field label required message.
			message: sprintf( __( '%s is required.', 'pods' ), fieldLabel ),
		},
	};
};

export const max = ( value, maxValue ) => {
	return {
		facts: { numericValue: value * 1, max: maxValue * 1 },
		conditions: { numericValue: { greater: '$max' } },
		event: {
			// translators: Exceeds a maximum value.
			message: sprintf( __( 'Exceeds the maximum value of %s', 'pods' ), maxValue ),
		},
	};
};

export const min = ( value, minValue ) => {
	return {
		facts: { numericValue: value * 1, min: minValue * 1 },
		conditions: { numericValue: { less: '$min' } },
		event: {
			// translators: Below a minimum value.
			message: sprintf( __( 'Below the minimum value of %s', 'pods' ), minValue ),
		},
	};
};

export const emailFormat = ( value ) => {
	const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

	return {
		facts: { value, emailRegex },
		conditions: {
			not: {
				or: [
					{ value: { equal: '' } },
					{ emailRegex: { matches: value } },
				],
			},
		},
		event: {
			message: __( 'Invalid email address format', 'pods' ),
		},
	};
};
