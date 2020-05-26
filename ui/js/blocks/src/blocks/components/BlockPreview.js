/**
 * External dependencies
 */
import sanitizeHtml from 'sanitize-html';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

import { autop } from '@wordpress/autop';

import { RichText } from '@wordpress/block-editor';

import {
	__experimentalGetSettings,
	dateI18n,
	format as formatDate,
} from '@wordpress/date';

import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import renderTemplate from '../../utils/renderTemplate';
import { plainText } from '../../config/html';

/**
 * Renders an individual field to be used in a template.
 *
 * @param {Object} field Field data.
 * @param {Object} attributes All block attributes.
 */
const renderField = ( field, attributes ) => {
	const {
		name,
		fieldOptions,
		type,
	} = field;
	const fieldValue = attributes[ name ];

	if ( 'undefined' === typeof fieldValue ) {
		return null;
	}

	switch ( type ) {
		case 'TextControl': {
			return (
				<div key={ name } className="field--textcontrol">
					{ sanitizeHtml( fieldValue, plainText ) }
				</div>
			);
		}
		case 'TextareaControl': {
			const {
				auto_p: shouldAutoP,
			} = fieldOptions;

			const sanitizedText = sanitizeHtml( fieldValue, plainText );

			return (
				<div
					key={ name }
					className="field--textareacontrol"
					dangerouslySetInnerHTML={ {
						__html: shouldAutoP ? autop( sanitizedText ) : sanitizedText,
					} }
				/>
			);
		}
		case 'RichText': {
			return (
				<RichText.Content
					key={ name }
					tagName='p'
					value={ fieldValue }
					className="field--richtext"
				/>
			);
		}
		case 'CheckboxControl': {
			return (
				<div key={ name } className="field--checkbox">
					{ fieldValue ? __( 'Yes' ) : __( 'No' ) }
				</div>
			);
		}
		case 'CheckboxGroup': {
			const {
				options,
			} = fieldOptions;

			const values = Array.isArray( fieldValue )
				? fieldValue.filter( value => !! value.checked )
				: [];

			return (
				<div key={ name } className="field--checkbox-group">
					{ values.length
						? values.map( ( value, index ) => {
							const matchingOption = options.find( option => value.value === option.value );

							return (
								<span
									className="field--checkbox-group__item"
									key={ value.value }
								>
									{ matchingOption.label }
									{ ( index < values.length - 1 ) ? ', ' : '' }
								</span>
							)
						} )
						: 'N/A' }
				</div>
			);
		}
		case 'RadioControl': {
			const { options } = fieldOptions;

			const matchingOption = options.find( option => fieldValue === option.value );

			return (
				<div key={ name } className="field--radio-control">
					{ !! matchingOption ? matchingOption.label : 'N/A' }
				</div>
			);
		}
		case 'SelectControl': {
			// Could be either a Select with "multiple" values or not.
			if ( ! Array.isArray( fieldValue ) ) {
				return (
					<div key={ name } className="field--select-control">
						{ fieldValue.label || 'N/A' }
					</div>
				);
			} else {
				const values = fieldValue;

				return (
					<div key={ name } className="field--select-control field--multiple-select-control">
						{ values.length
							? values.map( ( value, index ) => {
								return (
									<span
										className="field--select-group__item"
										key={ value }
									>
										{ value.label }
										{ ( index < values.length - 1 ) ? ', ' : '' }
									</span>
								)
							} )
							: 'N/A' }
					</div>
				);
			}
		}
		case 'DateTimePicker': {
			const dateFormat = __experimentalGetSettings().formats.datetime;

			return (
				<div key={ name } className="field--date-time">
					<time
						dateTime={ formatDate( 'c', fieldValue ) }
					>
						{ dateI18n( dateFormat, fieldValue ) }
					</time>
				</div>
			);
		}
		case 'NumberControl': {
			let { locale } = __experimentalGetSettings().l10n;

			locale = locale.replace( '_', '-' );

			return (
				<div key={ name } className="field--number">
					{ !! fieldValue && fieldValue.toLocaleString( locale ) }
				</div>
			);
		}
		case 'MediaUpload': {
			return (
				<div key={ name } className="field--media-upload">
					{ fieldValue && fieldValue.url || 'N/A' }
				</div>
			);
		}
		case 'ColorPicker': {
			return (
				<div
					key={ name } className="field--color"
					style={ {
						color: fieldValue,
					} }
				>
					{ fieldValue }
				</div>
			);
		}
		default:
			return null;
	}
};

const BlockPreview = ( {
	block,
	attributes = {}
} ) => {
	const {
		fields = [],
		template,
		blockName,
		renderType,
	} = block;

	if ( 'php' === renderType ) {
		return (
			<ServerSideRender
				block={ blockName }
				attributes={ attributes }
			/>
		);
	}

	return (
		<>
			{ renderTemplate( template, fields, attributes, renderField ) }
		</>
	)
};

export default BlockPreview;

