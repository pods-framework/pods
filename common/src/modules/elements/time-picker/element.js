/**
 * External dependencies
 */
import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import moment from 'moment';
import { noop } from 'lodash';
import classNames from 'classnames';
import { ScrollTo, ScrollArea } from 'react-scroll-to';

/**
 * WordPress dependencies
 */
import {
	Dropdown,
	Dashicon,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { PreventBlockClose } from '@moderntribe/common/components';
import Button from '@moderntribe/common/elements/button/element';
import Input from '@moderntribe/common/elements/input/element';
import {
	date as dateUtil,
	moment as momentUtil,
	time as timeUtil,
	TribePropTypes,
} from '@moderntribe/common/utils';
import './style.pcss';

const TimePicker = ( {
	allDay,
	current,
	disabled,
	end,
	onBlur,
	onChange,
	onClick,
	onFocus,
	showAllDay,
	start,
	step,
	timeFormat,
} ) => {

	const renderLabel = ( onAllDayClick ) => {
		if ( allDay ) {
			return (
				<Button
					className="tribe-editor__timepicker__all-day-btn"
					disabled={ disabled }
					onClick={ onAllDayClick }
				>
					{ __( 'All Day', 'tribe-common' ) }
				</Button>
			);
		}

		return (
			<Input
				className="tribe-editor__timepicker__input"
				disabled={ disabled }
				onBlur={ onBlur }
				onChange={ onChange }
				onFocus={ onFocus }
				type="text"
				value={ current }
			/>
		);
	};

	const renderToggle = ( { onToggle, isOpen } ) => (
		<Fragment>
			{ renderLabel( onToggle ) }
			<Button
				aria-expanded={ isOpen }
				className="tribe-editor__timepicker__toggle-btn"
				disabled={ disabled }
				onClick={ onToggle }
			>
				<Dashicon
					className="tribe-editor__timepicker__toggle-btn-icon"
					icon={ isOpen ? 'arrow-up' : 'arrow-down' }
				/>
			</Button>
		</Fragment>
	);

	const getItems = () => {
		const items = [];

		const startSeconds = timeUtil.toSeconds( start, timeUtil.TIME_FORMAT_HH_MM );
		const endSeconds = timeUtil.toSeconds( end, timeUtil.TIME_FORMAT_HH_MM );

		const currentMoment = moment( current, momentUtil.TIME_FORMAT );

		for ( let time = startSeconds; time <= endSeconds; time += step ) {
			let isCurrent = false;
			if ( currentMoment.isValid() ) {
				const currentTime = momentUtil.toTime24Hr( currentMoment );
				isCurrent = time === timeUtil.toSeconds( currentTime, timeUtil.TIME_FORMAT_HH_MM );
			}

			items.push( {
				value: time,
				text: formatLabel( time ),
				isCurrent,
			} );
		}

		return items;
	};

	const formatLabel = ( seconds ) => {
		return momentUtil.setTimeInSeconds( moment(), seconds ).format( momentUtil.toFormat( timeFormat ) );
	};

	const renderItem = ( item, onClose ) => {
		const itemClasses = {
			'tribe-editor__timepicker__item': true,
			'tribe-editor__timepicker__item--current': item.isCurrent && ! allDay,
		};

		return (
			<Button
				key={ `time-${ item.value }` }
				className={ classNames( itemClasses ) }
				value={ item.value }
				onClick={ () => onClick( item.value, onClose ) }
			>
				{ item.text }
			</Button>
		);
	};

	const renderContent = ( { onClose } ) => (
		<ScrollTo>
			{ () => (
				<PreventBlockClose>
					<ScrollArea
						key="tribe-element-timepicker-items"
						className="tribe-editor__timepicker__items"
					>
						{ showAllDay && renderItem(
							{ text: __( 'All Day', 'tribe-common' ), value: 'all-day' },
							onClose,
						) }
						{ getItems().map( ( item ) => renderItem( item, onClose ) ) }
					</ScrollArea>
				</PreventBlockClose>
			) }
		</ScrollTo>
	);

	return (
		<div
			key="tribe-element-timepicker"
			className="tribe-editor__timepicker"
		>
			<Dropdown
				className="tribe-editor__timepicker__toggle"
				contentClassName="tribe-editor__timepicker__content"
				position="bottom center"
				renderToggle={ renderToggle }
				renderContent={ renderContent }
			/>
		</div>
	);
};

TimePicker.defaultProps = {
	allDay: false,
	onBlur: noop,
	onChange: noop,
	onClick: noop,
	onFocus: noop,
	step: timeUtil.HALF_HOUR_IN_SECONDS,
	timeFormat: dateUtil.FORMATS.WP.time,
};

TimePicker.propTypes = {
	/**
	 * TribePropTypes.timeFormat check for string formatted as a time
	 * using 24h clock in hh:mm format
	 * e.g. 00:24, 03:57, 21:12
	 */
	allDay: PropTypes.bool,
	current: PropTypes.string,
	disabled: PropTypes.bool,
	end: TribePropTypes.timeFormat.isRequired,
	onBlur: PropTypes.func,
	onChange: PropTypes.func,
	onClick: PropTypes.func,
	onFocus: PropTypes.func,
	showAllDay: PropTypes.bool,
	start: TribePropTypes.timeFormat.isRequired,
	step: PropTypes.number,
	timeFormat: PropTypes.string,
};

export default TimePicker;
