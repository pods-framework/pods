/**
 * External dependencies
 */
import classnames from 'classnames';
import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Dashicon,
	Toolbar,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import {
	dragHandle,
	chevronUp,
	chevronDown,
	close,
	download,
	external,
	edit,
} from '@wordpress/icons';

/**
 * Other Pods dependencies
 */
import IframeModal from 'dfv/src/components/iframe-modal';
import Text from '../text';

/**
 * Wrapper component that adapts ListItem to work with RepeatableFieldList pattern.
 * This provides drag-and-drop functionality and move/delete controls.
 */
const ListItem = ( {
	fieldName,
	value,
	index,
	isDraggable,
	isRemovable,
	moveUp,
	moveDown,
	removeItem,
	fieldItemData,
	setFieldItemData,
	defaultIcon,
	showIcon = false,
	showDownloadLink = false,
	showViewLink = false,
	showEditLink = false,
	showEditTitle = false,
	editIframeTitle,
	onTitleChange,
	htmlAttrs = {},
} ) => {
	// Set up useSortable hook
	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
	} = useSortable( {
		id: index.toString(),
		disabled: ! isDraggable,
		data: {
			value: value?.value.toString(),
			label: value?.label.toString(),
		},
	} );

	const style = {
		transform: CSS.Translate.toString( transform ),
		transition,
	};

	// Find additional data for this value item
	const moreData = fieldItemData.find(
		( item ) => item?.id.toString() === value.value.toString()
	);

	const icon = showIcon ? ( moreData?.icon || defaultIcon ) : undefined;
	const isDashIcon = /^dashicons/.test( icon );
	const dashIconName = isDashIcon ? icon.replace( /^dashicons-/, '' ) : null;
	const id = value.value.toString();

	const downloadLink = showDownloadLink ? moreData?.download : undefined;
	const editLink = showEditLink ? moreData?.edit_link : undefined;
	const viewLink = showViewLink ? moreData?.link : undefined;
	const [ showEditModal, setShowEditModal ] = useState( false );

	const htmlFieldName = htmlAttrs?.name || fieldName;

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
				return ( newData.id && item?.id.toString() === newData.id.toString() )
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
		<div
			className="pods-field-wrapper__item pods-field-wrapper__repeatable"
			ref={ setNodeRef }
			style={ style }
		>
			{ isDraggable ? (
				<div className="pods-field-wrapper__controls pods-field-wrapper__controls--start">
					<Toolbar label="List select item">
						<ToolbarButton
							icon={ dragHandle }
							label={ __( 'Drag to reorder', 'pods' ) }
							showTooltip
							tabIndex="-1"
							className="pods-field-wrapper__drag-handle"
							{ ...listeners }
							{ ...attributes }
						/>

						<ToolbarGroup className="pods-field-wrapper__movers">
							<ToolbarButton
								disabled={ ! moveUp }
								onClick={ moveUp }
								icon={ chevronUp }
								label={ __( 'Move up', 'pods' ) }
								showTooltip
								className="pods-field-wrapper__mover"
							/>

							<ToolbarButton
								disabled={ ! moveDown }
								onClick={ moveDown }
								icon={ chevronDown }
								label={ __( 'Move down', 'pods' ) }
								showTooltip
								className="pods-field-wrapper__mover"
							/>
						</ToolbarGroup>
					</Toolbar>
				</div>
			) : null }

			<div
				className={ classnames(
					'pods-field-wrapper__field',
					'pods-list-select-item',
					isDraggable && 'pods-list-select-item--draggable',
					! isDraggable && 'pods-list-select-item--not-draggable',
				) }
			>
				<div className="pods-list-select-item__inner">
					{ icon ? (
						<div className="pods-list-select-item__col pods-list-select-item__icon">
							{ isDashIcon ? (
								<Dashicon
									className="pinkynail"
									icon={ dashIconName }
									size={ 32 }
									aria-label={ __( 'Icon', 'pods' ) }
								/>
							) : (
								<img
									className="pinkynail"
									width={ 32 }
									height={ 32 }
									src={ icon }
									alt={ __( 'Icon', 'pods' ) }
								/>
							) }
						</div>
					) : null }

					<div
						className={
							classnames(
								'pods-list-select-item__col',
								'pods-list-select-item__name',
								showEditTitle && 'pods-list-select-item__name-editable'
							)
						}
					>
						{ showEditTitle ? (
							<Text
								fieldConfig={ {
									name: `${ htmlFieldName }-${ id }`,
									htmlAttr: {
										name: `${ htmlFieldName }[${ id }][title]`,
									},
								} }
								value={ value.label }
								setValue={ ( newTitle ) => {
									if ( onTitleChange ) {
										onTitleChange( id, newTitle );
									}
								} }
								setHasBlurred={ () => {} }
							/>
						) : value.label }
					</div>
				</div>
			</div>

			{ editLink || downloadLink || viewLink || showEditModal || isRemovable ? (
				<div className="pods-field-wrapper__controls pods-field-wrapper__controls--end">
					{ editLink ? (
						<Toolbar label={ __( 'Edit item', 'pods' ) }>
							<ToolbarButton
								onClick={ ( event ) => {
									event.preventDefault();
									event.stopPropagation();
									setShowEditModal( true );
								} }
								icon={ edit }
								label={ __( 'Edit', 'pods' ) }
								showTooltip
								extraProps={ {
									variant: 'link',
									href: editLink,
									target: '_blank',
									additionalProps: {
										rel: 'noreferrer noopener',
									},
								} }
							/>
						</Toolbar>
					) : null }

					{ downloadLink ? (
						<Toolbar label={ __( 'Download item', 'pods' ) }>
							<ToolbarButton
								icon={ download }
								label={ __( 'Download', 'pods' ) }
								showTooltip
								extraProps={ {
									variant: 'link',
									href: downloadLink,
									target: '_blank',
									additionalProps: {
										rel: 'noreferrer noopener',
									},
								} }
							/>
						</Toolbar>
					) : null }

					{ viewLink ? (
						<Toolbar label={ __( 'View item', 'pods' ) }>
							<ToolbarButton
								icon={ external }
								label={ __( 'View', 'pods' ) }
								showTooltip
								extraProps={ {
									variant: 'link',
									href: viewLink,
									target: '_blank',
									additionalProps: {
										rel: 'noreferrer noopener',
									},
								} }
							/>
						</Toolbar>
					) : null }

					{ isRemovable ? (
						<Toolbar label={ __( 'Remove item', 'pods' ) }>
							<ToolbarButton
								onClick={ ( event ) => {
									event.stopPropagation();
									removeItem();
								} }
								icon={ close }
								label={ __( 'Remove', 'pods' ) }
								showTooltip
							/>
						</Toolbar>
					) : null }

					{ showEditModal ? (
						<IframeModal
							title={ editIframeTitle || `${ fieldName }: Edit` }
							iframeSrc={ editLink }
							onClose={ () => setShowEditModal( false ) }
						/>
					) : null }
				</div>
			) : null }
		</div>
	);
};

ListItem.propTypes = {
	fieldName: PropTypes.string.isRequired,
	value: PropTypes.shape( {
		label: PropTypes.string.isRequired,
		value: PropTypes.string.isRequired,
	} ).isRequired,
	index: PropTypes.number.isRequired,
	isDraggable: PropTypes.bool.isRequired,
	isRemovable: PropTypes.bool.isRequired,
	moveUp: PropTypes.func,
	moveDown: PropTypes.func,
	removeItem: PropTypes.func.isRequired,
	fieldItemData: PropTypes.arrayOf( PropTypes.any ).isRequired,
	setFieldItemData: PropTypes.func.isRequired,
	defaultIcon: PropTypes.string,
	showIcon: PropTypes.bool,
	showDownloadLink: PropTypes.bool,
	showViewLink: PropTypes.bool,
	showEditLink: PropTypes.bool,
	showEditTitle: PropTypes.bool,
	editIframeTitle: PropTypes.string,
	onTitleChange: PropTypes.func,
	htmlAttrs: PropTypes.object,
};

export default ListItem;

