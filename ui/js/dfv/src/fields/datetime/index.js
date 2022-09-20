import React, { useState, useMemo, useEffect } from 'react';
import { Dropdown } from '@wordpress/components';
import Datetime from 'react-datetime';
import moment from 'moment';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import { toBool } from 'dfv/src/helpers/booleans';
import {
	convertPHPDateFormatToMomentFormat,
	convertjQueryUIDateFormatToMomentFormat,
	convertjQueryUITimeFormatToMomentFormat,
	convertPodsDateFormatToMomentFormat,
	getArrayOfYearsFromJqueryUIYearRange,
} from 'dfv/src/helpers/dateFormats';

import { dateTimeValidator } from 'dfv/src/helpers/validators';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import 'react-datetime/css/react-datetime.css';
import './datetime.scss';

const checkForHTML5BrowserSupport = ( fieldType ) => {
	const input = document.createElement( 'input' );
	input.setAttribute( 'type', fieldType );
	input.setAttribute( 'value', 'not-a-date' );

	return ( input.value !== 'not-a-date' );
};

// Determine date and time formats based on the field's config values.
const getMomentDateFormat = ( formatType, podsFormat, formatCustomJS, formatCustom, dateFormatMomentJS ) => {
	if ( dateFormatMomentJS ) {
		return dateFormatMomentJS;
	}

	// eslint-disable-next-line camelcase
	const wpDefaultFormat = window?.podsDFVConfig?.datetime?.date_format || 'F j, Y';

	let format = convertPHPDateFormatToMomentFormat( wpDefaultFormat );

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

const getMomentTimeFormat = ( timeFormatType, podsTimeFormat, podsTimeFormat24, timeFormatCustomJS, timeFormatCustom, timeFormatMomentJS ) => {
	if ( timeFormatMomentJS ) {
		return timeFormatMomentJS;
	}

	// eslint-disable-next-line camelcase
	const wpDefaultFormat = window?.podsDFVConfig?.datetime?.time_format || 'g:i a';

	let format = convertPHPDateFormatToMomentFormat( wpDefaultFormat );

	switch ( timeFormatType ) {
		case '12':
			format = convertPodsDateFormatToMomentFormat( podsTimeFormat, false );
			break;
		case '24':
			format = convertPodsDateFormatToMomentFormat( podsTimeFormat24, true );
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

const DateTime = ( {
	addValidationRules,
	value,
	setValue,
	fieldConfig = {},
	setHasBlurred,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		name,
		type = 'datetime', // 'datetime', 'time', or 'date'
		datetime_date_format_moment_js: dateFormatMomentJS,
		datetime_format: podsFormat,
		datetime_format_custom: formatCustom,
		datetime_format_custom_js: formatCustomJS,
		datetime_html5: html5,
		datetime_time_format: podsTimeFormat,
		datetime_time_format_24: podsTimeFormat24,
		datetime_time_format_custom: timeFormatCustom,
		datetime_time_format_custom_js: timeFormatCustomJS,
		datetime_time_format_moment_js: timeFormatMomentJS,
		datetime_time_type: timeFormatType = 'wp', // 'wp, '12', '24', or 'custom'
		datetime_type: dateFormatType = 'wp', // 'wp', 'format', or 'custom'
		datetime_year_range_custom: yearRangeCustom,
		read_only: readOnly,
	} = fieldConfig;

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

	const momentDateFormat = useMemo(
		() => getMomentDateFormat( dateFormatType, podsFormat, formatCustomJS, formatCustom, dateFormatMomentJS ),
		[ dateFormatType, podsFormat, formatCustomJS, formatCustom, dateFormatMomentJS ]
	);

	const momentTimeFormat = useMemo(
		() => getMomentTimeFormat( timeFormatType, podsTimeFormat, podsTimeFormat24, timeFormatCustomJS, timeFormatCustom, timeFormatMomentJS ),
		[ timeFormatType, podsTimeFormat, podsTimeFormat24, timeFormatCustomJS, timeFormatCustom, timeFormatMomentJS ]
	);

	const getDBFormat = () => {
		// Use a full date and time format for our value string by default.
		// Unless we're only showing the date OR the time picker.
		if ( includeDateField && includeTimeField ) {
			return 'YYYY-MM-DD kk:mm:ss';
		} else if ( includeTimeField ) {
			return 'kk:mm:ss';
		} else if ( includeDateField ) {
			return 'YYYY-MM-DD';
		}

		return 'YYYY-MM-DD kk:mm:ss';
	};

	const getHTML5Format = () => {
		// Use a full date and time format for our value string by default.
		// Unless we're only showing the date OR the time picker.
		if ( includeDateField && includeTimeField ) {
			return 'YYYY-MM-DD[T]HH:mm:ss';
		} else if ( includeTimeField ) {
			return 'HH:mm:ss';
		} else if ( includeDateField ) {
			return 'YYYY-MM-DD';
		}

		return 'YYYY-MM-DD[T]HH:mm:ss';
	};

	const formatValueForHTML5Field = ( stringValue ) => {
		if ( ! stringValue ) {
			return '';
		}

		const momentObject = moment( stringValue, [ getHTML5Format(), getDBFormat() ] );

		if ( ! momentObject.isValid() ) {
			return '';
		}

		return momentObject.format( getHTML5Format() );
	};

	const getFullFormat = () => {
		// Use a full date and time format for our value string by default.
		// Unless we're only showing the date OR the time picker.
		if ( includeDateField && includeTimeField ) {
			if ( 'c' === podsFormat ) {
				return momentDateFormat;
			}

			return `${ momentDateFormat } ${ momentTimeFormat }`;
		} else if ( includeTimeField ) {
			return momentTimeFormat;
		} else if ( includeDateField ) {
			return momentDateFormat;
		}

		return `${ momentDateFormat } ${ momentTimeFormat }`;
	};

	const formatMomentObject = ( momentObject, defaultValue = '' ) => {
		if ( ! momentObject.isValid() ) {
			return defaultValue;
		}

		const userLocale = window?.podsDFVConfig?.userLocale ?? 'en';

		momentObject.locale( userLocale );

		return momentObject.format( getFullFormat() );
	};

	const formatMomentObjectForDB = ( momentObject, defaultValue = '' ) => {
		if ( ! momentObject.isValid() ) {
			return defaultValue;
		}

		return momentObject.format( getDBFormat() );
	};

	const handleHTML5InputFieldChange = ( event ) => setValue( event.target.value );

	// Keep local versions as a string (formatted and ready to display, and in case
	// the Moment object is invalid) and as a Moment object.
	const isValueEmpty = [ '0000-00-00', '0000-00-00 00:00:00', '' ].includes( value );

	const [ localStringValue, setLocalStringValue ] = useState(
		() => {
			if ( isValueEmpty ) {
				return '';
			}

			return formatMomentObject(
				moment( value, [ getDBFormat(), getFullFormat() ] ),
				value
			);
		},
	);
	const [ localMomentValue, setLocalMomentValue ] = useState(
		() => {
			if ( isValueEmpty ) {
				return '';
			}

			return formatMomentObject(
				moment( value, [ getDBFormat(), getFullFormat() ] ),
				value
			);
		},
	);

	const handleChange = ( newValue ) => {
		let momentObject = newValue;

		if ( momentObject && ! moment.isMoment( momentObject ) ) {
			momentObject = moment( newValue, [ getDBFormat(), getFullFormat() ] );
		}

		// Receives the selected moment object, if the date in the input is valid.
		// If the date in the input is not valid, the callback receives the value of
		// the input a string.
		if ( moment.isMoment( momentObject ) ) {
			setValue( formatMomentObjectForDB( momentObject ) );
			setLocalStringValue( formatMomentObject( momentObject ) );
			setLocalMomentValue( momentObject );
		} else {
			setValue( newValue );
			setLocalStringValue( newValue );
			setLocalMomentValue( null );
		}

		setHasBlurred();
	};

	// Set the initial view date to the current date, unless the range of years is before
	// the current time.
	let initialViewDate = ( yearRange && yearRange[ yearRange.length - 1 ] < new Date().getFullYear() )
		? new Date( yearRange[ 0 ], 0, 1 )
		: new Date();

	// Initial view date should be current value if we have one set.
	if ( ! isValueEmpty ) {
		initialViewDate = localMomentValue;
	}

	// Set up range validator, both for the react-datetime component
	// and our validation hook.
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

	useEffect( () => {
		const rangeValidationRule = {
			rule: dateTimeValidator( yearRange, getDBFormat() ),
			condition: () => true,
		};

		addValidationRules( [ rangeValidationRule ] );
	}, [] );

	// If we can use an HTML5 input field, we can just return an input field.
	if ( useHTML5Field ) {
		return (
			<input
				id={ htmlAttributes.id || `pods-form-ui-${ name }` }
				name={ htmlAttributes.name || name }
				className={ classnames( 'pods-form-ui-field pods-form-ui-field-type-datetime', htmlAttributes.class ) }
				type={ 'datetime' === type ? 'datetime-local' : type }
				value={ formatValueForHTML5Field( value ) }
				onChange={ handleHTML5InputFieldChange }
				onBlur={ setHasBlurred }
				readOnly={ toBool( readOnly ) }
			/>
		);
	}

	return (
		<div>
			<input
				readOnly={ true }
				hidden={ true }
				value={ value }
				name={ htmlAttributes.name || name }
			/>
			<Dropdown
				renderToggle={ ( state ) => (
					<input
						type="text"
						value={ localStringValue }
						onClick={ () => ! toBool( readOnly ) ? state.onToggle() : undefined }
						onChange={ ( event ) => {
							// Track local values, but don't change actual value until blur event.
							setLocalStringValue( event.target.value );
							setLocalMomentValue( moment( event.target.value, [ getDBFormat(), getFullFormat() ] ) );
						} }
						readOnly={ toBool( readOnly ) }
						onBlur={ ( event ) => handleChange( event.target.value ) }
						id={ htmlAttributes.id || `pods-form-ui-${ name }` }
						className={
							classnames(
								'pods-form-ui-field pods-form-ui-field-type-datetime',
								htmlAttributes.class
							)
						}
					/>
				) }
				renderContent={ () => (
					<Datetime
						className="pods-react-datetime-fix"
						value={ localMomentValue }
						onChange={ ( newValue ) => handleChange( newValue ) }
						dateFormat={ includeDateField ? momentDateFormat : false }
						timeFormat={ includeTimeField ? momentTimeFormat : false }
						isValidDate={ isValidDate }
						initialViewDate={ initialViewDate }
						input={ false }
						renderInput={ null }
					/>
				) }
			/>
		</div>
	);
};

DateTime.defaultProps = {
	value: '',
};

DateTime.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default DateTime;
