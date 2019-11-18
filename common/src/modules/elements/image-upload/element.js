/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { MediaUpload } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { Button, Image } from '@moderntribe/common/elements';
import { Close as CloseIcon } from '@moderntribe/common/icons';
import './style.pcss';

export const renderImageUploadButton = ( disabled, label ) => ( { open } ) => (
	<Button
		onClick={ open }
		className={ [ 'tribe-editor__button--sm', 'tribe-editor__image-upload__upload-button' ] }
		disabled={ disabled }
	>
		{ label }
	</Button>
);

export const renderImage = ( disabled, image, onRemove ) => (
	<div className="tribe-editor__image-upload__image-wrapper">
		<Image
			src={ image.src }
			alt={ image.alt }
			className="tribe-editor__image-upload__image"
		/>
		<Button
			className="tribe-editor__image-upload__remove-button"
			onClick={ onRemove }
			disabled={ disabled }
		>
			<CloseIcon />
			<span className="tribe-editor__image-upload__remove-button-text">
				{ __( 'remove', 'tribe-common' ) }
			</span>
		</Button>
	</div>
);

const ImageUpload = ( {
	buttonDisabled,
	buttonLabel,
	className,
	description,
	image,
	onRemove,
	onSelect,
	removeButtonDisabled,
	title,
} ) => {
	const hasImageClass = { 'tribe-editor__image-upload--has-image': image.id };

	return (
		<div className={ classNames(
			'tribe-editor__image-upload',
			hasImageClass,
			className,
		) }>
			{ title && <h3 className="tribe-editor__image-upload__title">{ title }</h3> }
			<div className="tribe-editor__image-upload__content">
				{ description && (
					<p className="tribe-editor__image-upload__description">{ description }</p>
				) }
				{
					image.id
						? renderImage( removeButtonDisabled, image, onRemove )
						: (
							<MediaUpload
								onSelect={ onSelect }
								type="image"
								render={ renderImageUploadButton( buttonDisabled, buttonLabel ) }
								value={ image.id }
							/>
						)
				}
			</div>
		</div>
	);
};

ImageUpload.propTypes = {
	buttonDisabled: PropTypes.bool,
	buttonLabel: PropTypes.string,
	className: PropTypes.string,
	description: PropTypes.string,
	image: PropTypes.shape( {
		alt: PropTypes.string.isRequired,
		id: PropTypes.number.isRequired,
		src: PropTypes.string.isRequired,
	} ).isRequired,
	onRemove: PropTypes.func.isRequired,
	onSelect: PropTypes.func.isRequired,
	removeButtonDisabled: PropTypes.bool,
	title: PropTypes.string,
};

ImageUpload.defaultProps = {
	onRemove: noop,
	onSelect: noop,
};

export default ImageUpload;
