import React from 'react';
import PropTypes from 'prop-types';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

// @todo this may be an incomplete field component
// @todo move the bidirectional logic here?
// @todo add tests
const Pick = ( props ) => {
	const {
		fieldConfig: {
			data = [],
		},
		setValue,
		value,
	} = props;

	// Default implementation if onChange is omitted from props
	const handleChange = ( event ) => setValue( event.target.value );

	return (
		/* eslint-disable-next-line jsx-a11y/no-onchange */
		<select
			id={ name }
			name={ name }
			value={ value }
			onChange={ handleChange }
		>
			{ Object.keys( data )
				// This custom sorting function is necessary because
				// JS will change the ordering of the keys in the object
				// if one of them is an empty string (which we may want as
				// the placeholder value), and will send the empty string
				// to the end of the object when it is enumerated.
				//
				// eslint-disable-next-line no-unused-vars
				.sort( ( a, b ) => a === '' ? -1 : 0 )
				.map( ( optionValue ) => {
					const option = data[ optionValue ];

					if ( 'string' === typeof option ) {
						return (
							<option key={ optionValue } value={ optionValue }>
								{ option }
							</option>
						);
					} else if ( 'object' === typeof option ) {
						const optgroupOptions = Object.entries( option );

						return (
							<optgroup label={ optionValue } key={ optionValue }>
								{ optgroupOptions.map( ( [ suboptionValue, suboptionLabel ] ) => {
									return (
										<option key={ suboptionValue } value={ suboptionValue }>
											{ suboptionLabel }
										</option>
									);
								} ) }
							</optgroup>
						);
					}
					return null;
				} ) }
		</select>
	);
};

Pick.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Pick;
