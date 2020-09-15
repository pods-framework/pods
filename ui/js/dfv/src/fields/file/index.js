import React from 'react';
import PropTypes from 'prop-types';

import MarionetteAdapter from 'dfv/src/fields/marionette-adapter';
import { File as FileView } from './file-upload';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

// @todo this may be an incomplete field component
// @todo add tests
const File = ( props ) => {
	const {
		fieldConfig = {},
		htmlAttr = {},
		value,
		setValue,
	} = props;

	const setValueFromModels = ( models ) => {
		// const uploadedFileIDs = models.map( ( model ) => model.id );

		const fileData = models.map( ( model ) => {
			return {
				id: model.get( 'id' ),
				icon: model.get( 'icon' ),
				name: model.get( 'name' ),
				edit_link: model.get( 'edit_link' ),
				link: model.get( 'link' ),
				download: model.get( 'download' ),
			};
		} );

		console.log( 'setting value for File component in setValueFromModels: ', fileData );

		setValue( fileData );
	};

	return (
		<MarionetteAdapter
			{ ...props }
			htmlAttr={ htmlAttr }
			fieldConfig={ fieldConfig }
			View={ FileView }
			value={ value }
			setValue={ setValueFromModels }
		/>
	);
};

File.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE.isRequired,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.arrayOf(
		PropTypes.shape( {
			id: PropTypes.oneOfType( [
				PropTypes.string,
				PropTypes.number,
			] ).isRequired,
			icon: PropTypes.string.isRequired,
			name: PropTypes.string.isRequired,
			edit_link: PropTypes.string.isRequired,
			link: PropTypes.string.isRequired,
			download: PropTypes.string.isRequired,
		} )
	),
};

export default File;
