import { useState, useEffect } from 'react';

// WordPress dependencies
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const useBidirectionalFieldData = (
	podType,
	podName,
	name,
	fieldTypeOption,
	relatedTypeOption,
) => {
	// In most cases, the 'data' passed to the field will be the 'data'
	// prop, unless it's the "Bidirectional Field"/"sister_id".
	const [ fieldItemData, setFieldItemData ] = useState( [] );

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
			setFieldItemData( [
				{
					id: '',
					name: __( 'Loading available fields…', 'pods' ),
					icon: '',
					edit_link: '',
					link: '',
					selected: false,
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
					setFieldItemData( [
						{
							id: '',
							name: __( 'No Related Fields Found', 'pods' ),
							icon: '',
							edit_link: '',
							link: '',
							selected: false,
						},
					] );
					return;
				}

				// Reduce the API results to an ID for the value and a label.
				const processedFields = results.fields.map( ( currentField ) => {
					// @todo is it just currentField?
					return {
						id: currentField.id.toString(),
						name: `${ currentField.label } (${ currentField.name }) [Pod: ${ currentField.parent_data?.name }]`,
						icon: '',
						edit_link: '',
						link: '',
						selected: false,
					};
				} );

				processedFields.unshift( {
					id: '',
					name: __( '-- Select Related Field --', 'pods' ),
					icon: '',
					edit_link: '',
					link: '',
					selected: false,
				} );

				setFieldItemData( processedFields );
			} catch ( error ) {
				setFieldItemData( {
					id: '',
					name: __( 'No Related Fields Found', 'pods' ),
					icon: '',
					edit_link: '',
					link: '',
					selected: false,
				} );
			}
		};

		loadBidirectionalFieldData();
	}, [ podType, podName, name, fieldTypeOption, relatedTypeOption, setFieldItemData ] );

	return {
		bidirectionFieldItemData: fieldItemData,
	};
};

export default useBidirectionalFieldData;
