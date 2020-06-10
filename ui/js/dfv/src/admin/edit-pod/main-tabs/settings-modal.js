import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';
import classNames from 'classnames';

import { __ } from '@wordpress/i18n';
import { Modal, Button } from '@wordpress/components';

import DynamicTabContent from './dynamic-tab-content';

import './settings-modal.scss';

const ENTER_KEY = 13;

const SettingsModal = ( {
	title,
	optionsPod: {
		groups: optionsSections = [],
	} = {},
	hasSaveError,
	saveButtonText,
	errorMessage,
	selectedOptions,
	cancelEditing,
	save,
} ) => {
	const [ selectedTab, setSelectedTab ] = useState( optionsSections[ 0 ].name );

	const [ changedOptions, setChangedOptions ] = useState( selectedOptions );

	// When the modal first opens, set any options to their defaults, unless
	// they're already set.
	useEffect( () => {
		const defaultOptions = {
			...changedOptions,
		};

		optionsSections.forEach( ( optionsSection ) => {
			( optionsSection.fields || [] ).forEach( ( field ) => {
				// Only set the value if it wasn't previously supplied,
				// and only if a default is provided.
				if (
					'undefined' === typeof defaultOptions[ field.name ] &&
					'undefined' !== typeof field.default &&
					'' !== field.default
				) {
					defaultOptions[ field.name ] = field.default;
				}
			} );
		} );

		setChangedOptions( defaultOptions );
	}, [] );

	return (
		<Modal
			className="pods-settings-modal"
			title={ title }
			isDismissible={ true }
			onRequestClose={ cancelEditing }
		>
			{ hasSaveError && (
				<div className="pod-field-group_settings-error-message">
					{ errorMessage }
				</div>
			) }

			<div className="pods-settings-modal__container">
				<div
					className="pods-settings-modal__tabs"
					role="tablist"
					aria-label={ __( 'Pods Field Group Settings', 'pods' ) }
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
			</div>

			<div className="pods-setting-modal__button-group">
				<Button isSecondary onClick={ cancelEditing }>
					{ __( 'Cancel', 'pods' ) }
				</Button>

				<Button isPrimary onClick={ save( changedOptions ) }>
					{ saveButtonText }
				</Button>
			</div>
		</Modal>
	);
};

SettingsModal.propTypes = {
	optionsPod: PropTypes.object.isRequired,
	selectedOptions: PropTypes.object.isRequired,
	title: PropTypes.string.isRequired,
	hasSaveError: PropTypes.bool.isRequired,
	errorMessage: PropTypes.string.isRequired,
	saveButtonText: PropTypes.string.isRequired,
	cancelEditing: PropTypes.func.isRequired,
	save: PropTypes.func.isRequired,
};

export default SettingsModal;
