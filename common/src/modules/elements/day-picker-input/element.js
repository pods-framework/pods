/**
 * External dependencies
 */
import React from 'react';
import classNames from 'classnames';
import 'react-day-picker/lib/style.css';
import ReactDayPickerInput from 'react-day-picker/DayPickerInput';

/**
 * Internal dependencies
 */
import './style.pcss';

const DayPickerInput = ( props ) => (
	<ReactDayPickerInput
		classNames={ {
			container: classNames(
				'tribe-editor__day-picker-input',
				'DayPickerInput',
			),
			overlayWrapper: classNames(
				'tribe-editor__day-picker-input__overlay-wrapper',
				'DayPickerInput-OverlayWrapper',
			),
			overlay: classNames(
				'tribe-editor__day-picker-input__overlay',
				'DayPickerInput-Overlay',
			),
		} }
		{ ...props }
	/>
);

export default DayPickerInput;
