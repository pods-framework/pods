import React, { useState } from 'react';
import * as PropTypes from 'prop-types';
import classNames from 'classnames';

import { __ } from '@wordpress/i18n';
import { Modal, Button } from '@wordpress/components';

import DynamicTabContent from './dynamic-tab-content';

const ENTER_KEY = 13;

const FieldGroupSettings = ( {
	title,
	editGroupPod: {
		groups: editGroupSections = [],
	} = {},
	hasSaveError,
	groupOptions,
	cancelEditing,
	save,
} ) => {
	const [ selectedTab, setSelectedTab ] = useState( editGroupSections[ 0 ].name );

	const [ changedOptions, setChangedOptions ] = useState( groupOptions );

	return (
		<Modal
			className="pods-field-group_settings pods-field-group_settings--visible"
			title={ title }
			isDismissible={ true }
			onRequestClose={ cancelEditing }
		>
			<Button isPrimary onClick={ () => save( changedOptions ) }>
				{ __( 'Save Group', 'pods' ) }
			</Button>

			<Button isSecondary onClick={ cancelEditing }>
				{ __( 'Cancel', 'pods' ) }
			</Button>

			{ hasSaveError && (
				<span className="pod-field-group_settings-error-message">
					{ __( 'There was an error saving the group, please try again.', 'pods' ) }
				</span>
			) }

			<div className="pods-field-group_settings-container">
				<div className="pods-field-group_settings-options">
					<div
						className="pods-field-group_settings-sidebar"
						role="tablist"
						aria-label={ __( 'Pods Field Group Settings', 'pods' ) }
					>
						{ editGroupSections.map( ( {
							name: sectionName,
							label: sectionLabel,
						} ) => {
							const isActive = selectedTab === sectionName;

							const classes = classNames(
								'pods-field-group_settings-sidebar-item',
								{
									'pods-field-group_settings-sidebar-item--active': isActive,
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
						className="pods-field-group_settings-main"
						role="tabpanel"
						aria-labelledby="main"
						id="main-tab"
					>
						{
							<DynamicTabContent
								tabOptions={ editGroupSections.find( ( section ) => section.name === selectedTab ).fields }
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
			</div>
		</Modal>
	);
};

FieldGroupSettings.propTypes = {
	editGroupPod: PropTypes.object.isRequired,
	groupOptions: PropTypes.object.isRequired,
	title: PropTypes.string.isRequired,
	hasSaveError: PropTypes.bool.isRequired,
	cancelEditing: PropTypes.func.isRequired,
	save: PropTypes.func.isRequired,
};

export default FieldGroupSettings;
