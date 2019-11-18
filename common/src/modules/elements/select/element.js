/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import ReactSelect, { components } from 'react-select';
import { Dashicon } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.pcss';

const DropdownIndicator = ( props ) => (
	components.DropdownIndicator && (
		<components.DropdownIndicator { ...props }>
			<Dashicon
				className="tribe-editor__select__dropdown-indicator"
				icon={ 'arrow-down' }
			/>
		</components.DropdownIndicator>
	)
);

const IndicatorSeparator = () => null;

const Select = ( { className, ...rest } ) => (
	<ReactSelect
		className={ classNames( 'tribe-editor__select', className ) }
		classNamePrefix="tribe-editor__select"
		components={ { DropdownIndicator, IndicatorSeparator } }
		{ ...rest }
	/>
);

Select.propTypes = {
	className: PropTypes.string,
};

export default Select;
