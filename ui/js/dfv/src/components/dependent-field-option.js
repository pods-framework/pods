import React, { useEffect, useState } from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { withSelect } from '@wordpress/data';

// Pod dependencies
import PodsFieldOption from 'dfv/src/components/field-option';
import validateFieldDependencies from 'dfv/src/helpers/validateFieldDependencies';
import { STORE_KEY_EDIT_POD } from 'dfv/src/admin/edit-pod/store/constants';

// Conditionally display a FieldOption (depends-on support)
const DependentFieldOption = ( {
	podType,
	podName,
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
	// prop, unless it's the "Bidirectional Field"/"sister_id".
	const [ dataOptions, setDataOptions ] = useState( data );

	// Workaround for the pick_object value: this value should be changed
	// to a combination of the `pick_object` sent by the API and the
	// `pick_val`. This was originally done to make the form easier to select.
	//
	// But this processing may not need to happen - it'll get set correctly
	// after a UI update, but will be wrong after the update from saving to the API,
	// so we'll check that the values haven't already been merged.
	let processedValue = value;
	let processedAllOptionValues = allOptionValues;

	if (
		'pick_object' === name &&
		allOptionValues.pick_val &&
		! value.includes( `-${ allOptionValues.pick_val }`, `-${ allOptionValues.pick_val }`.length )
	) {
		processedValue = `${ value }-${ allOptionValues.pick_val }`;
		processedAllOptionValues.pick_object = `${ value }-${ allOptionValues.pick_val }`;
	}

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
	const fieldTypeOption = allOptionValues.type;

	const relatedTypeOption = allOptionValues.pick_object;

	useEffect( () => {
		// We only need to fetch data if we're creating a "Relationship"/"pick" field
		// and if we're looking at the "Bidirectional Field"/"sister_id" value for the field.
		if ( 'pick' !== fieldTypeOption || 'sister_id' !== name ) {
			return;
		}

		// Only get results if "Related Type"/"pick_object" is a Post Type,
		// Taxonomy, or Pod.
		let podValue = '';

		if ( relatedTypeOption.startsWith( 'post_type-' ) ) {
			podValue = relatedTypeOption.substring( 10 ); // everything after 'post_type-'
		} else if ( relatedTypeOption.startsWith( 'taxonomy-' ) ) {
			podValue = relatedTypeOption.substring( 9 ); // everything after 'taxonomy-'
		} else if ( relatedTypeOption.startsWith( 'pod-' ) ) {
			podValue = relatedTypeOption.substring( 4 ); // everything after 'pod-'
		} else {
			return;
		}

		const loadBidirectionalFieldData = async () => {
			const args = {
				pick_object: podType,
			};

			// If the current pod is a post_type, taxonomy, or pod,
			// set the `pick_val` to the pod name being edited.
			if ( [ 'post_type', 'taxonomy', 'user' ].includes( podType ) ) {
				args.pick_val = podName;
			}

			const endpointParams = new URLSearchParams( {
				types: 'pick',
				include_parent: 1,
				pod: podValue,
				args: JSON.stringify( args ),
			} );

			try {
				const requestPath = `pods/v1/fields?${ endpointParams.toString() }`;

				const results = await apiFetch( { path: requestPath } );

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
					{
						'': __( '-- Select Related Field --', 'pods' ),
					}
				);

				setDataOptions( processedFields );
			} catch ( error ) {
				setDataOptions( { '': __( 'No Related Fields Found', 'pods' ) } );
			}
		};

		loadBidirectionalFieldData();
	}, [ podType, podName, name, fieldTypeOption, relatedTypeOption, setDataOptions ] );

	// Don't render a field that hasn't had its dependencies met.
	if ( ! validateFieldDependencies( processedAllOptionValues, dependents ) ) {
		return null;
	}

	return (
		<PodsFieldOption
			fieldType={ fieldType }
			name={ name }
			required={ required }
			value={ processedValue || defaultValue }
			label={ label }
			data={ dataOptions }
			onChange={ handleInputChange }
			helpText={ helpText }
			description={ description }
		/>
	);
};

DependentFieldOption.propTypes = {
	podType: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
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

export default withSelect( ( select ) => {
	const storeSelect = select( STORE_KEY_EDIT_POD );

	return {
		podType: storeSelect.getPodOption( 'type' ),
		podName: storeSelect.getPodOption( 'name' ),
	};
} )( DependentFieldOption );
