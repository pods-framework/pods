import React, { useState, useEffect } from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import { toBool } from 'dfv/src/helpers/booleans';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

const Oembed = ( {
	value,
	setValue,
	fieldConfig = {},
	setHasBlurred,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		name,
		oembed_height: height,
		oembed_show_preview: showPreview,
		oembed_width: width,
	} = fieldConfig;

	const [ preview, setPreview ] = useState( null );
	const [ isPreviewUpdated, setIsPreviewUpdated ] = useState( false );
	const [ previewFailure, setPreviewFailure ] = useState( null );

	const handleChange = ( event ) => setValue( event.target.value );

	const handleBlur = () => {
		setIsPreviewUpdated( false );
		setHasBlurred();
	};

	useEffect( () => {
		if ( isPreviewUpdated || ! toBool( showPreview ) || ! value ) {
			setPreview( null );
			return;
		}

		const updatePreview = async () => {
			const args = { url: value };

			if ( !! height && 0 !== parseInt( height, 10 ) ) {
				args.maxheight = height;
			}

			if ( !! width && 0 !== parseInt( width, 10 ) ) {
				args.maxwidth = width;
			}

			try {
				const embedProxyResponse = await apiFetch( {
					path: addQueryArgs( '/oembed/1.0/proxy', args ),
				} );

				setPreview( embedProxyResponse.html );
				setPreviewFailure( null );
			} catch ( e ) {
				setPreview( null );
				setPreviewFailure( __( 'Failed to load the embed preview', 'pods' ) );
			}
		};

		updatePreview();
	}, [ value, showPreview, isPreviewUpdated ] );

	return (
		<>
			<input
				id={ htmlAttributes.id || `pods-form-ui-${ name }` }
				name={ htmlAttributes.name || name }
				className={ classnames( 'pods-form-ui-field pods-form-ui-field-type-oembed', htmlAttributes.class ) }
				type="text"
				value={ value || '' }
				onChange={ handleChange }
				onBlur={ handleBlur }
			/>

			{ showPreview && !! preview && (
				<>
					<p className="howto">
						{ __( 'Preview', 'pods' ) }
					</p>

					{ !! previewFailure
						? (
							<p className="pods-oembed-preview-failure">
								{ previewFailure }
							</p>
						) : (
							<div
								className="pods-oembed-preview"
								dangerouslySetInnerHTML={ { __html: preview } }
							/>
						)
					}
				</>
			) }
		</>
	);
};

Oembed.defaultProps = {
	value: '',
};

Oembed.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Oembed;
