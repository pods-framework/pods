import { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';

/**
 * Helper function to retrieve information about a media library post from its post ID.
 *
 * @param {number} mediaID Post ID for the media.
 *
 * @return {Object} Object of data about the media.
 */
const getMediaItemData = async ( mediaID ) => {
	try {
		const result = await apiFetch( { path: `/wp/v2/media/${ mediaID }` } );

		return {
			id: mediaID,
			icon: result?.media_details?.sizes?.thumbnail?.source_url,
			name: result.title.rendered,
			// @todo This should be based on the adminurl instead of hardcoded to /wp-admin/ -- fix this later.
			edit_link: `/wp-admin/post.php?post=${ mediaID }&action=edit`,
			link: result.link,
			download: result.source_url,
		};
	} catch ( e ) {
		return {
			id: mediaID,
		};
	}
};

// The `value` prop will probably be a comma-separated string of media post IDs,
// but we need to pass an array of objects with data about the media
// to the Backbone view/model. Only make the expensive API requests if
// we don't have data about a media post on initial page load.
const useMediaItemData = ( value, fieldItemData, fileFieldName ) => {
	const [ mediaItemsData, setMediaItemsData ] = useState( [] );

	useEffect( () => {
		if ( ! value ) {
			setMediaItemsData( [] );
			return;
		}

		const getAndSetMediaData = async ( mediaIDs ) => {
			// Check first if all items are available in fieldItemData,
			// if not we need to do a REST API request to get the data.
			let areAllItemsAvailableFromFieldItemData = true;

			const dataFromFieldItemData = mediaIDs.map( ( mediaID ) => {
				const matchingFieldItemData = fieldItemData.find(
					( fieldItem ) => Number( fieldItem.id ) === Number( mediaID ),
				);

				if ( ! matchingFieldItemData ) {
					areAllItemsAvailableFromFieldItemData = false;
					return null;
				}

				return matchingFieldItemData;
			} );

			if ( areAllItemsAvailableFromFieldItemData ) {
				setMediaItemsData( dataFromFieldItemData );
				return;
			}

			// If we didn't find everything, fall back to the API request.
			const results = await Promise.all( mediaIDs.map( getMediaItemData ) );
			setMediaItemsData( results );
		};

		if ( Array.isArray( value ) ) {
			getAndSetMediaData( value );
		} else if ( 'object' === typeof value ) {
			setMediaItemsData( value );
		} else if ( 'string' === typeof value ) {
			getAndSetMediaData( value.split( ',' ) );
		} else if ( 'number' === typeof value ) {
			getAndSetMediaData( [ value ] );
		} else {
			// eslint-disable-next-line no-console
			console.error( `Invalid value type for file field: ${ fileFieldName }` );
		}
	}, [] );

	return [
		mediaItemsData,
		setMediaItemsData,
	];
};

export default useMediaItemData;
