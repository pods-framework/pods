import React from 'react';
import * as PropTypes from 'prop-types';

import { __ } from '@wordpress/i18n';
import { Modal } from '@wordpress/components';

const FieldGroupSettings = ( { groupName, show } ) => {
	const closeModal = ( e ) => {
		e.stopPropagation();
		show( false );
	};

	return (
		<Modal
			className="pods-field-group_settings pods-field-group_settings--visible"
			title={ `${ groupName } ` + __( 'Settings', 'pods' ) }
			onRequestClose={ ( e ) => closeModal( e ) }
		>
			<div className="pods-field-group_settings-container">
				<div className="pods-field-group_settings-options">
					<div className="pods-field-group_settings-sidebar" role="tablist" aria-label="Pods Field Group Settings">
						<div className="pods-field-group_settings-sidebar-item pods-field-group_settings-sidebar-item--active" aria-selected="true" id="main" aria-controls="main-tab">
							{ __( 'General', 'pods' ) }
						</div>
						<div className="pods-field-group_settings-sidebar-item" aria-selected="false" id="advanced" aria-controls="advanced-tab">
							{ __( 'Advanced', 'pods' ) }
						</div>
						<div className="pods-field-group_settings-sidebar-item" aria-selected="false" id="other" aria-controls="other-tab">
							{ __( 'Other Group Settings Tab', 'pods' ) }
						</div>
					</div>

					<div className="pods-field-group_settings-main" role="tabpanel" aria-labelledby="main" id="main-tab">
						<label className="pods-input-container">
							<span className="pods-label_text">$id</span>
							<input className="pods-input" type="text"></input>
						</label>
						<label className="pods-input-container">
							<span className="pods-label_text">$title</span>
							<input className="pods-input" type="text"></input>
						</label>
						<label className="pods-input-container">
							<span className="pods-label_text">$callback</span>
							<input className="pods-input" type="text"></input>
						</label>
						<label className="pods-input-container">
							<span className="pods-label_text">$screen</span>
							<input className="pods-input" type="text"></input>
						</label>
						<label className="pods-input-container">
							<span className="pods-label_text">$context</span>
							<input className="pods-input" type="text"></input>
						</label>
						<label className="pods-input-container">
							<span className="pods-label_text">$priority</span>
							<input className="pods-input" type="text"></input>
						</label>
						<label className="pods-input-container">
							<span className="pods-label_text">$callback_args</span>
							<input className="pods-input" type="text"></input>
						</label>
					</div>

					<div className="pods-field-group_settings-advanced" role="tabpanel" aria-labelledby="advanced" id="advanced-tab" hidden="hidden">
						<p>Advanced tabpanel</p>
					</div>

					<div className="pods-field-group_settings-other" role="tabpanel" aria-labelledby="other" id="other-tab" hidden="hidden">
						<p>Other tabpanel</p>
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

export default FieldGroupSettings;
