import React from 'react';
import * as PropTypes from 'prop-types';

const { Modal } = wp.components;
const { __ } = wp.i18n;

export const FieldGroupSettings = ( { groupName, show } ) => {
	const closeModal = ( e ) => {
		e.stopPropagation();
		show( false );
	};

	return (
		<Modal
			className="pods-field-group_settings pods-field-group_settings--visible"
			title={`${groupName} ` + __( 'Settings', 'pods' )}
			onRequestClose={( e ) => closeModal( e )}>
			<div className="pods-field-group_settings-container">
				<div className="pods-field-group_settings-options">
					<div className="pods-field-group_settings-sidebar">
						<div className="pods-field-group_settings-sidebar-item pods-field-group_settings-sidebar-item--active">{__( 'General', 'pods' )}</div>
						<div className="pods-field-group_settings-sidebar-item">{__( 'Advanced', 'pods' )}</div>
						<div className="pods-field-group_settings-sidebar-item">{__( 'Other Group Settings Tab', 'pods' )}</div>
					</div>
					<div className="pods-field-group_settings-main">
						<p>$id</p>
						<p>$title</p>
						<p>$callback</p>
						<p>$screen</p>
						<p>$context</p>
						<p>$priority</p>
						<p>$callback_args</p>
					</div>
				</div>
			</div>
		</Modal>
	);
};

FieldGroupSettings.propTypes = {
	groupName: PropTypes.string.isRequired,
	show: PropTypes.func.isRequired,
};
