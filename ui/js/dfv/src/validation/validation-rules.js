const { __, sprintf } = wp.i18n;

export const validationRules = {
	required: ( value, fieldLabel ) => {
		return {
			facts: { value },
			conditions: { value: { equal: '' } },
			event: {
				/** translators: %s is the field label */
				message: sprintf( __( '%s is required.', 'pods' ), fieldLabel ),
			},
		};
	},

	max: ( value, max ) => {
		return {
			facts: { numericValue: value * 1, max: max * 1 },
			conditions: { numericValue: { greater: '$max' } },
			event: {
				/** translators: %s is the maximum number allowed */
				message: sprintf( __( 'Exceeds the maximum value of %s', 'pods' ), max ),
			},

		};
	},

	min: ( value, min ) => {
		return {
			facts: { numericValue: value * 1, min: min * 1 },
			conditions: { numericValue: { less: '$min' } },
			event: {
				/** translators: %s is the minimum number allowed */
				message: sprintf( __( 'Below the minimum value of %s', 'pods' ), min ),
			},

		};
	},

	emailFormat: ( value ) => {
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
				message: __( 'Invalid email address format' ),
			},
		};
	},
};
