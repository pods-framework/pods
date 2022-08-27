/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * Pods components
 */
import MarionetteAdapter from 'dfv/src/fields/marionette-adapter';
import { File as FileView } from './file-upload';

/**
 * Other Pods dependencies
 */
import useMediaItemData from './hooks/useMediaItemData';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const FileFull = ( props ) => {
	const {
		fieldConfig = {},
		value,
		setValue,
		setHasBlurred,
	} = props;

	const {
		name,
		fieldItemData = [],
		file_format_type: fileFormatType,
		file_limit: fileLimit,
		htmlAttr: htmlAttributes = {},
	} = fieldConfig;

	const [ mediaItemsData, setMediaItemsData ] = useMediaItemData(
		value,
		fieldItemData,
		name,
	);

	const setValueFromModels = ( models ) => {
		if ( Array.isArray( models ) ) {
			setValue( models.map( ( model ) => model.id ).join( ',' ) );

			setMediaItemsData( models.map( ( model ) => model.attributes ) );
		} else {
			setValue( models.get( 'id' ) );

			setMediaItemsData( models.get( 'attributes' ) );
		}

		setHasBlurred( true );
	};

	// Force the limit to 1 if this the field only allows a single upload.
	const correctedLimit = fileFormatType === 'single'
		? '1'
		: fileLimit;

	return (
		<MarionetteAdapter
			{ ...props }
			fieldConfig={ {
				...fieldConfig,
				file_limit: correctedLimit,
				htmlAttr: htmlAttributes,
			} }
			View={ FileView }
			value={ mediaItemsData }
			setValue={ setValueFromModels }
		/>
	);
};

FileFull.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.oneOfType( [
		PropTypes.arrayOf(
			PropTypes.oneOfType( [
				PropTypes.string,
				PropTypes.number,
			] )
		),
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default FileFull;
