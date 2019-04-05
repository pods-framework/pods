const { __, sprintf } = wp.i18n;

export const validationRules = {
	'required': ( value, fieldLabel ) => {
		return {
			params: { value: value },
			conditions: { value: { equal: '' } },
			event: {
				message: sprintf( __( '%s is required.', 'pods' ), fieldLabel )
			}
		};
	},

	'max': ( value, max ) => {
		return {
			params: { numericValue: value * 1, max: max * 1 },
			conditions: { numericValue: { greater: '$max' } },
			event: {
				message: sprintf( __( 'Exceeds the maximum value of %s', 'pods' ), max )
			}

		};
	},

	'min': ( value, min ) => {
		return {
			params: { numericValue: value * 1, min: min * 1 },
			conditions: { numericValue: { less: '$min' } },
			event: {
				message: sprintf( __( 'Below the minimum value of %s', 'pods' ), min )
			}

		};
	}
};
