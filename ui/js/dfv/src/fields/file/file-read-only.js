
/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Pods components
 */
// @todo move ListSelectValues out of the Pick field because it's now reusable.
import ListSelectValues from '../pick/list-select-values';

/**
 * Other Pods dependencies
 */
import useMediaItemData from './hooks/useMediaItemData';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const FileReadOnly = ( props ) => {
	const {
		fieldConfig: {
			name,
			fieldItemData: fieldItemData = [],
			file_format_type: fileFormatType,
			file_limit: fileLimit,
		},
		value,
	} = props;

	const correctedLimit = fileFormatType === 'single'
		? '1'
		: fileLimit;

	const [ mediaItemsData ] = useMediaItemData(
		value,
		fieldItemData,
		name,
	);

	const formattedValues = mediaItemsData.map( ( item ) => ( {
		label: item.name,
		value: item.id,
	} ) );

	console.log( 'readonly File value', value, mediaItemsData );
	// @todo take getMediaItemData() from FileFull component and use it to get data in the right format.

	if ( 0 === formattedValues.length ) {
		return (
			<span>
				{ __( 'No files have been uploaded.', 'pods' ) }
			</span>
		);
	}

	return (
		<ListSelectValues
			fieldName={ name }
			value={ formattedValues }
			setValue={ () => {} }
			fieldItemData={ mediaItemsData }
			setFieldItemData={ () => {} }
			isMulti={ fileFormatType !== 'single' }
			limit={ parseInt( correctedLimit, 10 ) || 0 }
			showIcon={ true }
			showViewLink={ false }
			showEditLink={ false }
			readOnly={ true }
		/>
	);
};

FileReadOnly.propTypes = {
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

export default FileReadOnly;
