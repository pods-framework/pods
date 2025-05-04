import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';

import './copy-button.scss';

const copyToClipboard = async ( text ) => {
	try {
		// @link https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText
		// clipboard.writeText only works in secure context (https).
		await navigator.clipboard.writeText( text );
	} catch {
		// Fallback for older browsers, or in unsecure contexts (http).
		const input = document.createElement( 'input' );
		input.style.position = 'fixed';
		input.style.left = '-9999px';
		input.style.top = '-9999px';
		document.body.appendChild( input );
		input.value = text;
		input.select();
		document.execCommand( 'copy' );
		document.body.removeChild( input );
		Promise.resolve();
	}
};

// https://lucide.dev/icons/copy
const CopyButton = ( { label = 'Copy', textToCopy = null, onClick = null } ) => {
	const [ copied, setCopied ] = useState( false );
	const handleClick = async () => {
		if ( onClick ) {
			onClick();
		} else {
			await copyToClipboard( textToCopy );
		}
		setCopied( true );
		const timeout = setTimeout( () => {
			setCopied( false );
			clearTimeout( timeout );
		}, 3000 );
	};
	return (
		<button
			className="pods-field_copy-button"
			aria-label={ label }
			onClick={ handleClick }
		>
			{ copied ? (
				<span>
					({ __( 'Copied', 'pods' ) })
				</span>
			) : (
				<svg
					xmlns="http://www.w3.org/2000/svg"
					width="16"
					height="16"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					strokeWidth="1.5"
					strokeLinecap="round"
					strokeLinejoin="round"
					aria-hidden="true"
					focusable="false"
				>
					<rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
					<path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
				</svg>
			) }
		</button>
	);
};

CopyButton.propTypes = {
	label: PropTypes.string.isRequired,
	textToCopy: PropTypes.string,
	onClick: PropTypes.func,
};

export default CopyButton;
