/* eslint-disable react/prop-types */
import React from 'react';
const useState = React.useState;
import { PodsDFVEditPodStatusMessage } from 'pods-dfv/src/admin/edit-pod/manage-fields/status-message';
import { PodsDFVSluggable } from 'pods-dfv/src/admin/edit-pod/sluggable';
import { PodsDFVEditPodTabs } from 'pods-dfv/src/admin/edit-pod/edit-pod-tabs';
import { PodsDFVManageFields } from 'pods-dfv/src/admin/edit-pod/manage-fields/manage-fields';
import { PodsDFVPostboxContainer } from 'pods-dfv/src/admin/edit-pod/postbox-container';
import { PodsDFVFieldBasicOptions } from 'pods-dfv/src/admin/edit-pod/manage-fields/basic-options';

const { __ } = wp.i18n;
const { Modal } = wp.components;

const ACTION = 'pods_admin_proto';

export const PodsDFVEditPod = ( props ) => {
	const oldName = props.podInfo.name;
	const [ podName, setPodName ] = useState( props.podInfo.name );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ saved, setSaved ] = useState( false );
	const [ showModal, setShowModal ] = useState( false );

	const handleSubmit = ( e ) => {
		const requestData = {
			'id': props.podInfo.id,
			'name': podName,
			'old_name': oldName,
			'_wpnonce': props.fieldConfig.nonce,
			'fields': props.podInfo.fields
		};
		e.preventDefault();

		setSaved( false );
		setIsSaving( true );
		fetch( `${ajaxurl}?pods_ajax=1&action=${ACTION}`, {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			body: JSON.stringify( requestData )
		} )
		.then(
			( result ) => {
				console.log( result );
				setSaved( true );
			},
			( error ) => {
				console.log( error );
			}
		).finally( () => {
			setIsSaving( false );
		} );
	};

	const onFieldEditClick = ( e, id ) => {
		e.preventDefault();
		setShowModal( true );
	};

	return (
		<form className='pods-submittable pods-nav-tabbed' onSubmit={handleSubmit}>
			{ showModal && (
				<Modal
					title='Modal Field Edit Proto'
					onRequestClose={() => setShowModal( false )}>

					<PodsDFVFieldBasicOptions />
				</Modal>
			) }

			<div className='pods-submittable-fields'>
				<h2>
					{'Edit Pod: '}
					<PodsDFVSluggable
						value={podName}
						onChange={ ( e ) => setPodName( e.target.value ) }
					/>
				</h2>
				<PodsDFVEditPodStatusMessage
					isSaving={isSaving}
					saved={saved}
				/>
				<PodsDFVEditPodTabs />
			</div>
			<div id='poststuff'>
				<div id='post-body' className='meta-box-holder columns-2'>
					<div id='post-body-content' className='pods-nav-tab-group'>
						<PodsDFVManageFields
							fields={props.podInfo.fields}
							onFieldEditClick={onFieldEditClick}
						/>
					</div>
					<PodsDFVPostboxContainer isSaving={isSaving} />
				</div>
			</div>
		</form>
	);
};
