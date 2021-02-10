import React, { useState, useRef, useEffect } from 'react';
import sanitizeHtml from 'sanitize-html';
import PropTypes from 'prop-types';

import { Dashicon } from '@wordpress/components';

import { richTextInlineOnly } from '../../../blocks/src/config/html';
import './help-tooltip.scss';

const ENTER_KEY = 13;

const HelpTooltip = ( {
	helpText,
	helpLink,
} ) => {
	const [ showTooltip, setShowTooltip ] = useState( false );
	const tooltipRef = useRef( null );

	const toggleTooltip = () => setShowTooltip( ( previousValue ) => ! previousValue );

	useEffect( () => {
		if ( tooltipRef?.current ) {
			tooltipRef.current.focus();
		}

		const handleClickOutside = ( event ) => {
			if (
				tooltipRef?.current &&
				! tooltipRef.current.contains( event.target )
			) {
				setShowTooltip( false );
			}
		};

		document.addEventListener( 'mousedown', handleClickOutside );

		return () => {
			document.removeEventListener( 'mousedown', handleClickOutside );
		};
	}, [ tooltipRef, setShowTooltip ] );

	return (
		<div className="pods-help-tooltip">
			<span
				className="pods-help-tooltip__icon"
				tabIndex="0"
				onClick={ ( event ) => {
					event.preventDefault();
					toggleTooltip();
				} }
				onKeyPress={ ( event ) => event.charCode === ENTER_KEY && toggleTooltip() }
				role="button"
			>
				<Dashicon icon="editor-help" />
			</span>

			{ showTooltip && (
				// eslint-disable-next-line
				<div
					className="pods-help-tooltip__tooltip"
					tabIndex="-1"
					ref={ tooltipRef }
					onClick={ () => setShowTooltip( false ) }
				>
					<i></i>
					{ helpLink
						? (
							<a href={ helpLink } target="_blank" rel="noreferrer">
								<span
									dangerouslySetInnerHTML={ {
										__html: sanitizeHtml( helpText, richTextInlineOnly ),
									} }
								/>
								<Dashicon
									icon="external"
								/>
							</a>
						)
						: (
							<span
								dangerouslySetInnerHTML={ {
									__html: sanitizeHtml( helpText, richTextInlineOnly ),
								} }
							/>
						)
					}
				</div>
			) }
		</div>
	);
};

HelpTooltip.propTypes = {
	helpText: PropTypes.string.isRequired,
	helpLink: PropTypes.string,
};

export default HelpTooltip;
