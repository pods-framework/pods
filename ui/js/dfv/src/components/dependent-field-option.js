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
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

// Conditionally display a FieldOption (depends-on support)
const DependentFieldOption = ( {
	podType,
	podName,
	field,
	value,
	allOptionValues,
	setOptionValue,
} ) => {
	const {
		data,
		default: defaultValue,
		'depends-on': dependsOn,
		name,
	} = field;

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
	const processedAllOptionValues = allOptionValues;

	if (
		'pick_object' === name &&
		allOptionValues.pick_val &&
		! value.includes( `-${ allOptionValues.pick_val }`, `-${ allOptionValues.pick_val }`.length )
	) {
		processedValue = `${ value }-${ allOptionValues.pick_val }`;
		processedAllOptionValues.pick_object = `${ value }-${ allOptionValues.pick_val }`;
	}

	const handleInputChange = ( newValue ) => {
		// If there's a default, then don't allow an empty value.
		const newValueWithDefault = newValue || defaultValue;

		if ( 'boolean' === field.type ) {
			const binaryStringFromBoolean = newValueWithDefault ? '1' : '0';

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
		let podValue = relatedTypeOption;

		if ( relatedTypeOption.startsWith( 'post_type-' ) ) {
			podValue = relatedTypeOption.substring( 10 ); // everything after 'post_type-'
		} else if ( relatedTypeOption.startsWith( 'taxonomy-' ) ) {
			podValue = relatedTypeOption.substring( 9 ); // everything after 'taxonomy-'
		} else if ( relatedTypeOption.startsWith( 'comment-' ) ) {
			podValue = relatedTypeOption.substring( 8 ); // everything after 'comment-'
		} else if ( relatedTypeOption.startsWith( 'pod-' ) ) {
			podValue = relatedTypeOption.substring( 4 ); // everything after 'pod-'
		} else if ( ! [ 'user', 'media', 'comment' ].includes( relatedTypeOption ) ) {
			// We only support post types, taxonomies, comments, users, and media for bi-directional relationships.
			return;
		}

		const loadBidirectionalFieldData = async () => {
			// Initialize the field with loading text.
			setDataOptions( [
				{
					value: '',
					label: __( 'Loading available fields…', 'pods' ),
				},
			] );

			const args = {
				pick_object: podType,
			};

			// If the current pod is a post_type, taxonomy, or pod,
			// set the `pick_val` to the pod name being edited.
			if ( [ 'post_type', 'taxonomy', 'pod' ].includes( podType ) ) {
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
					setDataOptions( [
						{
							value: '',
							label: __( 'No Related Fields Found', 'pods' ),
						},
					] );
					return;
				}

				// Reduce the API results to an ID for the value and a label.
				const processedFields = results.fields.map( ( currentField ) => {
					return {
						value: currentField.id,
						label: `${ currentField.label } (${ currentField.name }) [Pod: ${ currentField.parent_data?.name }]`,
					};
				} );

				processedFields.unshift( {
					value: '',
					label: __( '-- Select Related Field --', 'pods' ),
				} );

				setDataOptions( processedFields );
			} catch ( error ) {
				setDataOptions( {
					value: '',
					label: __( 'No Related Fields Found', 'pods' ),
				} );
			}
		};

		loadBidirectionalFieldData();
	}, [ podType, podName, name, fieldTypeOption, relatedTypeOption, setDataOptions ] );

	// Don't render a field that hasn't had its dependencies met.
	if ( ! validateFieldDependencies( processedAllOptionValues, dependsOn ) ) {
		return null;
	}

	return (
		<PodsFieldOption
			field={ field }
			data={ dataOptions }
			value={ processedValue || defaultValue }
			setValue={ handleInputChange }
		/>
	);
};

DependentFieldOption.propTypes = {
	podType: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
	field: FIELD_PROP_TYPE_SHAPE,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
		PropTypes.array,
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
