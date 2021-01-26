import { useState, useEffect } from 'react';

// WordPress dependencies
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const useBidirectionalFieldData = (
	data,
	podType,
	podName,
	name,
	fieldTypeOption,
	relatedTypeOption,
) => {
	// In most cases, the 'data' passed to the field will be the 'data'
	// prop, unless it's the "Bidirectional Field"/"sister_id".
	const [ dataOptions, setDataOptions ] = useState( data );

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

	return dataOptions;
};

export default useBidirectionalFieldData;
