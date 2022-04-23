/**
 * External dependencies
 */
import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Toolbar, ToolbarButton } from '@wordpress/components';

import { dragHandle } from '@wordpress/icons';

/**
 * Pods components
 */
import ValidationMessages from 'dfv/src/components/validation-messages';

/**
 * Other Pods dependencies
 */
import useValidation from 'dfv/src/hooks/useValidation';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const SubfieldWrapper = ( {
	fieldConfig,
	FieldComponent,
	isDraggable,
	moveControls,
	deleteControl,
	value,
	podType,
	podName,
	index,
	allPodValues,
	allPodFieldsMap,
	setValue,
	setHasBlurred,
} ) => {
	// Adjust the `name`/`htmlAttr[name]` and IDs
	// for repeatable fields, so that each value gets saved.
	const subfieldConfig = {
		...fieldConfig,
		name: `${ fieldConfig.name }[${ index }]`,
		htmlAttr: {
			...( fieldConfig.htmlAttr || {} ),
		},
	};

	if ( subfieldConfig.htmlAttr?.name ) {
		subfieldConfig.htmlAttr.name = `${ subfieldConfig.htmlAttr.name }[${ index }]`;
	}

	if ( subfieldConfig.htmlAttr?.id ) {
		subfieldConfig.htmlAttr.id = `${ subfieldConfig.htmlAttr.id }-${ index }`;
	}

	const [ hasSubfieldBlurred, setHasSubfieldBlurred ] = useState( false );

	const handleBlur = () => {
		setHasSubfieldBlurred( true );
		setHasBlurred( true );
	};

	// Subfields get their own set of validation rules
	const [ validationMessages, addValidationRules ] = useValidation(
		[],
		value
	);

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
	} );

	const style = {
		transform: CSS.Translate.toString( transform ),
		transition,
	};

	const validationMessagesComponent = ( hasSubfieldBlurred && validationMessages.length ) ? (
		<ValidationMessages messages={ validationMessages } />
	) : undefined;

	return (
		<div
			className="pods-field-wrapper__item pods-field-wrapper__repeatable"
			ref={ setNodeRef }
			style={ style }
		>
			{ isDraggable ? (
				<div className="pods-field-wrapper__controls pods-field-wrapper__controls--start">
					<Toolbar label="Repeatable field">
						<ToolbarButton
							icon={ dragHandle }
							label={ __( 'Drag to reorder', 'pods' ) }
							showTooltip
							// Should not be able to tab to drag handle as this
							// button can only be used with a pointer device.
							tabIndex="-1"
							className="pods-field-wrapper__drag-handle"
							{ ...listeners }
							{ ...attributes }
						/>

						{ moveControls ? moveControls : null }
					</Toolbar>
				</div>
			) : null }

			<div className="pods-field-wrapper__field">
				<FieldComponent
					value={ value }
					podName={ podName }
					podType={ podType }
					allPodValues={ allPodValues }
					allPodFieldsMap={ allPodFieldsMap }
					setValue={ setValue }
					isValid={ !! validationMessages.length }
					addValidationRules={ addValidationRules }
					setHasBlurred={ handleBlur }
					fieldConfig={ subfieldConfig }
				/>

				{ validationMessagesComponent }
			</div>

			{ isDraggable && deleteControl ? (
				<div className="pods-field-wrapper__controls pods-field-wrapper__controls--end">
					<Toolbar label="Repeatable field delete">
						{ deleteControl ? deleteControl : null }
					</Toolbar>
				</div>
			) : null }
		</div>
	);
};

SubfieldWrapper.propTypes = {
	/**
	 * Field config.
	 */
	fieldConfig: FIELD_PROP_TYPE_SHAPE.isRequired,

	/**
	 * Component to render.
	 */
	FieldComponent: PropTypes.elementType.isRequired,

	/**
	 * Enable the draggable handle.
	 */
	isDraggable: PropTypes.bool.isRequired,

	/**
	 * Additional controls to add.
	 */
	moveControls: PropTypes.element,
	deleteControl: PropTypes.element,

	/**
	 * Field value.
	 */
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
		PropTypes.array,
	] ),

	/**
	 * Pod type being edited.
	 */
	podType: PropTypes.string,

	/**
	 * Pod slug being edited.
 	*/
	podName: PropTypes.string,

	/**
	 * All values for the Pod (not needed on most field types) (optional).
	 */
	allPodValues: PropTypes.object,

	/**
	 * All fields from the Pod, including ones that belong to other groups. This
	 * should be a Map object, keyed by the field name, to make lookup easier (optional).
	 */
	allPodFieldsMap: PropTypes.object,

	/**
	 * Function to update the field's value on change.
	 */
	setValue: PropTypes.func.isRequired,

	/**
	 * Function to call when a field has blurred.
 	*/
	setHasBlurred: PropTypes.func.isRequired,
};

export default SubfieldWrapper;
