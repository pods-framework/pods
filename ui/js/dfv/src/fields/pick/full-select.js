/**
 * External dependencies
 */
import React from 'react';
import Select, { components } from 'react-select';
import AsyncSelect from 'react-select/async';
import AsyncCreatableSelect from 'react-select/async-creatable';
import PropTypes from 'prop-types';

/**
 * Other Pods dependencies
 */
import loadAjaxOptions from '../../helpers/loadAjaxOptions';

import { AJAX_DATA } from 'dfv/src/config/prop-types';

const FullSelect = ( {
	isTaggable,
	ajaxData,
	shouldRenderValue,
	formattedOptions,
	value,
	onChange,
	placeholder,
	isMulti,
	isClearable,
	isReadOnly,
} ) => {
	const useAsyncSelectComponent = isTaggable || ajaxData?.ajax;
	const AsyncSelectComponent = isTaggable ? AsyncCreatableSelect : AsyncSelect;

	return (
		<>
			{ useAsyncSelectComponent ? (
				<AsyncSelectComponent
					controlShouldRenderValue={ shouldRenderValue }
					defaultOptions={ formattedOptions }
					loadOptions={ ajaxData?.ajax ? loadAjaxOptions( ajaxData ) : undefined }
					value={ value }
					placeholder={ placeholder }
					isMulti={ isMulti }
					isClearable={ isClearable }
					onChange={ onChange }
					readOnly={ isReadOnly }
					components={ {
						MultiValue: components.MultiValue,
						MultiValueLabel: components.MultiValueLabel,
					} }
				/>
			) : (
				<Select
					controlShouldRenderValue={ shouldRenderValue }
					options={ formattedOptions }
					value={ value }
					placeholder={ placeholder }
					isMulti={ isMulti }
					isClearable={ isClearable }
					onChange={ onChange }
					readOnly={ isReadOnly }
					components={ {
						MultiValue: components.MultiValue,
						MultiValueLabel: components.MultiValueLabel,
					} }
				/>
			) }
		</>
	);
};

const REACT_SELECT_VALUE_PROP_TYPE = PropTypes.shape( {
	label: PropTypes.string,
	value: PropTypes.string,
} );

FullSelect.propTypes = {
	/**
	 * True if new values can be created.
	 */
	isTaggable: PropTypes.bool.isRequired,

	/**
	 * Helper data for loading ajax results.
	 */
	ajaxData: AJAX_DATA,

	/**
	 * True if the value should be rendered (will be false for List Select fields).
	 */
	shouldRenderValue: PropTypes.bool.isRequired,

	/**
	 * Formatted options for the react-select component.
	 */
	formattedOptions: PropTypes.arrayOf( REACT_SELECT_VALUE_PROP_TYPE ),

	/**
	 * Value, either an array for multi or single value.
	 */
	value: PropTypes.oneOfType( [
		REACT_SELECT_VALUE_PROP_TYPE,
		PropTypes.arrayOf( REACT_SELECT_VALUE_PROP_TYPE ),
	] ),

	/**
	 * Callback to update the value.
	 */
	onChange: PropTypes.func.isRequired,

	/**
	 * Placeholder text.
	 */
	placeholder: PropTypes.string.isRequired,

	/**
	 * True if multiple values are allowed.
	 */
	isMulti: PropTypes.bool.isRequired,

	/**
	 * True if the field can be cleared.
	 */
	isClearable: PropTypes.bool.isRequired,

	/**
	 * True if the field should be read-only.
	 */
	isReadOnly: PropTypes.bool.isRequired,
};

export default FullSelect;
