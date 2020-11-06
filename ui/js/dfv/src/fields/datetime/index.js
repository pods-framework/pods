import React, { useMemo } from 'react';
import Datetime from 'react-datetime';
import moment from 'moment';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';
import {
	convertPHPDateFormatToMomentFormat,
	convertjQueryUIDateFormatToMomentFormat,
	convertjQueryUITimeFormatToMomentFormat,
	convertPodsDateFormatToMomentFormat,
	getArrayOfYearsFromJqueryUIYearRange,
} from 'dfv/src/helpers/dateFormats';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import 'react-datetime/css/react-datetime.css';

const checkForHTML5BrowserSupport = ( fieldType ) => {
	const input = document.createElement( 'input' );
	input.setAttribute( 'type', fieldType );
	input.setAttribute( 'value', 'not-a-date' );

	return ( input.value !== 'not-a-date' );
};

// Determine date and time formats based on the field's config values.
const getMomentDateFormat = ( formatType, podsFormat, formatCustomJS, formatCustom ) => {
	// @todo Replace default with window.podsAdminConfig.datetime.date_format
	let format = convertPHPDateFormatToMomentFormat( 'F j, Y' );

	switch ( formatType ) {
		case 'format':
			format = convertPodsDateFormatToMomentFormat( podsFormat );
			break;
		case 'custom':
			format = ( !! formatCustomJS )
				? convertjQueryUIDateFormatToMomentFormat( formatCustomJS )
				: convertPHPDateFormatToMomentFormat( formatCustom );
			break;
		case 'wp':
		default:
			break;
	}

	return format;
};

const getMomentTimeFormat = ( timeFormatType, podsTimeFormat, podsTimeFormat24, timeFormatCustomJS, timeFormatCustom ) => {
	// @todo Replace default with window.podsAdminConfig.datetime.time_format
	let format = convertPHPDateFormatToMomentFormat( 'g:i a' );

	switch ( timeFormatType ) {
		case '12':
			format = convertPodsDateFormatToMomentFormat( podsTimeFormat );
			break;
		case '24':
			format = convertPodsDateFormatToMomentFormat( podsTimeFormat24 );
			break;
		case 'custom':
			format = ( !! timeFormatCustomJS )
				? convertjQueryUITimeFormatToMomentFormat( timeFormatCustomJS )
				: convertPHPDateFormatToMomentFormat( timeFormatCustom );
			break;
		case 'wp':
		default:
			break;
	}

	return format;
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
		datetime_year_range_custom: yearRangeCustom,
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

	const yearRange = useMemo(
		() => getArrayOfYearsFromJqueryUIYearRange(
			yearRangeCustom,
			new Date().getFullYear(),
			new Date( value ).getFullYear()
		),
		[ yearRangeCustom, value ]
	);

	// Set the inital view date to the current date, unless the range of years is before
	// the current time.
	const initialViewDate = ( yearRange && yearRange[ yearRange.length - 1 ] < new Date().getFullYear() )
		? new Date( yearRange[ 0 ], 0, 1 )
		: new Date();

	const isValidDate = ( current ) => {
		if ( 'undefined' === typeof yearRange || ! yearRange.length ) {
			return true;
		}

		const beginningOfFirstYearInRange = moment( `${ yearRange[ 0 ] }-01-01` );
		const endOfLastYearInRange = moment( `${ yearRange[ yearRange.length - 1 ] }-12-31` );

		const isAfterStartYear = current.isSameOrAfter( beginningOfFirstYearInRange );
		const isBeforeEndYear = current.isSameOrBefore( endOfLastYearInRange );

		return isAfterStartYear && isBeforeEndYear;
	};

	const momentDateFormat = useMemo(
		() => getMomentDateFormat( dateFormatType, podsFormat, formatCustomJS, formatCustom ),
		[ dateFormatType, podsFormat, formatCustomJS, formatCustom ]
	);

	const momentTimeFormat = useMemo(
		() => getMomentTimeFormat( timeFormatType, podsTimeFormat, podsTimeFormat24, timeFormatCustomJS, timeFormatCustom ),
		[ timeFormatType, podsTimeFormat, podsTimeFormat24, timeFormatCustomJS, timeFormatCustom ]
	);

	// If we can use an HTML5 input field, we can just return an input field.
	if ( useHTML5Field ) {
		return (
			<input
				name={ name }
				id={ `pods-form-ui-${ name }` }
				className="pods-form-ui-field pods-form-ui-field-type-datetime"
				type={ 'datetime' === type ? 'datetime-local' : type }
				value={ value }
				onChange={ handleInputFieldChange }
			/>
		);
	}

	return (
		<Datetime
			value={ value }
			onChange={ handleChange }
			dateFormat={ includeDateField && momentDateFormat }
			timeFormat={ includeTimeField && momentTimeFormat }
			isValidDate={ isValidDate }
			initialViewDate={ initialViewDate }
		/>
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
