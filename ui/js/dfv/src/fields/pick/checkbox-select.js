import React from 'react';
import PropTypes from 'prop-types';

const CheckboxSelect = ( {
	name,
	value = [],
	options,
	setValue,
} ) => {
	const toggleValueOption = ( option ) => {
		if ( value.includes( option ) ) {
			setValue( value.filter( ( item ) => item !== option ) );
		}

		setValue( [ ...value, option ] );
	};

	return (
		<ul className="pods-checkbox-pick" id={ name }>
			{ Object.keys( options ).map( ( optionValue ) => {
				const option = options[ optionValue ];

				return (
					<li key={ optionValue } className="pods-checkbox-pick__option">
						<div className="pods-field pods-boolean">
							<label
								className="pods-form-ui-label"
								htmlFor={ `pods-${ name }-${ option }` }
							>
								<input
									id={ `pods-${ name }-${ option }` }
									checked={ value.includes( optionValue ) }
									className="pods-form-ui-field-type-pick"
									type="checkbox"
									value={ optionValue }
									onChange={ () => toggleValueOption( optionValue ) }
								/>
								{ option }
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
	value: PropTypes.arrayOf( PropTypes.string ),
	setValue: PropTypes.func.isRequired,
	options: PropTypes.object.isRequired,
};

export default CheckboxSelect;
