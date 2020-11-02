import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';

import apiFetch from '@wordpress/api-fetch';

import MarionetteAdapter from 'dfv/src/fields/marionette-adapter';
import { File as FileView } from './file-upload';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

// @todo add tests
const File = ( props ) => {
	const {
		fieldConfig = {},
		htmlAttr = {},
		value,
		setValue,
	} = props;

	const [ collectionData, setCollectionData ] = useState( [] );

	const setValueFromModels = ( models ) => {
		if ( Array.isArray( models ) ) {
			setValue( models.map( ( model ) => model.id ).join( ',' ) );
		} else {
			setValue( models.get( 'id' ) );
		}
	};

	// Force the limit to 1 if this the field only allows a single upload.
	const correctedLimit = fieldConfig.file_format_type === 'single' ? 1 : fieldConfig.file_limit;

	// The `value` prop will be a comma-separated string of media post IDs,
	// but we need to pass an array of objects with data about the media
	// to the Backbone view/model.
	useEffect( () => {
		if ( ! value || ! value.length ) {
			setCollectionData( [] );
			return;
		}

		const allMediaIDs = value.split( ',' );

		const getMediaItemData = async ( mediaID ) => {
			try {
				const result = await apiFetch( { path: `/wp/v2/media/${ mediaID }` } );

				return {
					id: mediaID,
					// eslint-disable-next-line camelcase
					icon: result?.media_details?.sizes?.thumbnail?.source_url,
					name: result.title.rendered,
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

		const getAndSetMediaData = async ( mediaIDs ) => {
			const results = await Promise.all( mediaIDs.map( getMediaItemData ) );
			setCollectionData( results );
		};

		getAndSetMediaData( allMediaIDs );
	}, [ value ] );

	return (
		<MarionetteAdapter
			{ ...props }
			htmlAttr={ htmlAttr }
			fieldConfig={ {
				...fieldConfig,
				file_limit: correctedLimit,
			} }
			View={ FileView }
			value={ collectionData }
			setValue={ setValueFromModels }
		/>
	);
};

File.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE.isRequired,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default File;
