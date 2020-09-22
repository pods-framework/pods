import React from 'react';
import PropTypes from 'prop-types';

import { __ } from '@wordpress/i18n';

const SimpleSelect = ( {
	name,
	value,
	options,
	setValue,
	placeholder = __( '-- Select One --', 'pods' ),
	isMulti = false,
} ) => {
	// This custom sorting function is necessary because
	// JS will change the ordering of the keys in the object
	// if one of them is an empty string (which we may want as
	// the placeholder value), and will send the empty string
	// to the end of the object when it is enumerated.
	//
	const optionKeys = Object.keys( options )
		// eslint-disable-next-line no-unused-vars
		.sort( ( a, b ) => a[ 0 ] === '' ? -1 : 0 );

	return (
		/* eslint-disable-next-line jsx-a11y/no-onchange */
		<select
			id={ name }
			name={ name }
			value={ value }
			onChange={ ( event ) => {
				if ( ! isMulti ) {
					setValue( event.target.value );
					return;
				}

				setValue(
					Array.from( event.target.options )
						.filter( ( option ) => option.selected )
						.map( ( option ) => option.value )
				);
			} }
			multiple={ isMulti }
		>
			<>
				{ ! isMulti && (
					<option key="placeholder" value="">
						{ placeholder }
					</option>
				) }

				{ optionKeys.map( ( optionValue ) => {
					const option = options[ optionValue ];

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
			</>
		</select>
	);
};

SimpleSelect.propTypes = {
	name: PropTypes.string.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	setValue: PropTypes.func.isRequired,
	options: PropTypes.object.isRequired,
	placeholder: PropTypes.string,
	isMulti: PropTypes.bool,
};

export default SimpleSelect;
