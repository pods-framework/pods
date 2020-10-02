import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const CheckboxSelect = ( {
	name,
	value,
	options = [],
	setValue,
	isMulti,
} ) => {
	const toggleValueOption = ( option ) => {
		if ( value.includes( option ) ) {
			setValue( value.filter( ( item ) => item !== option ) );
		} else {
			setValue( [ ...value, option ] );
		}
	};

	return (
		<ul
			className={ classnames( 'pods-checkbox-pick', options.length === 1 && 'pods-checkbox-pick--single' ) }
			id={ name }
		>
			{ options.map( ( {
				value: optionValue,
				label: optionLabel,
			} ) => {
				return (
					<li
						key={ optionValue }
						className={ classnames( 'pods-checkbox-pick__option', options.length === 1 && 'pods-checkbox-pick__option--single' ) }
					>
						<div className="pods-field pods-boolean">
							<label
								className="pods-form-ui-label pods-checkbox-pick__option__label"
								htmlFor={ `pods-${ name }-${ optionLabel }` }
							>
								<input
									id={ `pods-${ name }-${ optionLabel }` }
									checked={ isMulti ? value.includes( optionValue ) : value === optionValue }
									className="pods-form-ui-field-type-pick"
									type="checkbox"
									value={ optionValue }
									onChange={ () => {
										if ( isMulti ) {
											console.log( 'multi checkbox, setting ', optionValue );
											toggleValueOption( optionValue );
										} else {
											setValue( value === optionValue ? undefined : optionValue );
										}
									} }
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
	name: PropTypes.string.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.string ),
		PropTypes.string,
	] ),
	setValue: PropTypes.func.isRequired,
	options: PICK_OPTIONS.isRequired,
	isMulti: PropTypes.bool.isRequired,
};

export default CheckboxSelect;
