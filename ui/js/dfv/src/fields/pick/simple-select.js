import React from 'react';
import PropTypes from 'prop-types';

import { __ } from '@wordpress/i18n';

import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const SimpleSelect = ( {
	name,
	value,
	options,
	setValue,
	placeholder = __( '-- Select One --', 'pods' ),
	isMulti = false,
} ) => {
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

				{ options.map( ( { label: optionLabel, value: optionValue } ) => {
					if ( 'string' === typeof optionValue ) {
						return (
							<option key={ optionValue } value={ optionValue }>
								{ optionLabel }
							</option>
						);
					} else if ( Array.isArray( optionValue ) ) {
						return (
							<optgroup label={ optionLabel } key={ optionLabel }>
								{ optionValue.map( ( { value: suboptionValue, label: suboptionLabel } ) => {
									return (
										<option key={ suboptionValue } value={ suboptionValue }>
											{ suboptionLabel }
										</option>
									);
								} ) }
							</optgroup>
						);
					} else if ( 'object' === typeof optionValue ) {
						const optgroupOptions = Object.entries( optionValue );

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
	options: PICK_OPTIONS.isRequired,
	placeholder: PropTypes.string,
	isMulti: PropTypes.bool,
};

export default SimpleSelect;
