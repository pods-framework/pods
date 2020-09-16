import React from 'react';
import PropTypes from 'prop-types';

const RadioSelect = ( {
	name,
	value,
	options,
	setValue,
} ) => {
	return (
		<ul className="pods-radio-pick" id={ name }>
			{ Object.keys( options ).map( ( optionValue ) => {
				const option = options[ optionValue ];

				return (
					<li key={ optionValue } className="pods-radio-pick__option">
						<div className="pods-field pods-boolean">
							<label
								className="pods-form-ui-label"
								htmlFor={ `pods-${ name }-${ option }` }
							>
								<input
									id={ `pods-${ name }-${ option }` }
									checked={ value === optionValue }
									className="pods-form-ui-field-type-pick"
									type="radio"
									value={ optionValue }
									onChange={ ( event ) => {
										if ( event.target.checked ) {
											setValue( optionValue );
										}
									} }
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

RadioSelect.propTypes = {
	name: PropTypes.string.isRequired,
	value: PropTypes.string,
	setValue: PropTypes.func.isRequired,
	options: PropTypes.object.isRequired,
};

export default RadioSelect;
