import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import { omit } from 'lodash';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import classnames from 'classnames';

import { Dashicon } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';

import SettingsModal from './settings-modal';
import FieldList from 'dfv/src/admin/edit-pod/main-tabs/field-list';
import { GROUP_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import { SAVE_STATUSES } from 'dfv/src/store/constants';

import './field-group.scss';

const ENTER_KEY = 13;

const FieldGroup = ( props ) => {
	const {
		podType,
		podName,
		podID,
		podLabel,
		group,
		isExpanded,
		hasMoved,
		saveStatus,
		saveMessage,
		resetGroupSaveStatus,
		deleteGroup,
		saveGroup,
		toggleExpanded,
		editGroupPod,
		storeKey,
	} = props;

	const {
		name: groupName,
		label: groupLabel,
		id: groupID,
		fields,
	} = group;

	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
		isDragging,
	} = useSortable( { id: groupName } );

	const style = {
		transform: CSS.Translate.toString( transform ),
		transition,
	};

	const [ showSettings, setShowSettings ] = useState( false );

	useEffect( () => {
		// Close the Group Settings modal if we finished saving.
		if ( SAVE_STATUSES.SAVE_SUCCESS === saveStatus ) {
			setShowSettings( false );
		}
	}, [ saveStatus ] );

	const handleKeyPress = ( event ) => {
		if ( showSettings ) {
			return;
		}

		if ( event.charCode === ENTER_KEY ) {
			toggleExpanded();
		}
	};

	const handleTitleClick = ( event ) => {
		event.stopPropagation();

		if ( showSettings ) {
			return;
		}

		toggleExpanded();
	};

	const onEditGroupClick = ( event ) => {
		event.stopPropagation();
		setShowSettings( true );
	};

	const onEditGroupCancel = ( event ) => {
		event.stopPropagation();
		setShowSettings( false );
		resetGroupSaveStatus( groupName );
	};

	const onEditGroupSave = ( updatedOptions = {} ) => ( event ) => {
		event.stopPropagation();
		saveGroup(
			podID,
			groupName,
			updatedOptions.name || groupName,
			updatedOptions.label || groupLabel || groupName,
			omit( updatedOptions, [ 'name', 'label', 'id' ] ),
			groupID,
		);
	};

	const onDeleteGroupClick = ( event ) => {
		event.stopPropagation();

		// eslint-disable-next-line no-alert
		const confirmation = confirm(
			// eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace
			__( 'You are about to permanently delete this Field Group and all of the Fields within it. Make sure you have recent backups just in case. Are you sure you would like to delete this Group?\n\nClick ‘OK’ to continue, or ‘Cancel’ to make no changes.', 'pods' )
		);

		if ( confirmation ) {
			deleteGroup( groupID );
		}
	};

	const classes = classnames(
		'pods-field-group-wrapper',
		hasMoved && 'pods-field-group-wrapper--unsaved',
	);

	return (
		<div
			ref={ setNodeRef }
			className={ classes }
			style={ style }
		>
			<div
				tabIndex={ 0 }
				role="button"
				className="pods-field-group_title"
				onClick={ handleTitleClick }
				onKeyPress={ handleKeyPress }
			>
				<div className="pods-field-group_name">
					{ /* eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions */ }
					<div
						className="pods-field-group_handle"
						aria-label="drag"
						// eslint-disable-next-line react/jsx-props-no-spreading
						{ ...listeners }
						// eslint-disable-next-line react/jsx-props-no-spreading
						{ ...attributes }
						onClick={ ( event ) => event.stopPropagation() }
					>
						<Dashicon icon="menu" />
					</div>

					{ groupLabel }

					{ !! groupID && (
						<span className="pods-field-group_name__id">
							{ `\u00A0 [id = ${ groupID }]` }
						</span>
					) }
				</div>

				<div className="pods-field-group_buttons">
					{ ! isExpanded && (
						<>
							<button
								className="pods-field-group_button pods-field-group_manage_link"
								onClick={ toggleExpanded }
							>
								{ __( 'Manage Fields', 'pods' ) }
							</button>
							|
						</>
					) }

					<button
						className="pods-field-group_button pods-field-group_edit"
						onClick={ ( event ) => onEditGroupClick( event ) }
					>
						{ __( 'Edit', 'pods' ) }
					</button>
					|
					<button
						className="pods-field-group_button pods-field-group_delete"
						onClick={ onDeleteGroupClick }
					>
						{ __( 'Delete', 'pods' ) }
					</button>
				</div>

				<button
					className="pods-field-group_button pods-field-group_manage"
				>
					<Dashicon icon={ isExpanded ? 'arrow-up' : 'arrow-down' } />
				</button>

				{ showSettings && (
					<SettingsModal
						podType={ podType }
						podName={ podName }
						optionsPod={ editGroupPod }
						selectedOptions={ omit( group, [ 'fields' ] ) }
						title={ sprintf(
							// @todo Zack: Make these into elements we can style the parent pod differently.
							/* translators: %1$s: Pod Label, %2$s Group Label */
							__( '%1$s > %2$s > Edit Group', 'pods' ),
							podLabel,
							groupLabel
						) }
						hasSaveError={ saveStatus === SAVE_STATUSES.SAVE_ERROR }
						errorMessage={
							saveMessage ||
							__( 'There was an error saving the group, please try again.', 'pods' )
						}
						saveButtonText={ __( 'Save Group', 'pods' ) }
						cancelEditing={ onEditGroupCancel }
						save={ onEditGroupSave }
					/>
				) }
			</div>

			{ isExpanded && ! isDragging && (
				<FieldList
					podType={ podType }
					podName={ podName }
					fields={ fields || [] }
					podID={ podID }
					podLabel={ podLabel }
					groupName={ groupName }
					groupID={ groupID }
					groupLabel={ groupLabel }
					storeKey={ storeKey }
				/>
			) }
		</div>
	);
};

FieldGroup.propTypes = {
	storeKey: PropTypes.string.isRequired,
	podType: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
	podID: PropTypes.number.isRequired,
	podLabel: PropTypes.string.isRequired,
	group: GROUP_PROP_TYPE_SHAPE,

	index: PropTypes.number.isRequired,
	isExpanded: PropTypes.bool.isRequired,
	editGroupPod: PropTypes.object.isRequired,
	hasMoved: PropTypes.bool.isRequired,
	saveStatus: PropTypes.string,
	saveMessage: PropTypes.string,

	toggleExpanded: PropTypes.func.isRequired,
	deleteGroup: PropTypes.func.isRequired,
	saveGroup: PropTypes.func.isRequired,
	resetGroupSaveStatus: PropTypes.func.isRequired,
};

export default FieldGroup;
