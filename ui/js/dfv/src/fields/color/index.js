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
						setValue( newValue.hex );
						setHasBlurred();
					} }
					disableAlpha
					className="pods-color-picker"
				/>
			) }
		</div>
	);
};

Color.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Color;
