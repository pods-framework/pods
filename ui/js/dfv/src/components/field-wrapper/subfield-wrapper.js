/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * Pods components
 */

/**
 * Other Pods dependencies
 */
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const SubfieldWrapper = ( {
	fieldConfig,
	FieldComponent,
	startControls,
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

	return (
		<div className="pods-field-wrapper__item">
			{ startControls ? (
				<div className="pods-field-wrapper__controls pods-field-wrapper__controls--start">
					{ startControls }
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

			{ endControls ? (
				<div className="pods-field-wrapper__controls pods-field-wrapper__controls--end">
					{ endControls }
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
	 * Additional controls to add.
	 */
	controls: PropTypes.element,

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
