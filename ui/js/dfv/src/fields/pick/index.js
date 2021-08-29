/**
 * External dependencies
 */
import React, { useState, useEffect } from 'react';
import AsyncSelect from 'react-select/async';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Other Pods dependencies
 */
import SimpleSelect from './simple-select';
import RadioSelect from './radio-select';
import CheckboxSelect from './checkbox-select';
import ListSelectValues from './list-select-values';

import IframeModal from 'dfv/src/components/iframe-modal';

import loadAjaxOptions from '../../helpers/loadAjaxOptions';
import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './pick.scss';

// We may get the data value as an array or an object.
const formatDataFromProp = ( data ) => {
	// Skip unless we're handling an object of values.
	if ( 'object' !== typeof data || Array.isArray( data ) ) {
		return data;
	}

	const entries = Object.entries( data );

	return entries.reduce( ( accumulator, entry ) => {
		if ( 'string' === typeof entry[ 1 ] ) {
			return [
				...accumulator,
				{
					label: entry[ 1 ],
					value: entry[ 0 ],
				},
			];
		}

		const subOptions = Object.entries( entry[ 1 ] )
			.map( ( subEntry ) => ( { label: subEntry[ 1 ], value: subEntry[ 0 ] } ) );

		return [
			...accumulator,
			{
				label: entry[ 0 ],
				value: subOptions,
			},
		];
	}, [] );
};

const formatValuesForReactSelectComponent = (
	value,
	options = [],
	fieldItemData = [],
	isMulti = false
) => {
	if ( ! value ) {
		return isMulti ? [] : [];
	}

	if ( ! isMulti ) {
		return [
			options.find( ( option ) => option.value === value ),
		];
	}

	const splitValue = Array.isArray( value ) ? value : value.split( ',' );

	return splitValue.map(
		( currentValue ) => {
			const fullValueFromOptions = options.find(
				( option ) => option.value === currentValue
			);

			if ( fullValueFromOptions ) {
				return fullValueFromOptions;
			}

			const fullFieldItem = fieldItemData.find(
				( item ) => Number( item.id ) === Number( currentValue )
			);

			if ( fullFieldItem ) {
				return {
					label: fullFieldItem?.name,
					value: fullFieldItem?.id.toString(),
				};
			}

			return {};
		}
	);
};

const formatValuesForHTMLSelectElement = ( value, isMulti ) => {
	if ( ! value ) {
		return undefined;
	}

	if ( ! isMulti ) {
		return value;
	}

	return Array.isArray( value ) ? value : value.split( ',' );
};

const Pick = ( props ) => {
	const {
		fieldConfig: {
			htmlAttr: htmlAttributes = {},
			readonly: readOnly,
			fieldItemData,
			data = [],
			label,
			name,
			default_icon: defaultIcon,
			iframe_src: addNewIframeSrc,
			iframe_title_add: addNewIframeTitle,
			iframe_title_edit: editIframeTitle,
			pick_allow_add_new: allowAddNew,
			pick_custom: pickCustomOptions,
			// pick_display,
			// pick_display_format_multi,
			// pick_display_format_separator,
			pick_format_multi: formatMulti = 'autocomplete',
			pick_format_single: formatSingle = 'dropdown',
			pick_format_type: formatType = 'single',
			// pick_groupby,
			pick_limit: limit,
			pick_object: pickObject,
			// pick_orderby: orderBy,
			// pick_post_status: postStatus,
			pick_select_text: selectText = __( '-- Select One --', 'pods' ),
			pick_show_edit_link: showEditLink,
			pick_show_icon: showIcon,
			pick_show_view_link: showViewLink,
			// pick_table,
			// pick_table_id,
			// pick_table_index,
			// pick_taggable,
			// pick_user_role,
			// pick_val: pickValue,
			// rest_pick_depth: pickDepth,
			// rest_pick_response: pickResponse,
			// pick_where,
			ajax_data: ajaxData,
		},
		setValue,
		value,
		setHasBlurred,
	} = props;

	const isSingle = 'single' === formatType;
	const isMulti = 'multi' === formatType;

	const [ showAddNewIframe, setShowAddNewIframe ] = useState( false );

	// The options could be derived from the `data` prop (as a default),
	// or we may need to do more work to break them apart or load them by the API.
	const [ dataOptions, setDataOptions ] = useState( formatDataFromProp( data ) );

	// fieldItemData may get edited by add/edit modals, but we only need to track this
	// in state.
	const [ editedFieldItemData, setEditedFieldItemData ] = useState( fieldItemData );

	useEffect( () => {
		if ( 'custom-simple' !== pickObject ) {
			return;
		}

		const unsplitOptions = pickCustomOptions.split( '\n' );

		// Set an empty array if no entries or malformed.
		if ( ! unsplitOptions.length ) {
			setDataOptions( [] );
			return;
		}

		const optionEntries = unsplitOptions.map(
			( unsplitOption ) => {
				const splitOption = unsplitOption.split( '|' );

				// Return if malformed entry.
				if ( 1 === splitOption.length ) {
					return {
						value: splitOption[ 0 ],
						label: splitOption[ 0 ],
					};
				} else if ( 2 !== splitOption.length ) {
					return null;
				}

				return {
					value: splitOption[ 0 ],
					label: splitOption[ 1 ],
				};
			}
		);

		// Filter out any options missing the value or label.
		const filteredOptionEntries = optionEntries.filter(
			( entry ) => entry.value && entry.label
		);

		setDataOptions( filteredOptionEntries );
	}, [ pickObject, pickCustomOptions ] );

	const setValueWithLimit = ( newValue ) => {
		// We don't need to worry about limits if this isn't a multi-select field.
		if ( isSingle ) {
			setValue( newValue );
			setHasBlurred( true );

			return;
		}

		// Filter out empty values that could have gotten passed in.
		const filteredNewValues = newValue.filter( ( item ) => !! item );

		// If no limit is set, set the value.
		const numericLimit = parseInt( limit, 10 ) || 0;

		if ( isNaN( numericLimit ) || 0 === numericLimit || -1 === numericLimit ) {
			setHasBlurred( true );
			setValue( filteredNewValues );
			return;
		}

		// If we're trying to set more items than the limit allows, just return.
		if ( filteredNewValues.length > numericLimit ) {
			return;
		}

		setValue( filteredNewValues );
		setHasBlurred( true );
	};

	useEffect( () => {
		const listenForIframeMessages = ( event ) => {
			if (
				event.origin !== window.location.origin ||
				'PODS_MESSAGE' !== event.data.type ||
				! event.data.data
			) {
				return;
			}

			setShowAddNewIframe( false );

			const { data: newData = {} } = event.data;

			setEditedFieldItemData( ( prevData ) => [
				...prevData,
				newData,
			] );

			setValueWithLimit( [
				...( value || [] ),
				newData?.id.toString(),
			] );
		};

		if ( showAddNewIframe ) {
			window.addEventListener( 'message', listenForIframeMessages, false );
		} else {
			window.removeEventListener( 'message', listenForIframeMessages, false );
		}

		return () => {
			window.removeEventListener( 'message', listenForIframeMessages, false );
		};
	}, [ showAddNewIframe ] );

	// There are a variety of different "select" components, this
	// chooses the right one based on the options.
	const renderSelectComponent = () => {
		if ( ! isMulti && 'radio' === formatSingle ) {
			return (
				<RadioSelect
					htmlAttributes={ htmlAttributes }
					name={ name }
					value={ value }
					setValue={ setValueWithLimit }
					options={ dataOptions }
					readOnly={ !! readOnly }
				/>
			);
		}

		if (
			( isSingle && 'checkbox' === formatSingle ) ||
			( isMulti && 'checkbox' === formatMulti )
		) {
			let formattedValue = value;

			if ( isMulti ) {
				formattedValue = Array.isArray( value )
					? value
					: ( value || '' ).split( ',' );
			}

			return (
				<CheckboxSelect
					htmlAttributes={ htmlAttributes }
					name={ name }
					value={ formattedValue }
					isMulti={ isMulti }
					setValue={ setValueWithLimit }
					options={ dataOptions }
					readOnly={ !! readOnly }
				/>
			);
		}

		if (
			( isSingle && 'list' === formatSingle ) ||
			( isMulti && 'list' === formatMulti ) ||
			( isSingle && 'autocomplete' === formatSingle ) ||
			( isMulti && 'autocomplete' === formatMulti )
		) {
			const isListSelect = ( isSingle && 'list' === formatSingle ) || ( isMulti && 'list' === formatMulti );

			const formattedValue = formatValuesForReactSelectComponent(
				value,
				dataOptions,
				editedFieldItemData,
				isMulti,
			);

			return (
				<>
					<AsyncSelect
						controlShouldRenderValue={ ! isListSelect }
						defaultOptions={ dataOptions }
						loadOptions={ ajaxData?.ajax ? loadAjaxOptions( ajaxData ) : undefined }
						value={ isMulti ? formattedValue : formattedValue[ 0 ] }
						// translators: %s is the field label.
						placeholder={ sprintf( __( 'Search %s…', 'pods' ), label ) }
						isMulti={ isMulti }
						onChange={ ( newOption ) => {
							// The new value(s) may have been loaded by ajax, if it was, then it wasn't
							// in our array of dataOptions, and we should add it, so we can keep track of
							// the label.
							setDataOptions( ( prevData ) => {
								const prevDataValues = prevData.map( ( option ) => option.value );
								const updatedData = [ ...prevData ];
								const newOptions = isMulti ? newOption : [ newOption ];

								newOptions.forEach( ( option ) => {
									if ( prevDataValues.includes( option.value ) ) {
										return;
									}

									updatedData.push( option );
								} );

								return updatedData;
							} );

							if ( isMulti ) {
								setValueWithLimit( newOption.map(
									( selection ) => selection.value )
								);
							} else {
								setValueWithLimit( newOption.value );
							}
						} }
						readOnly={ !! readOnly }
					/>

					{ isListSelect ? (
						<ListSelectValues
							fieldName={ name }
							value={ formattedValue }
							setValue={ setValueWithLimit }
							fieldItemData={ editedFieldItemData }
							setFieldItemData={ setEditedFieldItemData }
							isMulti={ isMulti }
							limit={ parseInt( limit, 10 ) || 0 }
							defaultIcon={ defaultIcon }
							showIcon={ toBool( showIcon ) }
							showViewLink={ toBool( showViewLink ) }
							showEditLink={ toBool( showEditLink ) }
							editIframeTitle={ editIframeTitle }
							readOnly={ !! readOnly }
						/>
					) : null }

					{ formattedValue.map( ( selectedValue, index ) => (
						<input
							name={ `${ name }[${ index }]` }
							key={ selectedValue.value }
							type="hidden"
							value={ selectedValue.value }
						/>
					) ) }
				</>
			);
		}

		return (
			<SimpleSelect
				htmlAttributes={ htmlAttributes }
				name={ name }
				value={ formatValuesForHTMLSelectElement( value, isMulti ) }
				setValue={ ( newValue ) => setValueWithLimit( newValue ) }
				options={ dataOptions }
				placeholder={ selectText }
				isMulti={ isMulti }
				readOnly={ !! readOnly }
			/>
		);
	};

	return (
		<>
			{ renderSelectComponent() }

			{ ( allowAddNew && addNewIframeSrc ) ? (
				<Button
					className="pods-related-add-new pods-modal"
					onClick={ () => setShowAddNewIframe( true ) }
					isSecondary
				>
					{ __( 'Add New', 'pods', ) }
				</Button>
			) : null }

			{ showAddNewIframe ? (
				<IframeModal
					title={ addNewIframeTitle }
					iframeSrc={ addNewIframeSrc }
					onClose={ () => setShowAddNewIframe( false ) }
				/>
			) : null }
		</>
	);
};

Pick.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.string ),
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default Pick;
