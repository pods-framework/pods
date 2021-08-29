import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import { __ } from '@wordpress/i18n';

import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const SimpleSelect = ( {
	htmlAttributes,
	name,
	value,
	options,
	setValue,
	placeholder = __( '-- Select One --', 'pods' ),
	isMulti = false,
	readOnly = false,
} ) => {
	const classes = classnames(
		'pods-form-ui-field pods-form-ui-field-type-pick pods-form-ui-field-select',
		htmlAttributes.class
	);

	return (
		/* eslint-disable-next-line jsx-a11y/no-onchange */
		<select
			id={ htmlAttributes.id || `pods-form-ui-${ name }` }
			name={ htmlAttributes.name || name }
			className={ classes }
			value={ value || '' }
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
			readOnly={ !! readOnly }
		>
			<>
				{ ! isMulti && placeholder && (
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
	htmlAttributes: PropTypes.shape( {
		id: PropTypes.string,
		class: PropTypes.string,
		name: PropTypes.string,
	} ),
	name: PropTypes.string.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	setValue: PropTypes.func.isRequired,
	options: PICK_OPTIONS.isRequired,
	placeholder: PropTypes.string,
	isMulti: PropTypes.bool,
	readOnly: PropTypes.bool,
};

export default SimpleSelect;
