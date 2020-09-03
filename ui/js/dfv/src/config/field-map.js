import mnRenderer from 'dfv/src/core/renderers/mn-renderer';
import reactRenderer from 'dfv/src/core/renderers/react-renderer';
import reactDirectRenderer from 'dfv/src/core/renderers/react-direct-renderer';

import * as fields from 'dfv/src/field-manifest';

const FIELD_MAP = {
	heading: {
		FieldClass: fields.PodsDFVHeading,
		renderer: reactRenderer,
	},
	html: {
		FieldClass: fields.PodsDFVHTML,
		renderer: reactRenderer,
	},
	file: {
		FieldClass: fields.File,
		renderer: mnRenderer,
	},
	avatar: {
		FieldClass: fields.File,
		renderer: mnRenderer,
	},
	pick: {
		FieldClass: fields.Pick,
		renderer: mnRenderer,
	},
	text: {
		FieldClass: fields.PodsDFVText,
		renderer: reactRenderer,
	},
	password: {
		FieldClass: fields.PodsDFVPassword,
		renderer: reactRenderer,
	},
	number: {
		FieldClass: fields.PodsDFVNumber,
		renderer: reactRenderer,
	},
	email: {
		FieldClass: fields.PodsDFVEmail,
		renderer: reactRenderer,
	},
	paragraph: {
		FieldClass: fields.PodsDFVParagraph,
		renderer: reactRenderer,
	},
	'edit-pod': {
		FieldClass: fields.PodsDFVEditPod,
		renderer: reactDirectRenderer,
	},
};

export default FIELD_MAP;
