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
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './pick.scss';

// The react-select component needs options in the format of
// [ { value: '', label: '' } ] instead of a simple map.
const formatOptionsForReactSelectComponent = ( options ) => {
	return Object
		.keys( options )
		.reduce(
			( accumulator, current ) => ( [
				...accumulator,
				{
					value: current,
					label: options[ current ],
				},
			] ),
			[]
		);
};

const formatValuesForReactSelectComponent = ( value, options = {}, isMulti = false ) => {
	if ( ! value ) {
		return isMulti ? [] : undefined;
	}

	if ( ! isMulti ) {
		return {
			value,
			label: options[ value ],
		};
	}

	const splitValue = Array.isArray( value ) ? value : value.split( ',' );

	return splitValue.map( ( currentValue ) => {
		return {
			value: currentValue,
			label: options[ currentValue ],
		};
	} );
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
			label,
			name,
			pick_allow_add_new: allowAddNew,
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
			pick_orderby: orderBy,
			pick_post_status: postStatus,
			pick_select_text: selectText,
			pick_show_edit_link: showEditLink,
			pick_show_icon: showIcon,
			pick_show_view_link: showViewLink,
			// pick_table,
			// pick_table_id,
			// pick_table_index,
			// pick_taggable,
			// pick_user_role,
			pick_val: pickValue,
			rest_pick_depth: pickDepth,
			rest_pick_response: pickResponse,
			// pick_where,
			data = [],
		},
		setValue,
		value,
	} = props;

	const isSingle = 'single' === formatType;
	const isMulti = 'multi' === formatType;

	// The options could be derived from the `data` prop (as a default),
	// or we may need to do more work to break them apart or load them by the API.
	const [ dataOptions, setDataOptions ] = useState( data );

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
				// @todo better error handling
				const unsplitOptions = pickCustomOptions.split( '\n' );
				const optionEntries = unsplitOptions.map( ( unsplitOption ) => unsplitOption.split( '|' ) );

				setDataOptions( Object.fromEntries( optionEntries ) );
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
				name={ name }
				value={ value }
				setValue={ setValue }
				options={ dataOptions }
			/>
		);
	}

	if ( isMulti && 'checkbox' === formatMulti ) {
		// @todo is the API returning the correct format?
		const formattedValue = Array.isArray( value ) ? value : ( value || '' ).split( ',' );

		return (
			<CheckboxSelect
				name={ name }
				value={ formattedValue }
				setValue={ setValue }
				options={ options }
			/>
		);
	}

	if (
		( isSingle && 'list' === formatSingle ) ||
		( isMulti && 'list' === formatMulti )
	) {
		const formattedValue = value
			? formatValuesForReactSelectComponent( value, options, isMulti )
			: undefined;

		return (
			<ListSelect
				name={ name }
				value={ formattedValue }
				setValue={ setValue }
				options={ formatOptionsForReactSelectComponent( options ) }
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
		const formattedValue = formatValuesForReactSelectComponent( value, options, isMulti );

		return (
			<Select
				name={ name }
				options={ formatOptionsForReactSelectComponent( options ) }
				value={ formattedValue }
				// translators: Placeholder with the field label.
				placeholder={ sprintf( __( 'Search %s…', 'pods' ), label ) }
				isMulti={ isMulti }
				onChange={ ( newOption ) => {
					if ( isMulti ) {
						setValue( newOption.map( ( selection ) => selection.value ) );
					} else {
						setValue( newOption.value );
					}
				} }
			/>
		);
	}

	return (
		<SimpleSelect
			name={ name }
			value={ formatValuesForHTMLSelectElement( value, isMulti ) }
			setValue={ ( newValue ) => setValue( newValue ) }
			options={ options }
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
	] ),
	data: PropTypes.object,
};

export default Pick;
