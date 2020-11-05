import React from 'react';
import Datetime from 'react-datetime';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';
import {
	convertPHPDateFormatToMomentFormat,
	convertjQueryUIDateFormatToMomentFormat,
	convertjQueryUITimeFormatToMomentFormat,
	convertPodsDateFormatToMomentFormat,
} from 'dfv/src/helpers/dateFormats';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import 'react-datetime/css/react-datetime.css';

const checkForHTML5BrowserSupport = ( fieldType ) => {
	const input = document.createElement( 'input' );
	input.setAttribute( 'type', fieldType );
	input.setAttribute( 'value', 'not-a-date' );

	return ( input.value !== 'not-a-date' );
};

const DateTime = ( props ) => {
	const {
		value,
		setValue,
		fieldConfig = {},
	} = props;

	const {
		name,
		type = 'datetime', // 'datetime', 'time', or 'date'
		// datetime_allow_empty: allowEmpty,
		datetime_format: podsFormat,
		datetime_format_custom: formatCustom,
		datetime_format_custom_js: formatCustomJS,
		datetime_html5: html5,
		datetime_time_format: podsTimeFormat,
		datetime_time_format_24: podsTimeFormat24,
		datetime_time_format_custom: timeFormatCustom,
		datetime_time_format_custom_js: timeFormatCustomJS,
		datetime_time_type: timeFormatType = 'wp', // 'wp, '12', '24', or 'custom'
		datetime_type: dateFormatType = 'wp', // 'wp', 'format', or 'custom'
		// datetime_year_range_custom: yearRangeCustom,
	} = fieldConfig;

	const handleInputFieldChange = ( event ) => setValue( event.target.value );

	const handleChange = ( newValue ) => {
		// Receives the selected moment object, if the date in the input is valid.
		// If the date in the input is not valid, the callback receives the value of
		// the input a string.
		setValue(
			'string' === typeof momentValue
				? newValue
				: newValue.format( 'dddd, MMMM Do YYYY, h:mm:ss a' )
		);
	};

	const includeTimeField = 'datetime' === type || 'time' === type;
	const includeDateField = 'datetime' === type || 'date' === type;

	const useHTML5Field = toBool( html5 ) && checkForHTML5BrowserSupport( 'datetime-local' );

	// Determine date and time formats.
	// @todo Replace default with window.podsAdminConfig.datetime.date_format
	let momentDateFormat = convertPHPDateFormatToMomentFormat( 'F j, Y' );

	switch ( dateFormatType ) {
		case 'format':
			momentDateFormat = convertPodsDateFormatToMomentFormat( podsFormat );
			break;
		case 'custom':
			momentDateFormat = ( !! formatCustomJS )
				? convertjQueryUIDateFormatToMomentFormat( formatCustomJS )
				: convertPHPDateFormatToMomentFormat( formatCustom );
			break;
		case 'wp':
		default:
			break;
	}

	// @todo Replace default with window.podsAdminConfig.datetime.time_format
	let momentTimeFormat = convertPHPDateFormatToMomentFormat( 'g:i a' );

	switch ( timeFormatType ) {
		case '12':
			momentTimeFormat = convertPodsDateFormatToMomentFormat( podsTimeFormat );
			break;
		case '24':
			momentTimeFormat = convertPodsDateFormatToMomentFormat( podsTimeFormat24 );
			break;
		case 'custom':
			momentTimeFormat = ( !! timeFormatCustomJS )
				? convertjQueryUITimeFormatToMomentFormat( timeFormatCustomJS )
				: convertPHPDateFormatToMomentFormat( timeFormatCustom );
			break;
		case 'wp':
		default:
			break;
	}

	// If we can use an HTML5 input field, we can just return an input field.
	if ( useHTML5Field ) {
		return (
			<input
				name={ name }
				id={ `pods-form-ui-${ name }` }
				className="pods-form-ui-field pods-form-ui-field-type-datetime"
				type="datetime-local"
				value={ value }
				onChange={ handleInputFieldChange }
			/>
		);
	}

	return (
		<>
			<Datetime
				value={ value }
				onChange={ handleChange }
				dateFormat={ includeDateField && momentDateFormat }
				timeFormat={ includeTimeField && momentTimeFormat }
			/>
			{ `TESTING:\n date format: ${ momentDateFormat }` }
			<br />
			{ `TESTING:\n time format: ${ momentTimeFormat }` }
		</>
	);
};

DateTime.defaultProps = {
	value: '',
};

DateTime.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default DateTime;
