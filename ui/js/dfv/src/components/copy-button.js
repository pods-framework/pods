import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';

import './copy-button.scss';

// https://lucide.dev/icons/copy
const CopyButton = ( { label, textToCopy, onClick } ) => {
	const [ copied, setCopied ] = useState( false );
	const handleClick = async () => {
		if ( onClick ) {
			onClick();
		} else {
			await navigator.clipboard.writeText( textToCopy );
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
					({ __( 'Copied name', 'pods' ) })
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

CopyButton.defaultProps = {
	label: 'Copy',
	textToCopy: null,
	onClick: null,
};

CopyButton.propTypes = {
	label: PropTypes.string.isRequired,
	textToCopy: PropTypes.string,
	onClick: PropTypes.func,
};

export default CopyButton;
