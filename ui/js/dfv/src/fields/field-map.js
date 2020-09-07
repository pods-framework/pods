import mnRenderer from 'dfv/src/core/renderers/mn-renderer';
import reactRenderer from 'dfv/src/core/renderers/react-renderer';
import reactDirectRenderer from 'dfv/src/core/renderers/react-direct-renderer';

import Boolean from 'dfv/src/fields/boolean';
import EditPod from 'dfv/src/admin/edit-pod/edit-pod';
import Email from 'dfv/src/fields/email';
import Heading from 'dfv/src/fields/heading';
import HTMLField from 'dfv/src/fields/html-field';
import NumberField from 'dfv/src/fields/number-field';
import Password from 'dfv/src/fields/password';
import Paragraph from 'dfv/src/fields/paragraph';
import Pick from 'dfv/src/fields/pick';
import Text from 'dfv/src/fields/text';

// Backbone fields, may not work:
import { File } from 'dfv/src/fields/file';

const FIELD_MAP = {
	avatar: {
		fieldComponent: File,
		renderer: mnRenderer,
	},
	boolean: {
		fieldComponent: Boolean,
		renderer: reactRenderer,
	},
	'edit-pod': {
		fieldComponent: EditPod,
		renderer: reactDirectRenderer,
	},
	email: {
		fieldComponent: Email,
		renderer: reactRenderer,
	},
	file: {
		fieldComponent: File,
		renderer: mnRenderer,
	},
	heading: {
		fieldComponent: Heading,
		renderer: reactRenderer,
	},
	html: {
		fieldComponent: HTMLField,
		renderer: reactRenderer,
	},
	number: {
		fieldComponent: NumberField,
		renderer: reactRenderer,
	},
	paragraph: {
		fieldComponent: Paragraph,
		renderer: reactRenderer,
	},
	password: {
		fieldComponent: Password,
		renderer: reactRenderer,
	},
	pick: {
		fieldComponent: Pick,
		renderer: reactRenderer,
	},
	text: {
		fieldComponent: Text,
		renderer: reactRenderer,
	},
};

export default FIELD_MAP;
