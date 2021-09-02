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

import useBidirectionalFieldData from 'dfv/src/hooks/useBidirectionalFieldData';
import loadAjaxOptions from '../../helpers/loadAjaxOptions';

import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './pick.scss';

const getFieldItemDataFromDataProp = ( data ) => {
	// Skip unless we're handling an object of values.
	if ( 'object' !== typeof data || Array.isArray( data ) ) {
		return [];
	}

	const entries = Object.entries( data );

	return entries.reduce( ( accumulator, entry ) => {
		if ( 'string' === typeof entry[ 1 ] ) {
			return [
				...accumulator,
				{
					id: entry[ 0 ],
					icon: '',
					name: entry[ 1 ],
					edit_link: '',
					link: '',
					selected: false,
				},
			];
		}

		const subOptions = Object.entries( entry[ 1 ] )
			.map( ( subEntry ) => ( { name: subEntry[ 1 ], id: subEntry[ 0 ] } ) );

		return [
			...accumulator,
			{
				id: subOptions,
				icon: '',
				name: entry[ 0 ],
				edit_link: '',
				link: '',
				selected: false,
			},
		];
	}, [] );
};

const formatValuesForReactSelectComponent = (
	value,
	fieldItemData = [],
	isMulti = false
) => {
	if ( ! value ) {
		return isMulti ? [] : [];
	}

	if ( ! isMulti ) {
		const selectedItemData = fieldItemData.find(
			( option ) => option.id.toString() === value.toString(),
		);

		return [
			{
				label: selectedItemData?.name,
				value: selectedItemData?.id.toString(),
			},
		];
	}

	const splitValue = Array.isArray( value ) ? value : value.split( ',' );

	return splitValue.map(
		( currentValue ) => {
			const fullFieldItem = fieldItemData.find(
				( option ) => option.id.toString() === currentValue.toString()
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
			ajax_data: ajaxData,
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
			// pick_custom: pickCustomOptions,
			// pick_display,
			// pick_display_format_multi,
			// pick_display_format_separator,
			pick_format_multi: formatMulti = 'autocomplete',
			pick_format_single: formatSingle = 'dropdown',
			pick_format_type: formatType = 'single',
			// pick_groupby,
			pick_limit: limit,
			// pick_object: pickObject,
			// pick_orderby: orderBy,
			// pick_post_status: postStatus,
			// pick_select_text: selectText = __( '-- Select One --', 'pods' ),
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
			type: fieldType,
		},
		setValue,
		value,
		setHasBlurred,
		podType,
		podName,
		allPodValues,
	} = props;

	const isSingle = 'single' === formatType;
	const isMulti = 'multi' === formatType;

	const [ showAddNewIframe, setShowAddNewIframe ] = useState( false );

	// Most options are set from the field's fieldItemData, but this could get
	// modified by the add/edit modals, or by loading ajax options, so we need to track
	// this in state, starting with the supplied fieldItemData from the page load.
	const [ modifiedFieldItemData, setModifiedFieldItemData ] = useState(
		fieldItemData ? fieldItemData : getFieldItemDataFromDataProp( data )
	);

	const { bidirectionFieldItemData } = useBidirectionalFieldData(
		podType,
		podName,
		name,
		fieldType,
		allPodValues?.pick_object || '',
	);

	useEffect( () => {
		// This is only relevant on the "Bidirectional"/'sister_id' field.
		if ( 'sister_id' !== name ) {
			return;
		}

		if ( bidirectionFieldItemData.length ) {
			setModifiedFieldItemData( bidirectionFieldItemData );
		}
	}, [ bidirectionFieldItemData ] );

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

			setModifiedFieldItemData( ( prevData ) => [
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
					value={ value || '' }
					setValue={ setValueWithLimit }
					options={ modifiedFieldItemData }
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
					options={ modifiedFieldItemData }
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
				modifiedFieldItemData,
				isMulti,
			);

			const formattedOptions = modifiedFieldItemData.map( ( item ) => ( {
				label: item.name,
				value: item.id,
			} ) );

			return (
				<>
					<AsyncSelect
						controlShouldRenderValue={ ! isListSelect }
						defaultOptions={ formattedOptions }
						loadOptions={ ajaxData?.ajax ? loadAjaxOptions( ajaxData ) : undefined }
						value={ isMulti ? formattedValue : formattedValue[ 0 ] }
						// translators: %s is the field label.
						placeholder={ sprintf( __( 'Search %s…', 'pods' ), label ) }
						isMulti={ isMulti }
						onChange={ ( newOption ) => {
							// The new value(s) may have been loaded by ajax, if it was, then it wasn't
							// in our array of dataOptions, and we should add it, so we can keep track of
							// the label.
							setModifiedFieldItemData( ( prevData ) => {
								const prevDataValues = prevData.map( ( option ) => option.id );
								const updatedData = [ ...prevData ];
								const newOptions = isMulti ? newOption : [ newOption ];

								newOptions.forEach( ( option ) => {
									if ( prevDataValues.includes( option.value ) ) {
										return;
									}

									updatedData.push( {
										id: option.value,
										name: option.label,
									} );
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
							fieldItemData={ modifiedFieldItemData }
							setFieldItemData={ setModifiedFieldItemData }
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
							key={ `${ name }-${ selectedValue.value }` }
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
				options={ modifiedFieldItemData }
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

	/**
	 * Pod type being edited.
	 */
	podType: PropTypes.string,

	/**
	 * Pod slug being edited.
	 */
	podName: PropTypes.string,

	/**
	 * All field values for the Pod to use for
	 * validating dependencies.
	 */
	allPodValues: PropTypes.object,

	/**
	 * Field value.
	 */
	value: PropTypes.oneOfType( [
		PropTypes.arrayOf(
			PropTypes.oneOfType( [
				PropTypes.string,
				PropTypes.number,
			] )
		),
		PropTypes.string,
		PropTypes.number,
	] ),
};

export default Pick;
