import React from 'react';
import sanitizeHtml from 'sanitize-html';
import Tippy from '@tippyjs/react';
import PropTypes from 'prop-types';

import { Dashicon } from '@wordpress/components';

import { richTextInlineOnly } from '../../../blocks/src/config/html';
import 'tippy.js/dist/tippy.css';
import './help-tooltip.scss';

const HelpTooltip = ( {
	helpText,
	helpLink,
} ) => {
	return (
		<Tippy
			className="pods-help-tooltip"
			// z-index is 1 higher than the Modal component
			trigger="click"
			zIndex={ 100001 }
			content={ helpLink ? (
				<a href={ helpLink } target="_blank" rel="noreferrer">
					<span
						dangerouslySetInnerHTML={ {
							__html: sanitizeHtml( helpText, richTextInlineOnly ),
						} }
					/>
					<Dashicon icon="external" />
				</a>
			) : (
				<span
					dangerouslySetInnerHTML={ {
						__html: sanitizeHtml( helpText, richTextInlineOnly ),
					} }
				/>
			) }
		>
			<span
				tabIndex="0"
				role="button"
				className="pods-help-tooltip__icon"
			>
				<Dashicon icon="editor-help" />
			</span>
		</Tippy>
	);
};

HelpTooltip.propTypes = {
	helpText: PropTypes.string.isRequired,
	helpLink: PropTypes.string,
};

export default HelpTooltip;
