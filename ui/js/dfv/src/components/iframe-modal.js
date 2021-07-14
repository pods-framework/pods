/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Modal } from '@wordpress/components';

/**
 * Other Pods dependencies
 */
import './iframe-modal.scss';

const IframeModal = ( {
	title,
	iframeSrc,
	onClose,
} ) => {
	return (
		<Modal
			className="pods-iframe-modal"
			title={ title }
			isDismissible={ true }
			onRequestClose={ onClose }
			focusOnMount={ true }
		>
			<iframe
				src={ iframeSrc }
				title={ title }
				className="pods-iframe-modal__iframe"
			/>
		</Modal>
	);
};

IframeModal.propTypes = {
	title: PropTypes.string.isRequired,
	iframeSrc: PropTypes.string.isRequired,
	onClose: PropTypes.func.isRequired,
};

export default IframeModal;
