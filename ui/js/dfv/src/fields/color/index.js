import React from 'react';
import PropTypes from 'prop-types';

import { __ } from '@wordpress/i18n';
import { ColorIndicator, ColorPicker, Dropdown, Button } from '@wordpress/components';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './color.scss';

const Color = ( {
	fieldConfig,
	setValue,
	value,
	setHasBlurred,
} ) => {
	const {
		name,
		color_select_label: selectLabel = __( 'Select Color', 'pods' ),
		color_clear_label: clearLabel = __( 'Clear', 'pods' ),
		readonly: readOnly,
	} = fieldConfig;

	return (
		<div className="pods-color-buttons">
			<input
				name={ name }
				type="hidden"
				value={ value || '' }
			/>

			{ readOnly ? (
				<div className="pods-color-buttons__buttons pods-color-buttons__buttons--disabled">
					<ColorIndicator colorValue={ value || '' } />

					<span className="pods-color-buttons__value">
						{ value }
					</span>
				</div>

			) : (
				<Dropdown
					renderToggle={ ( state ) => (
						<div className="pods-color-buttons__buttons">
							<Button
								onClick={ state.onToggle }
								className="button pods-color-select-button"
							>
								<ColorIndicator colorValue={ value || '' } />

								{ selectLabel }
							</Button>

							{ value ? (
								<Button
									onClick={ ( event ) => {
										event.preventDefault();
										setValue( '' );
									} }
									className="button"
								>
									{ clearLabel }
								</Button>
							) : null }
						</div>
					) }
					renderContent={ () => (
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
