/**
 * External dependencies
 */
import React, { useState, useEffect, useCallback } from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Pods components
 */
import ListValues from '../pick/list-values';
import AddFileButton from './components/AddFileButton';
import FileUploadQueue from './components/FileUploadQueue';

/**
 * Other Pods dependencies
 */
import useMediaItemData from './hooks/useMediaItemData';
import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const FileList = ( props ) => {
	const {
		fieldConfig: {
			name,
			fieldItemData: fieldItemData = [],
			file_format_type: fileFormatType,
			file_limit: fileLimit,
			file_add_button: fileAddButton,
			file_modal_title: fileModalTitle,
			file_modal_add_button: fileModalAddButton,
			file_attachment_tab: fileAttachmentTab,
			file_linked: fileShowDownloadLink = false,
			file_show_edit_link: fileShowEditLink = false,
			file_edit_title: fileShowEditTitle = false,
			file_uploader: fileUploader = 'attachment',
			file_field_template: fileFieldTemplate = 'rows',
			limit_extensions: limitExtensions,
			limit_types: limitTypes,
			file_post_id: filePostId,
			plupload_init: pluploadInit,
			htmlAttr: htmlAttributes = {},
			read_only: readOnly = false,
		},
		value,
		setValue,
		setHasBlurred,
	} = props;

	const correctedLimit = fileFormatType === 'single'
		? '1'
		: fileLimit;

	const [ mediaItemsData, setMediaItemsData ] = useMediaItemData(
		value,
		fieldItemData,
		name,
	);

	const [ uploadQueue, setUploadQueue ] = useState( [] );

	// Convert value to array format
	const getValueArray = useCallback( () => {
		if ( ! value ) {
			return [];
		}

		let valueArray = [];

		if ( Array.isArray( value ) ) {
			valueArray = value;
		} else if ( 'string' === typeof value ) {
			valueArray = value.split( ',' ).filter( ( v ) => v );
		} else if ( 'number' === typeof value ) {
			valueArray = [ value ];
		}

		return valueArray.map( ( id ) => ( {
			label: '', // Will be filled by ListValues from mediaItemsData
			value: id,
		} ) );
	}, [ value ] );

	// Update value when it changes from parent
	const [ currentValue, setCurrentValue ] = useState( getValueArray() );

	useEffect( () => {
		setCurrentValue( getValueArray() );
	}, [ getValueArray ] );

	const handleSetValue = ( newValue ) => {
		if ( ! newValue || 0 === newValue.length ) {
			setValue( undefined );
			setMediaItemsData( [] );
			setCurrentValue( [] );
		} else if ( fileFormatType === 'single' ) {
			setValue( newValue[ 0 ] );
			setCurrentValue( [ { label: '', value: newValue[ 0 ].toString() } ] );
		} else {
			setValue( newValue.join( ',' ) );
			setCurrentValue( newValue.map( ( id ) => ( { label: '', value: id.toString() } ) ) );
		}

		setHasBlurred( true );
	};

	// Handle title changes from inline editing
	const handleTitleChange = ( itemId, newTitle ) => {
		setMediaItemsData( ( prevData ) => prevData.map( ( item ) => {
			return ( item?.id.toString() === itemId.toString() )
				? { ...item, name: newTitle }
				: item;
		} ) );
	};

	// Handle upload progress updates
	const handleProgressUpdate = useCallback( ( queue ) => {
		setUploadQueue( queue );
	}, [] );

	// Handle files added from uploader
	const handleFilesAdded = useCallback( ( newFiles ) => {
		// Defensive check: ensure newFiles is an array
		if ( ! Array.isArray( newFiles ) ) {
			if ( window.console ) {
				// eslint-disable-next-line no-console
				console.error( 'handleFilesAdded received non-array:', newFiles );
			}
			return;
		}

		// Handle the file limit
		const parsedLimit = parseInt( correctedLimit, 10 );
		let updatedMediaData;

		setMediaItemsData( ( prevMediaData ) => {
			if ( 0 === parsedLimit || prevMediaData.length + newFiles.length <= parsedLimit ) {
				// No limit or within limit - add all new files
				updatedMediaData = [ ...prevMediaData, ...newFiles ];
			} else {
				// Enforce limit: keep the last N files, FIFO/queue style
				const combined = [ ...prevMediaData, ...newFiles ];
				updatedMediaData = combined.slice( combined.length - parsedLimit );
			}

			const updatedValues = updatedMediaData.map( ( file ) => file.id );

			if ( fileFormatType === 'single' ) {
				setValue( updatedValues[ 0 ] );
			} else {
				setValue( updatedValues.join( ',' ) );
			}

			setHasBlurred( true );

			return updatedMediaData;
		} );
	}, [ correctedLimit, fileFormatType, setValue, setHasBlurred ] );

	const formattedValues = currentValue.map( ( item ) => {
		const matchingData = mediaItemsData.find(
			( data ) => data.id.toString() === item.value.toString()
		);

		return {
			label: matchingData?.name || item.label || `ID: ${ item.value }`,
			value: item.value,
		};
	} );

	const isAtLimit = parseInt( correctedLimit, 10 ) > 0 && currentValue.length >= parseInt( correctedLimit, 10 );

	const htmlName = htmlAttributes.name || name;

	if ( toBool( readOnly ) && 0 === formattedValues.length ) {
		return (
			<span>
				{ __( 'No files have been uploaded.', 'pods' ) }
			</span>
		);
	}

	return (
		<div
			className={ `pods-file-container pods-file-container--template-${ fileFieldTemplate }` }
		>
			<ListValues
				fieldName={ name }
				htmlAttrs={ htmlAttributes }
				value={ formattedValues }
				setValue={ handleSetValue }
				fieldItemData={ mediaItemsData }
				setFieldItemData={ setMediaItemsData }
				isMulti={ fileFormatType !== 'single' }
				limit={ parseInt( correctedLimit, 10 ) || 0 }
				defaultIcon="dashicons-media-default"
				showIcon={ true }
				largeIcons={ fileFieldTemplate === 'tiles' }
				showDownloadLink={ toBool( fileShowDownloadLink ) }
				showEditLink={ toBool( fileShowEditLink ) }
				showEditTitle={ toBool( fileShowEditTitle ) }
				editIframeTitle={ `${ name }: Edit File` }
				readOnly={ toBool( readOnly ) }
				onTitleChange={ handleTitleChange }
			/>

			{ uploadQueue.length > 0 && (
				<FileUploadQueue files={ uploadQueue } />
			) }

			{ ! isAtLimit ? (
				<div className="pods-file-add-button" style={ { marginTop: '10px' } }>
					<AddFileButton
						fileUploader={ fileUploader }
						fileLimit={ correctedLimit }
						currentFileCount={ currentValue.length }
						onFilesAdded={ handleFilesAdded }
						onProgressUpdate={ handleProgressUpdate }
						buttonText={ fileAddButton }
						fileModalTitle={ fileModalTitle }
						fileModalAddButton={ fileModalAddButton }
						limitTypes={ limitTypes }
						limitExtensions={ limitExtensions }
						filePostId={ filePostId }
						fileAttachmentTab={ fileAttachmentTab }
						pluploadInit={ pluploadInit }
						disabled={ toBool( readOnly ) }
					/>
				</div>
			) : null }

			{ formattedValues.map( ( selectedValue ) => (
				<input
					name={ `${ htmlName }[${ selectedValue.value }][id]` }
					key={ `${ name }-${ selectedValue.value }` }
					type="hidden"
					value={ selectedValue.value }
				/>
			) ) }
		</div>
	);
};

FileList.propTypes = {
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

export default FileList;
