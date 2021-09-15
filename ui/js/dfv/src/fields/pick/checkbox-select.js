import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const CheckboxSelect = ( {
	htmlAttributes,
	name,
	value,
	options = [],
	setValue,
	isMulti,
	readOnly = false,
} ) => {
	const toggleValueOption = ( option ) => {
		if ( value.some( ( valueItem ) => valueItem.toString() === option.toString() ) ) {
			setValue(
				value.filter( ( item ) => item.toString() !== option.toString() )
			);
		} else {
			setValue( [ ...value, option ] );
		}
	};

	const totalOptions = options.length;

	return (
		<ul
			className={
				classnames(
					'pods-checkbox-pick',
					options.length === 1 && 'pods-checkbox-pick--single'
				)
			}
			id={ name }
		>
			{ options.map( (
				{
					id: optionValue,
					name: optionLabel,
				},
				optionIndex,
				allOptions
			) => {
				const nameBase = htmlAttributes.name || name;

				const nameAttribute = allOptions.length > 1
					? `${ nameBase }[${ optionIndex }]`
					: nameBase;

				let idAttribute = !! htmlAttributes.id ? htmlAttributes.id : `pods-form-ui-${ name }`;

				if ( 1 < totalOptions ) {
					idAttribute += `-${ optionValue }`;
				}

				return (
					<li
						key={ optionValue }
						className={
							classnames(
								'pods-checkbox-pick__option',
								options.length === 1 && 'pods-checkbox-pick__option--single'
							)
						}
					>
						<div className="pods-field pods-boolean">
							{ /* eslint-disable-next-line jsx-a11y/label-has-for */ }
							<label
								className="pods-form-ui-label pods-checkbox-pick__option__label"
							>
								<input
									name={ nameAttribute }
									id={ idAttribute }
									checked={
										isMulti
											? value.some( ( valueItem ) => valueItem.toString() === optionValue.toString() )
											: value === optionValue
									}
									className="pods-form-ui-field-type-pick"
									type="checkbox"
									value={ optionValue }
									onChange={ () => {
										if ( readOnly ) {
											return;
										}

										if ( isMulti ) {
											toggleValueOption( optionValue );
										} else {
											// Workaround for boolean fields:
											const unsetValue = ( 1 === options.length && optionValue === '1' )
												? '0'
												: undefined;

											setValue( value === optionValue ? unsetValue : optionValue );
										}
									} }
									readOnly={ !! readOnly }
								/>
								{ optionLabel }
							</label>
						</div>
					</li>
				);
			} ) }
		</ul>
	);
};

CheckboxSelect.propTypes = {
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
	isMulti: PropTypes.bool.isRequired,
	readOnly: PropTypes.bool,
};

export default CheckboxSelect;
