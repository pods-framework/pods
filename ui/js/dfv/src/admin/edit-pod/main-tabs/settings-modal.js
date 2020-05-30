import React, { useState } from 'react';
import * as PropTypes from 'prop-types';
import classNames from 'classnames';

import { __ } from '@wordpress/i18n';
import { Modal, Button } from '@wordpress/components';

import DynamicTabContent from './dynamic-tab-content';

const ENTER_KEY = 13;

const SettingsModal = ( {
	title,
	optionsPod: {
		groups: optionsSections = [],
	} = {},
	hasSaveError,
	errorMessage,
	selectedOptions,
	cancelEditing,
	save,
} ) => {
	const [ selectedTab, setSelectedTab ] = useState( optionsSections[ 0 ].name );

	const [ changedOptions, setChangedOptions ] = useState( selectedOptions );

	return (
		<Modal
			className="pods-settings-modal"
			title={ title }
			isDismissible={ true }
			onRequestClose={ cancelEditing }
		>
			{ hasSaveError && (
				<span className="pod-field-group_settings-error-message">
					{ errorMessage }
				</span>
			) }

			<div
				className="pods-settings-modal__tabs"
				role="tablist"
				aria-label={ __( 'Settings', 'pods' ) }
			>
				{ optionsSections.map( ( {
					name: sectionName,
					label: sectionLabel,
				} ) => {
					const isActive = selectedTab === sectionName;

					const classes = classNames(
						'pods-settings-modal__tab-item',
						{
							'pods-settings-modal__tab-item--active': isActive,
						}
					);

					return (
						<div
							className={ classes }
							aria-controls={ `${ sectionName }-tab` }
							role="button"
							tabIndex={ 0 }
							key={ sectionName }
							onClick={ () => setSelectedTab( sectionName ) }
							onKeyPress={ ( event ) => event.keyCode === ENTER_KEY && setSelectedTab( sectionName ) }
						>
							{ sectionLabel }
						</div>
					);
				} ) }
			</div>

			<div
				className="pods-settings-modal__panel"
				role="tabpanel"
				aria-labelledby="main"
				id="main-tab"
			>
				{
					<DynamicTabContent
						tabOptions={ optionsSections.find( ( section ) => section.name === selectedTab ).fields }
						optionValues={ changedOptions }
						setOptionValue={ ( optionName, value ) => {
							setChangedOptions( {
								...changedOptions,
								[ optionName ]: value,
							} );
						} }
					/>
				}
			</div>

			<Button isSecondary onClick={ cancelEditing }>
				{ __( 'Cancel', 'pods' ) }
			</Button>

			<Button isPrimary onClick={ () => save( changedOptions ) }>
				{ __( 'Save', 'pods' ) }
			</Button>
		</Modal>
	);
};

SettingsModal.propTypes = {
	optionsPod: PropTypes.object.isRequired,
	selectedOptions: PropTypes.object.isRequired,
	title: PropTypes.string.isRequired,
	hasSaveError: PropTypes.bool.isRequired,
	errorMessage: PropTypes.string.isRequired,
	cancelEditing: PropTypes.func.isRequired,
	save: PropTypes.func.isRequired,
};

export default SettingsModal;
