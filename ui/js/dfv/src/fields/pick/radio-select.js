import React from 'react';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';
import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const RadioSelect = ( {
	htmlAttributes,
	name,
	value,
	options,
	setValue,
	readOnly = false,
} ) => {
	return (
		<ul className="pods-radio-pick" id={ name }>
			{ options.map( ( {
				id: optionValue,
				name: optionLabel,
			} ) => {
				const idAttribute = !! htmlAttributes.id
					? `${ htmlAttributes.id }-${ optionValue }`
					: `${ name }-${ optionValue }`;

				return (
					<li key={ optionValue } className="pods-radio-pick__option">
						<div className="pods-field pods-boolean">
							{ /* eslint-disable-next-line jsx-a11y/label-has-for */ }
							<label
								className="pods-form-ui-label pods-radio-pick__option__label"
							>
								<input
									name={ htmlAttributes.name || name }
									id={ idAttribute }
									checked={ value.toString() === optionValue.toString() }
									className="pods-form-ui-field-type-pick"
									type="radio"
									value={ optionValue }
									onChange={ ( event ) => {
										if ( toBool( readOnly ) ) {
											return;
										}

										if ( event.target.checked ) {
											setValue( event.target.value );
										}
									} }
									readOnly={ toBool( readOnly ) }
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

RadioSelect.propTypes = {
	htmlAttributes: PropTypes.shape( {
		id: PropTypes.string,
		class: PropTypes.string,
		name: PropTypes.string,
	} ),
	name: PropTypes.string.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ),
	setValue: PropTypes.func.isRequired,
	options: PICK_OPTIONS.isRequired,
	readOnly: PropTypes.bool,
};

export default RadioSelect;
