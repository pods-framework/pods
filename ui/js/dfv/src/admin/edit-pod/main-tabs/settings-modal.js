import React, { useState, useEffect } from 'react';
import * as PropTypes from 'prop-types';
import classNames from 'classnames';

import { __ } from '@wordpress/i18n';
import { Modal, Button } from '@wordpress/components';

import DynamicTabContent from './dynamic-tab-content';
import sanitizeSlug from 'dfv/src/helpers/sanitizeSlug';

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

	const [ isValid, setIsValid ] = useState( false );

	// Wrapper around setChangedOptions(), which also sets the name/slug
	// based on the Label, if the slug hasn't previously been set.
	const setOptionValue = ( optionName, value ) => {
		const newOptions = {
			[ optionName ]: value,
		};

		// Generate a slug if needed.
		if ( 'label' === optionName && 'undefined' === typeof selectedOptions.name ) {
			newOptions.name = sanitizeSlug( value );
		}

		setChangedOptions( {
			...changedOptions,
			...newOptions,
		} );
	};

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

	// Check validity if any of the options have changed.
	useEffect( () => {
		// Go through each section, check that each one has all valid fields.
		const validity = optionsSections.every(
			( section ) => {
				return section.fields.every(
					( field ) => {
						// Fields that aren't required are automatically valid.
						if ( undefined === typeof field.required || ! field.required ) {
							return true;
						}

						// Skip fields won't be shown because their dependency isn't met.
						if ( field[ 'depends-on' ] ) {
							const dependsOnKeys = Object.keys( field[ 'depends-on' ] );

							const meetsDeps = dependsOnKeys.some(
								( key ) => changedOptions[ key ] === field[ 'depends-on' ][ key ]
							);

							if ( ! meetsDeps ) {
								return true;
							}
						}

						// Boolean values could be falsey and still valid.
						if ( 'boolean' === field.type ) {
							return 'undefined' !== typeof changedOptions[ field.name ];
						}

						return 'undefined' !== typeof changedOptions[ field.name ] &&
							'' !== changedOptions[ field.name ].toString();
					}
				);
			}
		);

		setIsValid( validity );
	}, [ changedOptions ] );

	return (
		<Modal
			className="pods-settings-modal"
			title={ title }
			isDismissible={ true }
			onRequestClose={ cancelEditing }
			focusOnMount={ true }
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
							setOptionValue={ setOptionValue }
						/>
					}
				</div>
			</div>

			<div className="pods-setting-modal__button-group">
				<Button
					isSecondary
					onClick={ cancelEditing }
				>
					{ __( 'Cancel', 'pods' ) }
				</Button>

				<Button
					isPrimary
					onClick={ save( changedOptions ) }
					disabled={ ! isValid }
				>
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
