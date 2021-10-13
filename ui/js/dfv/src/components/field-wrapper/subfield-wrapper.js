/**
 * External dependencies
 */
import React from 'react';
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
 * Other Pods dependencies
 */
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const SubfieldWrapper = ( {
	fieldConfig,
	FieldComponent,
	isDraggable,
	endControls,
	isRepeatable,
	value,
	podType,
	podName,
	index,
	allPodValues,
	allPodFieldsMap,
	setValue,
	setHasBlurred,
	validationMessages,
	addValidationRules,
} ) => {
	// Adjust the `name`/`htmlAttr[name]` and IDs
	// for repeatable fields, so that each value gets saved.
	const subfieldConfig = {
		...fieldConfig,
		name: isRepeatable ? `${ fieldConfig.name }[${ index }]` : fieldConfig.name,
		htmlAttr: {
			...( fieldConfig.htmlAttr || {} ),
		},
	};

	if ( subfieldConfig.htmlAttr?.name && isRepeatable ) {
		subfieldConfig.htmlAttr.name = `${ subfieldConfig.htmlAttr.name }[${ index }]`;
	}

	if ( subfieldConfig.htmlAttr?.id && isRepeatable ) {
		subfieldConfig.htmlAttr.id = `${ subfieldConfig.htmlAttr.id }-${ index }`;
	}

	// Set up useSortable hook
	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
		// isDragging,
	} = useSortable( {
		id: index.toString(),
		disabled: ! isDraggable,
	} );

	const style = {
		transform: CSS.Translate.toString( transform ),
		transition,
	};

	return (
		<div
			className="pods-field-wrapper__item"
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

						{ endControls ? endControls : null }
					</Toolbar>
				</div>
			) : null }

			<div className="pods-field-wrapper__field">
				<FieldComponent
					value={ value }
					// Only the Boolean Group fields need allPodValues and allPodFieldsMap,
					// because the subfields need to reference these.
					podName={ podName }
					podType={ podType }
					allPodValues={ allPodValues }
					allPodFieldsMap={ allPodFieldsMap }
					setValue={ setValue }
					isValid={ !! validationMessages.length }
					addValidationRules={ addValidationRules }
					setHasBlurred={ () => setHasBlurred( true ) }
					fieldConfig={ subfieldConfig }
				/>
			</div>
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
	endControls: PropTypes.element,

	/**
	 * True if part of a Repeatable field.
	 */
	isRepeatable: PropTypes.bool.isRequired,

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
	 * Array of validation messages.
	 */
	validationMessages: PropTypes.arrayOf( PropTypes.string ).isRequired,

	/**
	 * Callback to add additional validation rules.
	 */
	addValidationRules: PropTypes.func.isRequired,

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
