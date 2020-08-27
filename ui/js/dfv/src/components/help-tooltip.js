import React, { useState, useRef, useEffect } from 'react';
import sanitizeHtml from 'sanitize-html';
import * as PropTypes from 'prop-types';

import { Dashicon } from '@wordpress/components';

import { richTextInlineOnly } from '../../../blocks/src/config/html';
import './help-tooltip.scss';

const HelpTooltip = ( {
	helpText,
	helpLink,
} ) => {
	const [ showTooltip, setShowTooltip ] = useState( false );
	const tooltipRef = useRef( null );

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
		<div
			className="pods-help-tooltip"
			tabIndex="0"
			onFocus={ () => setShowTooltip( true ) }
		>
			<Dashicon
				icon="editor-help"
				className="pods-help-tooltip__icon"
			/>

			{ showTooltip && (
				<div
					className="pods-help-tooltip__tooltip"
					tabIndex="-1"
					ref={ tooltipRef }
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
