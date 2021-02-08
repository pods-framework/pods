// @todo add tests
import React, { useState, useEffect } from 'react';
import Select from 'react-select';
import PropTypes from 'prop-types';

// WordPress dependencies
import { __, sprintf } from '@wordpress/i18n';

import SimpleSelect from './simple-select';
import RadioSelect from './radio-select';
import CheckboxSelect from './checkbox-select';
import ListSelect from './list-select';

import { toBool } from 'dfv/src/helpers/booleans';
import {
	// PICK_OPTIONS,
	FIELD_PROP_TYPE_SHAPE,
} from 'dfv/src/config/prop-types';

import './pick.scss';

// We may get the data value as an array or an object.
const formatDataFromProp = ( data ) => {
	// Skip unless we're handling an object of values.
	if ( 'object' !== typeof data || Array.isArray( data ) ) {
		return data;
	}

	const entries = Object.entries( data );

	return entries.reduce( ( accumulator, entry ) => {
		if ( 'string' === typeof entry[ 1 ] ) {
			return [
				...accumulator,
				{
					label: entry[ 1 ],
					value: entry[ 0 ],
				},
			];
		}

		const subOptions = Object.entries( entry[ 1 ] )
			.map( ( subEntry ) => ( { label: subEntry[ 1 ], value: subEntry[ 0 ] } ) );

		return [
			...accumulator,
			{
				label: entry[ 0 ],
				value: subOptions,
			},
		];
	}, [] );
};

const formatValuesForReactSelectComponent = (
	value,
	options = [],
	isMulti = false
) => {
	if ( ! value ) {
		return isMulti ? [] : undefined;
	}

	if ( ! isMulti ) {
		return options.find( ( option ) => option.value === value );
	}

	const splitValue = Array.isArray( value ) ? value : value.split( ',' );

	return splitValue.map(
		( currentValue ) => options.find(
			( option ) => option.value === currentValue
		)
	);
};

const formatValuesForHTMLSelectElement = ( value, isMulti ) => {
	if ( ! value ) {
		return undefined;
	}

	if ( ! isMulti ) {
		return value;
	}

	return Array.isArray( value ) ? value : value.split( ',' );
};

const Pick = ( props ) => {
	const {
		fieldConfig: {
			htmlAttr: htmlAttributes = {},
			data = [],
			label,
			name,
			// pick_allow_add_new: allowAddNew,
			pick_custom: pickCustomOptions,
			// pick_display,
			// pick_display_format_multi,
			// pick_display_format_separator,
			pick_format_multi: formatMulti = 'autocomplete',
			pick_format_single: formatSingle = 'dropdown',
			pick_format_type: formatType = 'single',
			// pick_groupby,
			pick_limit: limit,
			pick_object: pickObject,
			// pick_orderby: orderBy,
			// pick_post_status: postStatus,
			pick_select_text: selectText,
			pick_show_edit_link: showEditLink,
			pick_show_icon: showIcon,
			pick_show_view_link: showViewLink,
			// pick_table,
			// pick_table_id,
			// pick_table_index,
			// pick_taggable,
			// pick_user_role,
			// pick_val: pickValue,
			// rest_pick_depth: pickDepth,
			// rest_pick_response: pickResponse,
			// pick_where,
		},
		setValue,
		value,
	} = props;

	const isSingle = 'single' === formatType;
	const isMulti = 'multi' === formatType;

	// The options could be derived from the `data` prop (as a default),
	// or we may need to do more work to break them apart or load them by the API.
	const [ dataOptions, setDataOptions ] = useState( formatDataFromProp( data ) );

	const setValueWithLimit = ( newValue ) => {
		// We don't need to worry about limits if this isn't a multi-select field.
		if ( isSingle ) {
			setValue( newValue );
			return;
		}

		// Filter out empty values that could have gotten passed in.
		const filteredNewValues = newValue.filter( ( item ) => !! item );

		// If no limit is set, set the value.
		const numericLimit = parseInt( limit, 10 ) || 0;

		if ( isNaN( numericLimit ) || 0 === numericLimit || -1 === numericLimit ) {
			setValue( filteredNewValues );
			return;
		}

		// If we're trying to set more items than the limit allows, just return.
		if ( filteredNewValues.length > numericLimit ) {
			return;
		}

		setValue( filteredNewValues );
	};

	useEffect( () => {
		// const loadAjaxOptions = async () => {
			// const url = window.ajaxurl + '?pods_ajax=1';

			// const ajaxData = {
			// 	_wpnonce: ajaxData._wpnonce,
			// 	action: 'pods_relationship',
			// 	method: 'select2',
			// 	pod: ajaxData.pod,
			// 	field: ajaxData.field,
			// 	uri: ajaxData.uri,
			// 	id: ajaxData.id,
			// 	query: params.term,
			// };

			// const results = await fetch(
			// 	url,
			// 	{
			// 		method: 'POST',
			// 		headers: {
			// 			'Content-Type': 'application/json',
			// 		},
			// 		body: JSON.stringify( data ),
			// 	}
			// );
		// };

		switch ( pickObject ) {
			case 'custom-simple':
				const unsplitOptions = pickCustomOptions.split( '\n' );

				// Set an empty array if no entries or malformed.
				if ( ! unsplitOptions.length ) {
					setDataOptions( [] );
					return;
				}

				const optionEntries = unsplitOptions.map(
					( unsplitOption ) => {
						const splitOption = unsplitOption.split( '|' );

						// Return if malformed entry.
						if ( splitOption.length !== 2 ) {
							return null;
						}

						return {
							value: splitOption[ 0 ],
							label: splitOption[ 1 ],
						};
					}
				);

				// Filter out any options missing the value or label.
				const filteredOptionEntries = optionEntries.filter(
					( entry ) => entry.value && entry.label
				);

				setDataOptions( filteredOptionEntries );
				break;
			// @todo add cases for taxonomies, etc and fall through?
			case 'post-type':
				// @todo get request working
				setDataOptions( [] );
				// @todo
				break;
			default:
				// By default, the options are already loaded from `data`.
				break;
		}
	}, [ pickObject ] );

	if ( ! isMulti && 'radio' === formatSingle ) {
		return (
			<RadioSelect
				htmlAttributes={ htmlAttributes }
				name={ name }
				value={ value }
				setValue={ setValueWithLimit }
				options={ dataOptions }
			/>
		);
	}

	if (
		( isSingle && 'checkbox' === formatSingle ) ||
		( isMulti && 'checkbox' === formatMulti )
	) {
		let formattedValue = value;

		if ( isMulti ) {
			formattedValue = Array.isArray( value )
				? value
				: ( value || '' ).split( ',' );
		}

		return (
			<CheckboxSelect
				htmlAttributes={ htmlAttributes }
				name={ name }
				value={ formattedValue }
				isMulti={ isMulti }
				setValue={ setValueWithLimit }
				options={ dataOptions }
			/>
		);
	}

	if (
		( isSingle && 'list' === formatSingle ) ||
		( isMulti && 'list' === formatMulti )
	) {
		const formattedValue = ( Object.keys( dataOptions ).length && value )
			? formatValuesForReactSelectComponent( value, dataOptions, isMulti )
			: undefined;

		return (
			<ListSelect
				htmlAttributes={ htmlAttributes }
				name={ name }
				value={ formattedValue }
				setValue={ setValueWithLimit }
				options={ dataOptions }
				// translators: Placeholder with the field label.
				placeholder={ sprintf( __( 'Search %s…', 'pods' ), label ) }
				isMulti={ isMulti }
				limit={ parseInt( limit, 10 ) || 0 }
				showIcon={ toBool( showIcon ) }
				showViewLink={ toBool( showViewLink ) }
				showEditLink={ toBool( showEditLink ) }
			/>
		);
	}

	if (
		( isSingle && 'autocomplete' === formatSingle ) ||
		( isMulti && 'autocomplete' === formatMulti )
	) {
		const formattedValue = formatValuesForReactSelectComponent( value, dataOptions, isMulti );

		return (
			<Select
				htmlAttributes={ htmlAttributes }
				name={ name }
				options={ dataOptions }
				value={ formattedValue }
				// translators: Placeholder with the field label.
				placeholder={ sprintf( __( 'Search %s…', 'pods' ), label ) }
				isMulti={ isMulti }
				onChange={ ( newOption ) => {
					if ( isMulti ) {
						setValueWithLimit( newOption.map( ( selection ) => selection.value ) );
					} else {
						setValueWithLimit( newOption.value );
					}
				} }
			/>
		);
	}

	return (
		<SimpleSelect
			htmlAttributes={ htmlAttributes }
			name={ name }
			value={ formatValuesForHTMLSelectElement( value, isMulti ) }
			setValue={ ( newValue ) => setValueWithLimit( newValue ) }
			options={ dataOptions }
			placeholder={ selectText }
			isMulti={ isMulti }
		/>
	);
};

Pick.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.string ),
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default Pick;
