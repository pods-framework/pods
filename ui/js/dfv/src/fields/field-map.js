import Avatar from 'dfv/src/fields/avatar';
import Boolean from 'dfv/src/fields/boolean';
import BooleanGroup from 'dfv/src/fields/boolean-group';
import Code from 'dfv/src/fields/code';
import Color from 'dfv/src/fields/color';
import ConditionalLogic from 'dfv/src/fields/conditional-logic';
import Currency from 'dfv/src/fields/currency';
import DateField from 'dfv/src/fields/date-field';
import DateTime from 'dfv/src/fields/datetime';
import EditPod from 'dfv/src/admin/edit-pod/edit-pod';
import Email from 'dfv/src/fields/email';
import File from 'dfv/src/fields/file';
import Heading from 'dfv/src/fields/heading';
import HTMLField from 'dfv/src/fields/html-field';
import NumberField from 'dfv/src/fields/number-field';
import Oembed from 'dfv/src/fields/oembed';
import Password from 'dfv/src/fields/password';
import Paragraph from 'dfv/src/fields/paragraph';
import Phone from 'dfv/src/fields/phone';
import Pick from 'dfv/src/fields/pick';
import Slug from 'dfv/src/fields/slug';
import Text from 'dfv/src/fields/text';
import Time from 'dfv/src/fields/time';
import Website from 'dfv/src/fields/website';
import Wysiwyg from 'dfv/src/fields/wysiwyg';

const FIELD_MAP = {
	avatar: {
		fieldComponent: Avatar,
		directRender: false,
	},
	boolean: {
		fieldComponent: Boolean,
		directRender: false,
	},
	boolean_group: {
		fieldComponent: BooleanGroup,
		directRender: false,
	},
	code: {
		fieldComponent: Code,
		directRender: false,
	},
	color: {
		fieldComponent: Color,
		directRender: false,
	},
	'conditional-logic': {
		fieldComponent: ConditionalLogic,
		directRender: false,
	},
	currency: {
		fieldComponent: Currency,
		directRender: false,
	},
	date: {
		fieldComponent: DateField,
		directRender: false,
	},
	datetime: {
		fieldComponent: DateTime,
		directRender: false,
	},
	'edit-pod': {
		fieldComponent: EditPod,
		directRender: true,
	},
	email: {
		fieldComponent: Email,
		directRender: false,
	},
	file: {
		fieldComponent: File,
		directRender: false,
	},
	heading: {
		fieldComponent: Heading,
		directRender: false,
	},
	html: {
		fieldComponent: HTMLField,
		directRender: false,
	},
	number: {
		fieldComponent: NumberField,
		directRender: false,
	},
	oembed: {
		fieldComponent: Oembed,
		directRender: false,
	},
	paragraph: {
		fieldComponent: Paragraph,
		directRender: false,
	},
	password: {
		fieldComponent: Password,
		directRender: false,
	},
	phone: {
		fieldComponent: Phone,
		directRender: false,
	},
	pick: {
		fieldComponent: Pick,
		directRender: false,
	},
	slug: {
		fieldComponent: Slug,
		directRender: false,
	},
	text: {
		fieldComponent: Text,
		directRender: false,
	},
	time: {
		fieldComponent: Time,
		directRender: false,
	},
	website: {
		fieldComponent: Website,
		directRender: false,
	},
	wysiwyg: {
		fieldComponent: Wysiwyg,
		directRender: false,
	},
};

export default FIELD_MAP;
