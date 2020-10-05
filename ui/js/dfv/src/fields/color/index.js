import React, { useState } from 'react';
import PropTypes from 'prop-types';

import { __ } from '@wordpress/i18n';
import { ColorIndicator, ColorPicker } from '@wordpress/components';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './color.scss';

const Color = ( {
	setValue,
	value,
} ) => {
	const [ isOpen, setIsOpen ] = useState( false );

	return (
		<div>
			<button
				onClick={ () => setIsOpen( ( prevValue ) => ! prevValue ) }
				className="button pods-color-select-button"
			>
				<ColorIndicator colorValue={ value } />

				{ __( 'Select Color', 'pods' ) }
			</button>

			{ isOpen && (
				<ColorPicker
					color={ value }
					onChangeComplete={ ( newValue ) => setValue( newValue.hex ) }
					disableAlpha
					className="pods-color-picker"
				/>
			) }
		</div>
	);
};

Color.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Color;
