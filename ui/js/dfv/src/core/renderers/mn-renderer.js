import jQuery from 'jquery';
import { PodsDFVFieldModel } from 'dfv/src/core/pods-field-model';

function mnRenderer( FieldClass, element, props ) {
	// Assemble the model and create the field
	const fieldModel = new PodsDFVFieldModel( {
		htmlAttr: props.data.htmlAttr,
		fieldConfig: props.data.fieldConfig,
	} );

	const newField = new FieldClass( {
		el: element,
		model: fieldModel,
		fieldItemData: props.data.fieldItemData,
	} );

	// Render the field, trigger an event for the outside world, and stash a reference
	newField.render();
	jQuery( element ).trigger( 'render' );
	return newField;
}

export default mnRenderer;
