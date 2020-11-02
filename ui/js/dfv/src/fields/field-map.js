import reactRenderer from 'dfv/src/core/renderers/react-renderer';
import reactDirectRenderer from 'dfv/src/core/renderers/react-direct-renderer';

import Avatar from 'dfv/src/fields/avatar';
import Boolean from 'dfv/src/fields/boolean';
import Code from 'dfv/src/fields/code';
import Color from 'dfv/src/fields/color';
import Currency from 'dfv/src/fields/currency';
import EditPod from 'dfv/src/admin/edit-pod/edit-pod';
import Email from 'dfv/src/fields/email';
import File from 'dfv/src/fields/file';
import Heading from 'dfv/src/fields/heading';
import HTMLField from 'dfv/src/fields/html-field';
import NumberField from 'dfv/src/fields/number-field';
import Password from 'dfv/src/fields/password';
import Paragraph from 'dfv/src/fields/paragraph';
import Phone from 'dfv/src/fields/phone';
import Pick from 'dfv/src/fields/pick';
import Slug from 'dfv/src/fields/slug';
import Text from 'dfv/src/fields/text';
import Website from 'dfv/src/fields/website';
import Wysiwyg from 'dfv/src/fields/wysiwyg';

const FIELD_MAP = {
	avatar: {
		fieldComponent: Avatar,
		renderer: reactRenderer,
	},
	boolean: {
		fieldComponent: Boolean,
		renderer: reactRenderer,
	},
	code: {
		fieldComponent: Code,
		renderer: reactRenderer,
	},
	color: {
		fieldComponent: Color,
		renderer: reactRenderer,
	},
	currency: {
		fieldComponent: Currency,
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
		renderer: reactRenderer,
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
	phone: {
		fieldComponent: Phone,
		renderer: reactRenderer,
	},
	pick: {
		fieldComponent: Pick,
		renderer: reactRenderer,
	},
	slug: {
		fieldComponent: Slug,
		renderer: reactRenderer,
	},
	text: {
		fieldComponent: Text,
		renderer: reactRenderer,
	},
	website: {
		fieldComponent: Website,
		renderer: reactRenderer,
	},
	wysiwyg: {
		fieldComponent: Wysiwyg,
		renderer: reactRenderer,
	},
};

export default FIELD_MAP;
