/**
 * External dependencies
 */
import React, { useState, useEffect } from 'react';
import AsyncSelect from 'react-select/async';
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import {
	chevronUp,
	chevronDown,
} from '@wordpress/icons';

/**
 * Other Pods dependencies
 */
import loadAjaxOptions from '../../helpers/loadAjaxOptions';
import IframeModal from 'dfv/src/components/iframe-modal';
import { PICK_OPTIONS, FIELD_PROP_TYPE } from 'dfv/src/config/prop-types';

import './list-select.scss';

const ListSelectItem = ( {
	fieldName,
	itemName,
	itemId,
	value,
	editLink,
	viewLink,
	editIframeTitle,
	icon,
	isRemovable,
	moveUp,
	moveDown,
	removeItem,
	setFieldItemData,
} ) => {
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
		<li className="pods-dfv-list-item pods-relationship">
			<input
				name={ itemName }
				id={ itemId }
				type="hidden"
				value={ value.value }
			/>

			<ul className="pods-dfv-list-meta relationship-item">
				<li className="pods-dfv-list-col pods-dfv-list-handle pods-list-select-move-buttons">
					<Button
						className={
							classnames(
								'pods-list-select-move-buttons__button',
								! moveUp && 'pods-list-select-move-buttons__button--disabled'
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
								'pods-list-select-move-buttons__button',
								! moveDown && 'pods-list-select-move-buttons__button--disabled'
							)
						}
						showTooltip
						disabled={ ! moveDown }
						onClick={ moveDown }
						icon={ chevronDown }
						label={ __( 'Move down', 'pods' ) }
					/>
				</li>

				{ icon ? (
					<li className="pods-dfv-list-col pods-dfv-list-icon">
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

				<li className="pods-dfv-list-col pods-dfv-list-name">
					{ value.label }
				</li>

				{ isRemovable ? (
					<li className="pods-dfv-list-col pods-dfv-list-remove">
						<a
							href="#remove"
							title={ __( 'Deselect', 'pods' ) }
							onClick={ removeItem }
						>
							{ __( 'Deselect', 'pods' ) }
						</a>
					</li>
				) : null }

				{ viewLink ? (
					<li className="pods-dfv-list-col pods-dfv-list-link">
						<a
							href={ viewLink }
							title={ __( 'View', 'pods' ) }
							target="_blank"
							rel="noreferrer"
						>
							{ __( 'View', 'pods' ) }
						</a>
					</li>
				) : null }

				{ editLink ? (
					<li className="pods-dfv-list-col pods-dfv-list-edit">
						<a
							href={ editLink }
							title={ __( 'Edit', 'pods' ) }
							target="_blank"
							rel="noreferrer"
							onClick={ ( event ) => {
								event.preventDefault();
								setShowEditModal( true );
							} }
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
};

ListSelectItem.propTypes = {
	fieldName: PropTypes.string.isRequired,
	itemName: PropTypes.string.isRequired,
	itemId: PropTypes.string.isRequired,
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
};

const ListSelect = ( {
	name,
	value,
	options,
	fieldItemData,
	setFieldItemData,
	setValue,
	placeholder,
	isMulti,
	limit,
	defaultIcon,
	showIcon,
	showViewLink,
	showEditLink,
	editIframeTitle,
	readOnly = false,
	ajaxData,
} ) => {
	// Always have an array of values for the list, even if
	// we were just passed a single object.
	let arrayOfValues = [];

	if ( value ) {
		arrayOfValues = isMulti ? value : [ value ];
	}

	const removeValueAtIndex = ( index = 0 ) => {
		if ( isMulti ) {
			setValue(
				[
					...arrayOfValues.slice( 0, index ),
					...arrayOfValues.slice( index + 1 ),
				].map( ( item ) => item.value )
			);
		} else {
			setValue( undefined );
		}
	};

	const swapItems = ( oldIndex, newIndex ) => {
		if ( ! isMulti ) {
			throw 'Swap items shouldn\'nt be called on a single ListSelect';
		}

		const newValues = [ ...arrayOfValues ];
		const tempValue = newValues[ newIndex ];

		newValues[ newIndex ] = newValues[ oldIndex ];
		newValues[ oldIndex ] = tempValue;

		setValue(
			newValues.map( ( item ) => item.value ),
		);
	};

	return (
		<>
			{ ! readOnly && (
				<div className="pods-ui-list-autocomplete">
					<AsyncSelect
						defaultOptions={ options }
						loadOptions={ ajaxData?.ajax ? loadAjaxOptions( ajaxData ) : undefined }
						value={ value }
						placeholder={ placeholder }
						isMulti={ isMulti }
						controlShouldRenderValue={ false }
						onChange={ ( newOption ) => {
							if ( isMulti ) {
								setValue( newOption.map( ( selection ) => selection.value ) );
							} else {
								setValue( newOption.value );
							}
						} }
					/>
				</div>
			) }

			<div className="pods-pick-values">
				{ !! arrayOfValues.length && (
					<ul className="pods-dfv-list pods-relationship">
						{ arrayOfValues.map( ( valueItem, index ) => {
							const itemName = isMulti ? `${ name }[${ index }]` : name;
							const itemId = isMulti ? `${ name }[${ index }]` : name;

							// There may be additional data in an object from the fieldItemData
							// array.
							const moreData = fieldItemData.find(
								( item ) => item?.id === valueItem.value
							);

							const icon = showIcon ? ( moreData?.icon || defaultIcon ) : undefined;

							// May need to change the label, if it differs from the provided value.
							const displayValue = valueItem;

							const matchingFieldItemData = fieldItemData.find(
								( item ) => Number( item.id ) === Number( valueItem.value )
							);

							if ( matchingFieldItemData && matchingFieldItemData.name ) {
								displayValue.label = matchingFieldItemData.name;
							}

							return (
								<ListSelectItem
									key={ `${ name }-${ index }` }
									fieldName={ name }
									itemName={ itemName }
									itemId={ itemId }
									value={ displayValue }
									isDraggable={ ! readOnly && ( 1 !== limit ) }
									isRemovable={ ! readOnly }
									editLink={ ! readOnly && showEditLink ? moreData?.edit_link : undefined }
									viewLink={ showViewLink ? moreData?.link : undefined }
									editIframeTitle={ editIframeTitle }
									icon={ icon }
									removeItem={ () => removeValueAtIndex( index ) }
									setFieldItemData={ setFieldItemData }
									moveUp={
										( ! readOnly && index !== 0 )
											? () => swapItems( index, index - 1 )
											: undefined
									}
									moveDown={
										( ! readOnly && index !== ( arrayOfValues.length - 1 ) )
											? () => swapItems( index, index + 1 )
											: undefined
									}
								/>
							);
						} ) }
					</ul>
				) }
			</div>
		</>
	);
};

ListSelect.propTypes = {
	htmlAttributes: PropTypes.shape( {
		id: PropTypes.string,
		class: PropTypes.string,
		name: PropTypes.string,
	} ),
	name: PropTypes.string.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.shape( {
			label: PropTypes.string.isRequired,
			value: PropTypes.string.isRequired,
		} ),
		PropTypes.arrayOf(
			PropTypes.shape( {
				label: PropTypes.string.isRequired,
				value: PropTypes.string.isRequired,
			} )
		),
	] ),
	setValue: PropTypes.func.isRequired,
	options: PICK_OPTIONS.isRequired,
	fieldItemData: PropTypes.arrayOf(
		PropTypes.any,
	),
	setFieldItemData: PropTypes.func.isRequired,
	placeholder: PropTypes.string.isRequired,
	isMulti: PropTypes.bool.isRequired,
	limit: PropTypes.number.isRequired,
	defaultIcon: PropTypes.string,
	showIcon: PropTypes.bool.isRequired,
	showViewLink: PropTypes.bool.isRequired,
	showEditLink: PropTypes.bool.isRequired,
	editIframeTitle: PropTypes.string,
	readOnly: PropTypes.bool,
	ajaxData: FIELD_PROP_TYPE.ajax_data,
};

export default ListSelect;
