import React from 'react';
import PropTypes from 'prop-types';

import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const RadioSelect = ( {
	name,
	value,
	options,
	setValue,
} ) => {
	return (
		<ul className="pods-radio-pick" id={ name }>
			{ options.map( ( {
				value: optionValue,
				label: optionLabel,
			} ) => {
				return (
					<li key={ optionValue } className="pods-radio-pick__option">
						<div className="pods-field pods-boolean">
							<label
								className="pods-form-ui-label"
								htmlFor={ `pods-${ name }-${ optionLabel }` }
							>
								<input
									name={ `pods-${ name }-${ optionLabel }` }
									id={ `pods-${ name }-${ optionLabel }` }
									checked={ value === optionValue }
									className="pods-form-ui-field-type-pick"
									type="radio"
									value={ optionValue }
									onChange={ ( event ) => {
										if ( event.target.checked ) {
											setValue( event.target.value );
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

RadioSelect.propTypes = {
	name: PropTypes.string.isRequired,
	value: PropTypes.string,
	setValue: PropTypes.func.isRequired,
	options: PICK_OPTIONS.isRequired,
};

export default RadioSelect;
