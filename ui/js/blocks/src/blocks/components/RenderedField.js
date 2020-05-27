/**
 * External dependencies
 */
import Select from 'react-select';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

import {
	RichText,
	MediaUploadCheck,
	MediaUpload,
} from '@wordpress/block-editor';

import {
	TextControl,
	TextareaControl,
	BaseControl,
	DateTimePicker,
	RadioControl,
	ColorPicker,
	Button,
} from '@wordpress/components';

import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import CheckboxGroup from '../../components/CheckboxGroup';
import CheckboxControlExtended from '../../components/CheckboxControlExtended';
import NumberControl from '../../components/NumberControl';

/**
 * Creates the handler for the 'onChange' prop for a field.
 *
 * @param {string} name Name of the field.
 * @param {Function} setAttributes The setAttributes function for a block.
 * @param {string} type The type of attribute ('string', 'array', 'number').
 *
 * @return {Function} Function update attributes to attach to an `onChange` prop.
 */
const createChangeHandler = ( name, setAttributes, type ) => ( newValue ) => {
	setAttributes( {
			[ name ]: 'NumberControl' === type ? parseInt( newValue, 10 ) : newValue,
	} );
}

/**
 * Renders an individual field to be used in a template.
 */
const RenderedField = ( {
	field,
	attributes,
	setAttributes
} ) => {
	const {
		name,
		type,
		fieldOptions = {},
	} = field;

	const fieldValue = attributes[ name ];

	const changeHandler = createChangeHandler( name, setAttributes, type );

	switch ( type ) {
		case 'TextControl': {
			const {
				type = 'text',
				help,
				label,
			} = fieldOptions;

			return (
				<TextControl
					key={ name }
					label={ label }
					value={ fieldValue }
					type={ type }
					help={ help }
					onChange={ changeHandler }
				/>
			);
		}
		case 'TextareaControl': {
			const {
				help,
				label,
			} = fieldOptions;

			return (
				<TextareaControl
					key={ name }
					label={ label }
					value={ fieldValue }
					help={ help }
					rows="4"
					onChange={ changeHandler }
				/>
			);
		}
		case 'RichText': {
			const { tagName = 'p' } = fieldOptions;

			return (
				<RichText
					key={ name }
					tagName={ tagName }
					value={ fieldValue }
					onChange={ changeHandler }
				/>
			);
		}
		case 'CheckboxControl': {
			const {
				label,
				help,
				heading = '',
			} = fieldOptions;

			return (
				<CheckboxControlExtended
					key={ name }
					heading={ heading }
					label={ label }
					help={ help }
					checked={ fieldValue }
					onChange={ changeHandler }
				/>
			);
		}
		case 'CheckboxGroup': {
			const {
				help,
				options,
				heading = '',
			} = fieldOptions;

			return (
				<CheckboxGroup
					key={ name }
					heading={ heading }
					help={ help }
					options={ options }
					values={ fieldValue }
					onChange={ changeHandler }
				/>
			);
		}
		case 'RadioControl': {
			const {
				help,
				options,
			} = fieldOptions;

			return (
				<RadioControl
					key={ name }
					help={ help }
					options={ options }
					selected={ fieldValue }
					onChange={ changeHandler }
				/>
			);
		}
		case 'SelectControl': {
			const {
				options,
				multiple,
				label,
			} = fieldOptions;

			const instanceId = useInstanceId( Select );
			const id = `inspector-select-control-${ instanceId }`;

			return (
				<BaseControl
					label={ label }
					id={ id }
					key={ name }
					className="full-width-base-control"
				>
					<Select
						id={ id }
						name={ name }
						options={ options }
						value={ fieldValue }
						isMulti={ multiple }
						onChange={ changeHandler }
						styles={ {
							container: ( provided ) => ( {
							...provided,
							width: '100%',
							} )
						} }
					/>
				</BaseControl>
			);
		}
		case 'DateTimePicker': {
			const {
				is12Hour,
				label,
			} = fieldOptions;

			return (
				<BaseControl
					label={ label }
					key={ name }
				>
					<DateTimePicker
						currentDate={ fieldValue }
						onChange={ changeHandler }
						is12Hour={ is12Hour }
					/>
				</BaseControl>
			);
		}
		case 'NumberControl': {
			const {
				isShiftStepEnabled,
				shiftStep,
				label,
				max = Infinity,
				min = -Infinity,
				step = 1,
			} = fieldOptions;

			const instanceId = useInstanceId( NumberControl );
			const id = `inspector-number-control-${ instanceId }`;

			return (
				<BaseControl
					label={ label }
					id={ id }
					key={ name }
				>
					<NumberControl
						id={ id }
						onChange={ changeHandler }
						isShiftStepEnabled={ isShiftStepEnabled }
						shiftStep={ shiftStep }
						max={ max }
						min={ min }
						step={ step }
						value={ fieldValue || '' }
					/>
				</BaseControl>
			);
		}
		case 'MediaUpload': {
			const ALLOWED_MEDIA_TYPES = [ 'image' ];

			return (
				<div>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => { changeHandler( { id: media.id, url: media.url, title: media.title } ); } }
							allowedTypes={ ALLOWED_MEDIA_TYPES }
							value={ fieldValue }
							render={ ( { open } ) => (
								<Button onClick={ open } isPrimary>
									{ __( 'Upload' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ !! fieldValue &&
						<Button
							onClick={ () => changeHandler( null ) }
							isSecondary
						>
							{ __( 'Remove Upload' ) }
						</Button>
					}
					{ fieldValue && !! fieldValue.title && (
						<div>
							{ fieldValue.title }
						</div>
					) }
				</div>
			);
		}
		case 'ColorPicker': {
			return (
				<ColorPicker
					color={ fieldValue }
					onChangeComplete={ ( value ) => changeHandler( value.hex ) }
					disableAlpha
				/>
			);
		}
		default:
			return null;
	}
};

export default RenderedField;
