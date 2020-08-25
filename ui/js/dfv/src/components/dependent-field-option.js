import React, { useEffect, useState } from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

// Pod dependencies
import PodsFieldOption from 'dfv/src/components/field-option';
import validateFieldDependencies from 'dfv/src/helpers/validateFieldDependencies';

// Conditionally display a FieldOption (depends-on support)
const DependentFieldOption = ( {
	fieldType,
	name,
	required,
	label,
	value,
	default: defaultValue,
	data,
	allOptionValues,
	dependents,
	description,
	helpText,
	setOptionValue,
} ) => {
	// In most cases, the 'data' passed to the field will be the 'data'
	// prop, unless it's the Bidirectional Field.
	const [ dataOptions, setDataOptions ] = useState( data );

	const handleInputChange = ( event ) => {
		const { target } = event;

		// If there's a default, then don't allow an empty value.
		const newValue = target.value || defaultValue;

		if ( 'checkbox' === target.type ) {
			const binaryStringFromBoolean = target.checked ? '1' : '0';

			setOptionValue( name, binaryStringFromBoolean );
		} else {
			setOptionValue( name, newValue );
		}
	};

	// The data for the "Bidirection Field" (or "sister_id") works differently
	// from all other fields - the data isn't loaded along with the others.
	// We need to watch the "Field Type" and "Related Type" fields for changes
	// to load the appropriate options here.
	// @todo move to a custom hook.
	const fieldTypeOption = allOptionValues.type;

	const relatedTypeOption = allOptionValues.pick_object;

	useEffect( () => {
		// We only need to fetch data if we're creating a "Relationship"/"pick" field
		// and if we're looking at the "Bidirectional Field"/"sister_id" value for the field.
		if ( 'pick' !== fieldTypeOption || 'sister_id' !== name ) {
			return;
		}

		console.log( 'looking at bidirectional field', [ name, fieldTypeOption, relatedTypeOption ] );

		// Only get results if "Related Type"/"pick_object" is a Post Type,
		// Taxonomy, or Pod.
		let pickObject = '';
		let pickVal = '';

		if ( relatedTypeOption.startsWith( 'post_type-' ) ) {
			pickObject = 'post_type';
			pickVal = relatedTypeOption.substring( 10 ); // everything after 'post_type-'
		} else if ( relatedTypeOption.startsWith( 'taxonomy-' ) ) {
			pickObject = 'taxonomy';
			pickVal = relatedTypeOption.substring( 9 ); // everything after 'taxonomy-'
		} else if ( relatedTypeOption.startsWith( 'pod-' ) ) {
			pickObject = 'pod';
			pickVal = relatedTypeOption.substring( 4 ); // everything after 'pod-'
		} else {
			return;
		}

		const loadBidirectionalFieldData = async () => {
			const endpointParams = new URLSearchParams( {
				types: 'pick',
				include_parent: 1,
				args: JSON.stringify( {
					pick_object: pickObject,
					pick_val: pickVal,
				} ),
			} );

			try {
				const results = await apiFetch(
					{ path: `pods/v1/fields?${ endpointParams.toString() }` }
				);

				console.log( 'fields loaded', results );

				if ( ! results.fields || ! results.fields.length ) {
					setDataOptions( { '': __( 'No Related Fields Found', 'pods' ) } );
					return;
				}

				// Reduce the API results to an ID for the value and a label.
				const processedFields = results.fields.reduce(
					( accumulator, field ) => ( {
						...accumulator,
						[ field.id ]: `${ field.label } (${ field.name }) [Pod: ${ field.parent_data?.name }]`,
					} ),
					{}
				);

				setDataOptions( {
					'': __( '-- Select Related Field --', 'pods' ),
					...processedFields,
				} );
			} catch ( error ) {
				setDataOptions( { '': __( 'No Related Fields Found', 'pods' ) } );
			}
		};

		loadBidirectionalFieldData();
	}, [ name, fieldTypeOption, relatedTypeOption, setDataOptions ] );

	// Don't render a field that hasn't had its dependencies met.
	if ( ! validateFieldDependencies( allOptionValues, dependents ) ) {
		return null;
	}

	return (
		<PodsFieldOption
			fieldType={ fieldType }
			name={ name }
			required={ required }
			value={ value || defaultValue }
			label={ label }
			data={ dataOptions }
			onChange={ handleInputChange }
			helpText={ helpText }
			description={ description }
		/>
	);
};

DependentFieldOption.propTypes = {
	fieldType: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	required: PropTypes.bool.isRequired,
	data: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.array,
	] ),
	default: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	description: PropTypes.string,
	label: PropTypes.string.isRequired,
	dependents: PropTypes.oneOfType( [
		// The API may provide an empty array if empty, or an object
		// if there are any values.
		PropTypes.array,
		PropTypes.object,
	] ),
	helpText: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	setOptionValue: PropTypes.func.isRequired,
};

export default DependentFieldOption;
