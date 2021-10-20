import React, { useState } from 'react';
import PropTypes from 'prop-types';

import { __ } from '@wordpress/i18n';
import { ColorIndicator, ColorPicker } from '@wordpress/components';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './color.scss';

const Color = ( {
	fieldConfig,
	setValue,
	value,
	enableAlpha,
	setHasBlurred,
} ) => {
	const { name } = fieldConfig;

	const [ isOpen, setIsOpen ] = useState( false );

	return (
		<div>
			<input
				name={ name }
				type="hidden"
				value={ value || '' }
			/>

			<button
				onClick={ ( event ) => {
					event.preventDefault();
					setIsOpen( ( prevValue ) => ! prevValue );
				} }
				className="button pods-color-select-button"
			>
				<ColorIndicator colorValue={ value || '' } />

				{ __( 'Select Color', 'pods' ) }
			</button>

			{ isOpen && (
				<ColorPicker
					color={ value }
					onChangeComplete={ ( newValue ) => {

						newValue.hexa = newValue.hex;
						if ( 1 > newValue.color._a ) {
							newValue.hexa = Math.round( newValue.color._a * 255 ).toString(16);
							if ( 2 > newValue.hexa.length ) {
								newValue.hexa = '0' + newValue.hexa;
							}
							newValue.hexa = newValue.hex + newValue.hexa;
						}

						setValue( newValue.hexa );
						setHasBlurred();
					} }
					enableAlpha={ enableAlpha }
					className="pods-color-picker"
				/>
			) }
		</div>
	);
};

Color.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
	enableAlpha: PropTypes.bool,
};

export default Color;
