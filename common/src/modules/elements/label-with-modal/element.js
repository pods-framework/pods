/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import LabeledItem from '@moderntribe/common/elements/labeled-item/element';
import ModalButton from '@moderntribe/common/elements/modal-button/element';
import './style.pcss';

const LabelWithModal = ( {
	className,
	isOpen,
	label,
	modalButtonDisabled,
	modalButtonLabel,
	modalClassName,
	modalContent,
	modalOverlayClassName,
	modalTitle,
	onClick,
	onClose,
	onOpen,
} ) => (
	<LabeledItem
		className={ classNames( 'tribe-editor__label-with-modal', className ) }
		label={ label }
	>
		<ModalButton
			className="tribe-editor__label-with-modal__modal-button"
			disabled={ modalButtonDisabled }
			isOpen={ isOpen }
			label={ modalButtonLabel }
			modalClassName={ modalClassName }
			modalContent={ modalContent }
			modalOverlayClassName={ modalOverlayClassName }
			modalTitle={ modalTitle }
			onClick={ onClick }
			onClose={ onClose }
			onOpen={ onOpen }
		/>
	</LabeledItem>
);

LabelWithModal.defaultProps = {
	onClick: noop,
	onClose: noop,
	onOpen: noop,
};

LabelWithModal.propTypes = {
	className: PropTypes.string,
	isOpen: PropTypes.bool,
	label: PropTypes.node,
	modalButtonDisabled: PropTypes.bool,
	modalButtonLabel: PropTypes.string,
	modalClassName: PropTypes.string,
	modalContent: PropTypes.node,
	modalOverlayClassName: PropTypes.string,
	modalTitle: PropTypes.string,
	onClick: PropTypes.func,
	onClose: PropTypes.func,
	onOpen: PropTypes.func,
};

export default LabelWithModal;
