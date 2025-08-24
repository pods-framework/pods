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

import {
	SAVE_STATUSES,
	DUPLICATE_STATUSES,
	DELETE_STATUSES,
} from 'dfv/src/store/constants';

import './field-group.scss';

const ENTER_KEY = 13;

const FieldGroup = ( props ) => {
	const {
		podType,
		podName,
		podID,
		podLabel,
		group,
		fieldsMovedSinceLastSave,
		isExpanded,
		hasMoved,
		hasMovedFields,
		saveStatus,
		saveMessage,
		duplicateStatus,
		deleteStatus,
		resetGroupSaveStatus,
		duplicateGroup,
		deleteGroup,
		removeGroupFromPod,
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

	const isDuplicating = DUPLICATE_STATUSES.DUPLICATING === duplicateStatus;
	const hasDuplicateFailed = DUPLICATE_STATUSES.DUPLICATE_ERROR === duplicateStatus;
	const isDeleting = DELETE_STATUSES.DELETING === deleteStatus;
	const hasDeleteFailed = DELETE_STATUSES.DELETE_ERROR === deleteStatus;

	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
		isDragging,
	} = useSortable( {
		id: groupName,
		data: {
			type: 'group',
		},
	} );

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

	useEffect( () => {
		// After the group deletion is finished, remove the group from the pod.
		if ( DELETE_STATUSES.DELETE_SUCCESS === deleteStatus ) {
			removeGroupFromPod();
		}
	}, [ deleteStatus ] );

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

	const onDuplicateGroupClick = ( event ) => {
		event.stopPropagation();

		if ( hasMovedFields ) {
			// eslint-disable-next-line no-alert
			alert(
				__( 'You moved fields outside of this group but did not save your changes to the Pod yet. To duplicate this Group, save changes for your Pod first.', 'pods' ),
			);

			return;
		}

		duplicateGroup( groupID, groupName );
	};

	const onDeleteGroupClick = ( event ) => {
		event.stopPropagation();

		if ( hasMovedFields ) {
			// eslint-disable-next-line no-alert
			alert(
				__( 'You moved fields outside of this group but did not save your changes to the Pod yet. To delete this Group, save changes for your Pod first.', 'pods' ),
			);

			return;
		}

		// eslint-disable-next-line no-alert
		const confirmation = confirm(
			// eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace
			__( 'You are about to permanently delete this Field Group and all of the Fields within it. Make sure you have recent backups just in case. Are you sure you would like to delete this Group?\n\nClick ‘OK’ to continue, or ‘Cancel’ to make no changes.', 'pods' )
		);

		if ( confirmation ) {
			deleteGroup( groupID, groupName );
		}
	};

	return (
		<div
			ref={ setNodeRef }
			className={
				classnames(
					'pods-field-group-wrapper',
					hasMoved && 'pods-field-group-wrapper--unsaved',
					isDuplicating && 'pods-field-group-wrapper--duplicating',
					hasDuplicateFailed && 'pods-field-group-wrapper--errored',
					isDeleting && 'pods-field-group-wrapper--deleting',
					hasDeleteFailed && 'pods-field-group-wrapper--errored',
				)
			}
			style={ style }
		>
			<div
				tabIndex={ 0 }
				role="button"
				className="pods-field-group_title"
				onClick={ handleTitleClick }
				onKeyPress={ handleKeyPress }
				aria-label={ __( 'Press and hold to drag this item to a new position in the list', 'pods' ) }
			>
				<div className="pods-field-group_name">
					{ /* eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions */ }
					<div
						className="pods-field-group_handle"
						// eslint-disable-next-line react/jsx-props-no-spreading
						{ ...listeners }
						// eslint-disable-next-line react/jsx-props-no-spreading
						{ ...attributes }
						onClick={ ( event ) => event.stopPropagation() }
					>
						<Dashicon icon="menu" />
					</div>

					{ groupLabel }

					{ hasDuplicateFailed ? (
						<div className="pods-field-group_name__error">
							{ __( 'Duplication failed. Try again?', 'pods' ) }
						</div>
					) : null }

					{ hasDeleteFailed ? (
						<div className="pods-field-group_name__error">
							{ __( 'Delete failed. Try again?', 'pods' ) }
						</div>
					) : null }

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
								aria-label={ __( 'Manage the fields for this field group', 'pods' ) }
							>
								{ __( 'Manage Fields', 'pods' ) }
							</button>
							|
						</>
					) }

					<button
						className="pods-field-group_button pods-field-group_edit"
						onClick={ ( event ) => onEditGroupClick( event ) }
						aria-label={ __( 'Edit this field group for the Pod', 'pods' ) }
					>
						{ __( 'Edit', 'pods' ) }
					</button>
					|
					<button
						className="pods-field-group_button pods-field-group_duplicate"
						onClick={ ( event ) => onDuplicateGroupClick( event ) }
						aria-label={ __( 'Duplicate this field group for the Pod', 'pods' ) }
					>
						{ __( 'Duplicate', 'pods' ) }
					</button>
					|
					<button
						className="pods-field-group_button pods-field-group_delete"
						onClick={ onDeleteGroupClick }
						aria-label={ __( 'Delete this field group from the Pod', 'pods' ) }
					>
						{ __( 'Delete', 'pods' ) }
					</button>
				</div>

				<button
					className="pods-field-group_button pods-field-group_manage"
					aria-pressed={ isExpanded }
					aria-label={
						isExpanded
							? __( 'Collapse this field group to hide the fields associated', 'pods' )
							: __( 'Expand this field group to see the fields associated', 'pods' )
					}
				>
					<Dashicon icon={ isExpanded ? 'arrow-up' : 'arrow-down' } />
				</button>

				{ showSettings && (
					<SettingsModal
						storeKey={ storeKey }
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
						isSaving={ saveStatus === SAVE_STATUSES.SAVING }
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
					storeKey={ storeKey }
					podType={ podType }
					podName={ podName }
					fields={ fields || [] }
					podID={ podID }
					podLabel={ podLabel }
					groupName={ groupName }
					groupID={ groupID }
					groupLabel={ groupLabel }
					fieldsMovedSinceLastSave={ fieldsMovedSinceLastSave }
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
	fieldsMovedSinceLastSave: PropTypes.array.isRequired,

	index: PropTypes.number.isRequired,
	isExpanded: PropTypes.bool.isRequired,
	editGroupPod: PropTypes.object.isRequired,
	hasMoved: PropTypes.bool.isRequired,
	hasMovedFields: PropTypes.bool.isRequired,
	saveStatus: PropTypes.string,
	saveMessage: PropTypes.string,
	duplicateStatus: PropTypes.string,
	deleteStatus: PropTypes.string,

	toggleExpanded: PropTypes.func.isRequired,
	duplicateGroup: PropTypes.func.isRequired,
	deleteGroup: PropTypes.func.isRequired,
	removeGroupFromPod: PropTypes.func.isRequired,
	saveGroup: PropTypes.func.isRequired,
	resetGroupSaveStatus: PropTypes.func.isRequired,
};

export default FieldGroup;
