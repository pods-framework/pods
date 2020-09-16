import React from 'react';
import Select from 'react-select';
import PropTypes from 'prop-types';

import { __, sprintf } from '@wordpress/i18n';

import SimpleSelect from './simple-select';
import RadioSelect from './radio-select';
import CheckboxSelect from './checkbox-select';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './pick.scss';

// @todo move the bidirectional logic here from DependentFieldOption
// @todo add tests
const Pick = ( props ) => {
	const {
		fieldConfig: {
			label,
			name,
			// pick_allow_add_new,
			pick_custom: pickCustomOptions,
			// pick_display,
			// pick_display_format_multi,
			// pick_display_format_separator,
			pick_format_multi: formatMulti = 'autocomplete',
			pick_format_single: formatSingle = 'dropdown',
			pick_format_type: formatType = 'single',
			// pick_groupby,
			// pick_limit,
			pick_object: pickObject,
			// pick_orderby,
			// pick_post_status,
			pick_select_text: selectText,
			// pick_show_edit_link,
			// pick_show_icon,
			// pick_show_view_link,
			// pick_table,
			// pick_table_id,
			// pick_table_index,
			// pick_taggable,
			// pick_user_role,
			// pick_val,
			// pick_where,
			data = [],
		},
		setValue,
		value,
	} = props;

	const isMulti = 'multi' === formatType;

	// Options could be in `data` or could be in `pick_custom`.
	let options = {};

	switch ( pickObject ) {
		case 'custom-simple':
			// @todo better error handling
			const unsplitOptions = pickCustomOptions.split( '\n' );
			const optionEntries = unsplitOptions.map( ( unsplitOption ) => unsplitOption.split( '|' ) );

			options = Object.fromEntries( optionEntries );
			break;
		default:
			break;
	}

	if ( ! isMulti && 'radio' === formatSingle ) {
		return (
			<RadioSelect
				name={ name }
				value={ value }
				setValue={ setValue }
				options={ options }
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

	if ( 'autocomplete' === formatSingle ) {
		const formattedOptions = Object
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

		return (
			<Select
				name={ name }
				options={ formattedOptions }
				value={ value ? {
					value,
					label: options[ value ],
				} : undefined }
				// translators: Placeholder with the field label.
				placeholder={ sprintf( __( 'Search %s…', 'pods' ), label ) }
				isMulti={ isMulti }
				onChange={ ( newOption ) => setValue( newOption.value ) }
			/>
		);
	}

	return (
		<SimpleSelect
			name={ name }
			value={ value }
			setValue={ ( newValue ) => {
				console.log( 'new value', newValue );
				setValue( newValue );
			} }
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
