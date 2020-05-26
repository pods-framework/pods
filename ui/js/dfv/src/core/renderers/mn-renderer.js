import jQuery from 'jquery';
import { PodsDFVFieldModel } from 'dfv/src/core/pods-field-model';

function mnRenderer( FieldClass, element, data ) {
	// Assemble the model and create the field
	const fieldModel = new PodsDFVFieldModel( {
		htmlAttr: data.htmlAttr,
		fieldConfig: data.fieldConfig,
	} );

	const newField = new FieldClass( {
		el: element,
		model: fieldModel,
		fieldItemData: data.fieldItemData,
	} );

	// Render the field, trigger an event for the outside world, and stash a reference
	newField.render();
	jQuery( element ).trigger( 'render' );
	return newField;
}

export default mnRenderer;
