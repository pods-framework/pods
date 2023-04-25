import React from 'react';
import PropTypes from 'prop-types';

import DateTime from '../datetime';
import moment from 'moment';
import 'moment/min/locales';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const Time = ( props ) => {
	const {
		fieldConfig = {},
	} = props;

	// Process the field config so that properties prefixed with "date_"
	// are changed to "datetime_".
	const fieldConfigEntries = Object.entries( fieldConfig ).filter(
		( entry ) => ! entry[ 0 ].startsWith( 'time_' )
	);

	const newConfig = {
		...Object.fromEntries( fieldConfigEntries ),
		datetime_allow_empty: fieldConfig.time_allow_empty = true,
		datetime_html5: fieldConfig.time_html5,
		datetime_time_format: fieldConfig.time_format,
		datetime_time_format_24: fieldConfig.time_format_24,
		datetime_time_format_custom: fieldConfig.time_format_custom,
		datetime_time_format_custom_js: fieldConfig.time_format_custom_js,
		datetime_time_format_moment_js: fieldConfig.time_format_moment_js,
		datetime_time_type: fieldConfig.time_type,
		datetime_type: 'time',
	};

	const userLocale = window?.podsDFVConfig?.userLocale ?? 'en';

	return (
		<DateTime
			{ ...props }
			fieldConfig={ newConfig }
			locale={ userLocale }
		/>
	);
};

Time.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Time;
