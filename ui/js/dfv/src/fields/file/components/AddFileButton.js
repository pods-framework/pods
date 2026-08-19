/**
 * External dependencies
 */
import React, { useRef, useEffect } from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { createUploader } from './createUploader';

/**
 * AddFileButton component
 * Handles file uploads using either media modal or plupload
 * @param root0
 * @param root0.fileUploader
 * @param root0.fileLimit
 * @param root0.currentFileCount
 * @param root0.onFilesAdded
 * @param root0.onError
 * @param root0.buttonText
 * @param root0.fileModalTitle
 * @param root0.fileModalAddButton
 * @param root0.limitTypes
 * @param root0.limitExtensions
 * @param root0.filePostId
 * @param root0.fileAttachmentTab
 * @param root0.pluploadInit
 * @param root0.disabled
 */
const AddFileButton = ( {
	fileUploader = 'attachment',
	fileLimit,
	currentFileCount,
	onFilesAdded,
	onError,
	onProgressUpdate,
	buttonText,
	fileModalTitle,
	fileModalAddButton,
	limitTypes,
	limitExtensions,
	filePostId,
	fileAttachmentTab,
	pluploadInit,
	disabled = false,
} ) => {
	const uploaderRef = useRef( null );
	const buttonContainerRef = useRef( null );
	const buttonIdRef = useRef( `pods-file-button-${ Math.random().toString( 36 ).substr( 2, 9 ) }` );
	const isInitializedRef = useRef( false );

	useEffect( () => {
		// Only initialize once
		if ( isInitializedRef.current ) {
			return;
		}

		// Wait for button to be rendered
		if ( ! buttonContainerRef.current ) {
			return;
		}

		// For plupload, find the actual button element inside the container
		const buttonElement = fileUploader === 'plupload'
			? buttonContainerRef.current.querySelector( 'button' ) || buttonContainerRef.current
			: null;

		// Create the uploader instance
		const uploader = createUploader( {
			fileUploader,
			fileLimit,
			currentFileCount,
			onFilesAdded,
			onProgressUpdate: onProgressUpdate || ( () => {} ),
			onError: onError || ( ( err ) => {
				if ( window.console ) {
					// eslint-disable-next-line no-console
					console.error( 'File upload error:', err );
				}
			} ),
			fileModalTitle,
			fileModalAddButton,
			limitTypes,
			limitExtensions,
			filePostId,
			fileAttachmentTab,
			pluploadInit,
		} );

		uploaderRef.current = uploader;
		isInitializedRef.current = true;

		// For plupload, we need to initialize it with the button element
		if ( fileUploader === 'plupload' && buttonElement && uploader ) {
			uploader.initialize( buttonElement );
		}

		// Cleanup on unmount
		return () => {
			if ( uploaderRef.current ) {
				uploaderRef.current.cleanup();
			}
			isInitializedRef.current = false;
		};
	}, [ fileUploader ] ); // Only re-initialize if uploader type changes

	// Update the uploader config when these values change, without re-initializing
	useEffect( () => {
		if ( uploaderRef.current ) {
			uploaderRef.current.config = {
				...uploaderRef.current.config,
				fileLimit,
				currentFileCount,
				onFilesAdded,
				onProgressUpdate: onProgressUpdate || uploaderRef.current.config.onProgressUpdate,
				onError: onError || uploaderRef.current.config.onError,
				fileModalTitle,
				fileModalAddButton,
				limitTypes,
				limitExtensions,
				filePostId,
				fileAttachmentTab,
				pluploadInit,
			};

			// Update the callback references
			uploaderRef.current.onFilesAdded = onFilesAdded;
			if ( onProgressUpdate ) {
				uploaderRef.current.onProgressUpdate = onProgressUpdate;
			}
			if ( onError ) {
				uploaderRef.current.onError = onError;
			}
		}
	}, [
		fileLimit,
		currentFileCount,
		onFilesAdded,
		onProgressUpdate,
		onError,
		fileModalTitle,
		fileModalAddButton,
		limitTypes,
		limitExtensions,
		filePostId,
		fileAttachmentTab,
		pluploadInit,
	] );

	const handleClick = ( event ) => {
		// For plupload, don't do anything - plupload handles it
		if ( fileUploader === 'plupload' ) {
			// Let plupload handle the click
			return;
		}

		// For other uploaders, prevent default and open the modal
		event.preventDefault();
		if ( uploaderRef.current ) {
			uploaderRef.current.open();
		}
	};

	return (
		<div className="pods-file-add-button" ref={ buttonContainerRef } id={ buttonIdRef.current }>
			<Button
				variant="secondary"
				onClick={ handleClick }
				disabled={ disabled }
			>
				{ buttonText || __( 'Add File', 'pods' ) }
			</Button>
		</div>
	);
};

AddFileButton.propTypes = {
	fileUploader: PropTypes.string,
	fileLimit: PropTypes.oneOfType( [ PropTypes.string, PropTypes.number ] ),
	currentFileCount: PropTypes.number,
	onFilesAdded: PropTypes.func.isRequired,
	onProgressUpdate: PropTypes.func,
	onError: PropTypes.func,
	buttonText: PropTypes.string,
	fileModalTitle: PropTypes.string,
	fileModalAddButton: PropTypes.string,
	limitTypes: PropTypes.string,
	limitExtensions: PropTypes.string,
	filePostId: PropTypes.oneOfType( [ PropTypes.string, PropTypes.number ] ),
	fileAttachmentTab: PropTypes.string,
	pluploadInit: PropTypes.object,
	disabled: PropTypes.bool,
};

export default AddFileButton;

