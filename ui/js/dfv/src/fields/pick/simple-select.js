import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';
import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const SimpleSelect = ( {
	htmlAttributes,
	name,
	value,
	options,
	setValue,
	isMulti = false,
	readOnly = false,
} ) => {
	const classes = classnames(
		'pods-form-ui-field pods-form-ui-field-type-pick pods-form-ui-field-select',
		htmlAttributes.class
	);

	let htmlName = htmlAttributes.name || name;

	// Maybe add [] to the multiple select field.
	if ( isMulti ) {
		htmlName += '[]';
	}

	return (
		/* eslint-disable-next-line jsx-a11y/no-onchange */
		<select
			id={ htmlAttributes.id || `pods-form-ui-${ name }` }
			name={ htmlName }
			className={ classes }
			value={ value || ( isMulti ? [] : '' ) }
			onChange={ ( event ) => {
				if ( readOnly ) {
					return;
				}

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
			readOnly={ toBool( readOnly ) }
		>
			<>
				{ options.map( ( { name: optionLabel, id: optionValue } ) => {
					if ( 'string' === typeof optionValue || 'number' === typeof optionValue ) {
						return (
							<option key={ optionValue } value={ optionValue }>
								{ optionLabel }
							</option>
						);
					} else if ( Array.isArray( optionValue ) ) {
						return (
							<optgroup label={ optionLabel } key={ optionLabel }>
								{ optionValue.map( ( { id: suboptionValue, name: suboptionLabel } ) => {
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
	htmlAttributes: PropTypes.shape( {
		id: PropTypes.string,
		class: PropTypes.string,
		name: PropTypes.string,
	} ),
	name: PropTypes.string.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.arrayOf(
			PropTypes.oneOfType( [
				PropTypes.string,
				PropTypes.number,
			] )
		),
		PropTypes.string,
		PropTypes.number,
	] ),
	setValue: PropTypes.func.isRequired,
	options: PICK_OPTIONS.isRequired,
	isMulti: PropTypes.bool,
	readOnly: PropTypes.bool,
};

export default SimpleSelect;
