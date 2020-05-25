import React, { useState } from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';
import GroupDragLayer from './group-drag-layer';
import FieldGroup from './field-group';
import { GROUP_PROP_TYPE_SHAPE } from 'pods-dfv/src/prop-types';

import './field-groups.scss';

const FieldGroups = ( {
	podName,
	groups,
	addGroup,
	deleteGroup,
	moveGroup,
	groupFieldList,
	setGroupFields,
	addGroupField,
	setFields,
	editGroupPod,
} ) => {
	// If there's only one group, expand that group initially.
	const [ expandedGroups, setExpandedGroups ] = useState(
		1 === groups.length ? { [ groups[ 0 ].name ]: true } : {}
	);

	const handleAddGroup = ( e ) => {
		e.preventDefault();

		const str = randomString( 6 );
		const name = 'Group ' + str;
		addGroup( name );
	};

	const createToggleExpandGroup = ( groupName ) => () => {
		setExpandedGroups( {
			...expandedGroups,
			[ groupName ]: expandedGroups[ groupName ] ? false : true,
		} );
	};

	const randomString = ( length ) => {
		let result = '';
		const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		const charactersLength = characters.length;
		for ( let i = 0; i < length; i++ ) {
			result += characters.charAt( Math.floor( Math.random() * charactersLength ) );
		}
		return result;
	};

	return (
		<div className="field-groups">
			<div className="pods-button-group_container">
				<button
					className="pods-button-group_add-new"
					onClick={ ( e ) => handleAddGroup( e ) }
				>
					{ __( '+ Add New Group', 'pods' ) }
				</button>
			</div>

			{ groups.map( ( group, index ) => {
				return (
					<FieldGroup
						key={ group.name }
						podName={ podName }
						group={ group }
						index={ index }
						editGroupPod={ editGroupPod }
						deleteGroup={ deleteGroup }
						moveGroup={ moveGroup }
						groupFieldList={ groupFieldList }
						setGroupFields={ setGroupFields }
						addGroupField={ addGroupField }
						setFields={ setFields }
						randomString={ randomString }
						isExpanded={ expandedGroups[ group.name ] || false }
						toggleExpanded={ createToggleExpandGroup( group.name ) }
					/>
				);
			} ) }

			<GroupDragLayer />

			<div className="pods-button-group_container">
				<button
					className="pods-button-group_add-new"
					onClick={ ( e ) => handleAddGroup( e ) }
				>
					{ __( '+ Add New Group', 'pods' ) }
				</button>
			</div>
		</div>
	);
};

FieldGroups.propTypes = {
	podName: PropTypes.string.isRequired,
	groups: PropTypes.arrayOf( GROUP_PROP_TYPE_SHAPE ).isRequired,
	addGroup: PropTypes.func.isRequired,
	deleteGroup: PropTypes.func.isRequired,
	moveGroup: PropTypes.func.isRequired,
	editGroupPod: PropTypes.object.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		const storeSelect = select( STORE_KEY_EDIT_POD );

		return {
			podName: storeSelect.getPodName(),
			groups: storeSelect.getGroups(),
			groupFieldList: storeSelect.groupFieldList(),
			editGroupPod: storeSelect.getGlobalGroupOptions(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const storeDispatch = dispatch( STORE_KEY_EDIT_POD );

		return {
			addGroup: storeDispatch.addGroup,
			deleteGroup: storeDispatch.deleteGroup,
			setGroupFields: storeDispatch.setGroupFields,
			addGroupField: storeDispatch.addGroupField,
			setFields: storeDispatch.setFields,
			moveGroup: storeDispatch.moveGroup,
		};
	} ),
] )( FieldGroups );
