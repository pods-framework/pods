/**
 * External dependencies
 */
 import React, { useState, useEffect, forwardRef } from 'react';
 import classnames from 'classnames';
 import PropTypes from 'prop-types';

 /**
  * WordPress dependencies
  */
 import { Button, Dashicon } from '@wordpress/components';
 import { __ } from '@wordpress/i18n';
 import {
	 chevronUp,
	 chevronDown,
 } from '@wordpress/icons';

 /**
  * Other Pods dependencies
  */
 import IframeModal from 'dfv/src/components/iframe-modal';

const ListSelectItem = forwardRef( ( {
	fieldName,
	value,
	editLink,
	viewLink,
	editIframeTitle,
	icon,
	isDraggable,
	isRemovable,
	moveUp,
	moveDown,
	removeItem,
	setFieldItemData,
	isDragging = false,
	style = {},
	listeners = {},
	attributes = {},
}, draggableRef ) => {
	const isDashIcon = /^dashicons/.test( icon );
	const [ showEditModal, setShowEditModal ] = useState( false );

	useEffect( () => {
		const listenForIframeMessages = ( event ) => {
			if (
				event.origin !== window.location.origin ||
				'PODS_MESSAGE' !== event.data.type ||
				! event.data.data
			) {
				return;
			}

			setShowEditModal( false );

			const { data: newData = {} } = event.data;

			setFieldItemData( ( prevData ) => prevData.map( ( item ) => {
				return ( newData.id && Number( item?.id ) === Number( newData.id ) )
					? newData
					: item;
			} ) );
		};

		if ( showEditModal ) {
			window.addEventListener( 'message', listenForIframeMessages, false );
		} else {
			window.removeEventListener( 'message', listenForIframeMessages, false );
		}

		return () => {
			window.removeEventListener( 'message', listenForIframeMessages, false );
		};
	}, [ showEditModal ] );

	return (
		<li
			className="pods-list-select-item"
			ref={ draggableRef }
			style={ style }
		>
			<ul className="pods-dfv-list-meta relationship-item">
				{ isDraggable ? (
					<>
						<li
							className="pods-list-select-item__col pods-list-select-item__drag-handle"
							aria-label="drag"
							// eslint-disable-next-line react/jsx-props-no-spreading
							{ ...listeners }
							// eslint-disable-next-line react/jsx-props-no-spreading
							{ ...attributes }
							style={ {
								cursor: isDragging ? 'grabbing' : 'grab',
							} }
						>
							<Dashicon icon="menu" />
						</li>

						<li className="pods-list-select-item__col pods-list-select-item__move-buttons">
							<Button
								className={
									classnames(
										'pods-list-select-item__move-button',
										! moveUp && 'pods-list-select-item__move-button--disabled'
									)
								}
								showTooltip
								disabled={ ! moveUp }
								onClick={ moveUp }
								icon={ chevronUp }
								label={ __( 'Move up', 'pods' ) }
							/>

							<Button
								className={
									classnames(
										'pods-list-select-move-button',
										! moveDown && 'pods-list-select-move-button--disabled'
									)
								}
								showTooltip
								disabled={ ! moveDown }
								onClick={ moveDown }
								icon={ chevronDown }
								label={ __( 'Move down', 'pods' ) }
							/>
						</li>
					</>
				) : null }

				{ icon ? (
					<li className="pods-list-select-item__col pods-list-select-item__icon">
						{ isDashIcon ? (
							<span
								className={ `pinkynail dashicons ${ icon }` }
							/>
						) : (
							<img
								className="pinkynail"
								src={ icon }
								alt="Icon"
							/>
						) }
					</li>
				) : null }

				<li className="pods-list-select-item__col pods-list-select-item__name">
					{ value.label }
				</li>

				{ isRemovable ? (
					<li className="pods-list-select-item__col pods-list-select-item__remove">
						<a
							href="#remove"
							title={ __( 'Deselect', 'pods' ) }
							onClick={ removeItem }
							className="pods-list-select-item__link"
						>
							{ __( 'Deselect', 'pods' ) }
						</a>
					</li>
				) : null }

				{ viewLink ? (
					<li className="pods-list-select-item__col pods-list-select-item__view">
						<a
							href={ viewLink }
							title={ __( 'View', 'pods' ) }
							target="_blank"
							rel="noreferrer"
							className="pods-list-select-item__link"
						>
							{ __( 'View', 'pods' ) }
						</a>
					</li>
				) : null }

				{ editLink ? (
					<li className="pods-list-select-item__col pods-list-select-item__edit">
						<a
							href={ editLink }
							title={ __( 'Edit', 'pods' ) }
							target="_blank"
							rel="noreferrer"
							onClick={ ( event ) => {
								event.preventDefault();
								setShowEditModal( true );
							} }
							className="pods-list-select-item__link"
						>
							{ __( 'Edit', 'pods' ) }
						</a>
					</li>
				) : null }
			</ul>

			{ showEditModal ? (
				<IframeModal
					title={ editIframeTitle || `${ fieldName }: Edit` }
					iframeSrc={ editLink }
					onClose={ () => setShowEditModal( false ) }
				/>
			) : null }
		</li>
	);
} );

ListSelectItem.propTypes = {
	fieldName: PropTypes.string.isRequired,
	value: PropTypes.shape( {
		label: PropTypes.string.isRequired,
		value: PropTypes.string.isRequired,
	} ),
	editLink: PropTypes.string,
	editIframeTitle: PropTypes.string,
	viewLink: PropTypes.string,
	icon: PropTypes.string,
	isDraggable: PropTypes.bool.isRequired,
	isRemovable: PropTypes.bool.isRequired,
	moveUp: PropTypes.func,
	moveDown: PropTypes.func,
	removeItem: PropTypes.func.isRequired,
	setFieldItemData: PropTypes.func.isRequired,

	// From useSortable:
	isDragging: PropTypes.bool,
	style: PropTypes.object,
	attributes: PropTypes.object,
	listeners: PropTypes.object,
};

export default ListSelectItem;
