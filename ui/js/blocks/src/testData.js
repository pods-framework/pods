// Fields
export const textField = {
	type: 'TextControl',
	name: 'textField',
	fieldOptions: {
		className: 'text__container',
		type: 'text',
		help: 'Some help text',
		label: 'Label for the text field',
	},
	attributeOptions: {
		selector: '.text__container',
		source: 'text',
	},
};

export const textareaField = {
	type: 'TextareaControl',
	name: 'textareaField',
	fieldOptions: {
		className: 'textarea__container',
		help: 'Some help text',
		label: 'Label for the textarea field',
	},
	attributeOptions: {
		selector: '.textarea__container',
		source: 'text',
	},
};

export const richTextField = {
	type: 'RichText',
	name: 'richTextField',
	fieldOptions: {
		tagName: 'p',
		className: 'custom__container',
	},
	attributeOptions: {
		selector: '.custom__container',
		source: 'html',
	},
};

export const checkboxField = {
	type: 'CheckboxControl',
	name: 'checkboxField',
	fieldOptions: {
		heading: 'Checkbox Field',
		label: 'Checkbox Test',
		help: 'Additional help text',
	},
	attributeOptions: {
		type: 'boolean',
		default: true,
	},
};

export const checkboxGroup = {
	type: 'CheckboxGroup',
	name: 'checkboxGroup',
	fieldOptions: {
		heading: 'Checkbox Field',
		help: 'Additional help text',
		options: [
			{ label: 'First Option', value: 'first' },
			{ label: 'Second Option', value: 'second' },
			{ label: 'Third Option', value: 'third' },
		],
	},
	attributeOptions: {
		type: 'array',
	},
};

export const radioField = {
	type: 'RadioControl',
	name: 'radioField',
	fieldOptions: {
		heading: 'Radio Field',
		help: 'Additional help text',
		options: [
			{ label: 'First Option', value: 'first' },
			{ label: 'Second Option', value: 'second' },
			{ label: 'Third Option', value: 'third' },
		],
	},
	attributeOptions: {
		type: 'array',
	},
};

export const selectField = {
	type: 'SelectControl',
	name: 'selectField',
	fieldOptions: {
		heading: 'Select Field',
		help: 'Additional help text',
		options: [
			{ label: 'First Option', value: 'first' },
			{ label: 'Second Option', value: 'second' },
			{ label: 'Third Option', value: 'third' },
		],
	},
	attributeOptions: {
		type: 'array',
	},
};

export const dateTimeField = {
	type: 'DateTimePicker',
	name: 'dateTimeField',
	fieldOptions: {
		is12Hour: true,
		label: 'Label for the datetime field',
	},
	attributeOptions: {
		type: 'string',
	},
};

export const numberField = {
	type: 'NumberControl',
	name: 'numberField',
	fieldOptions: {
		isShiftStepEnabled: false,
		shiftStep: false,
		step: 1,
		label: 'Label for the number field',
	},
	attributeOptions: {
		type: 'number',
	},
};

// Templates
export const basicTemplate = '<div class="some_class">Something else here and a field: {@textField}</div>';

export const multipleFieldsTemplate = '<section><div>A field with content: {@textField}</div><br /><div></div><div>And a number: {@numberField}</div></section>';

export const templateWithEveryFieldType = `<div>
	A text field: {@textField}
	A textarea field: {@textareaField}
	A rich text field: {@richTextField}
	A checkbox field: {@checkboxField}
	A group of checkboxes: {@checkboxGroup}
	A group of radio buttons: {@radioField}
	A select menu: {@selectField}
	A date/time field: {@dateTimeField}
	A number field: {@numberField}
</div>`;

// Full Blocks
export const simpleBlock = {
	blockName: 'test/custom-block',
    renderType: 'js',
    category: 'layout',
    description: 'A block to test defining the fields.',
    icon: 'editor-insertmore',
    keywords:[ 'test' ],
    supports: {
      html: false
    },
    title: 'Custom Block',
	renderTemplate: basicTemplate,
	fields: [
		textField,
		checkboxField,
	]
};

export const simpleBlockProps = {
	className: 'simple-block-test',
	attributes: {
		'textField': 'Some content',
		'checkboxField': true,
	},
};

export const allFieldsBlock = {
	blockName: 'test/all-fields',
    renderType: 'js',
    category: 'layout',
    description: 'A block to test all supported fields.',
    icon: 'editor-insertmore',
    keywords:[ 'test' ],
    supports: {
      html: false
    },
    title: 'All Field Block',
	renderTemplate: templateWithEveryFieldType,
	fields: [
		textField,
		textareaField,
		richTextField,
		checkboxField,
		checkboxGroup,
		radioField,
		selectField,
		dateTimeField,
		numberField,
	]
};

export const allFieldsBlockProps = {
	className: 'all-fields-block-test',
	attributes: {
		'textField': '<em>Content for the text field</em>, but these <script></script>tags will be stripped <strong>out</strong>',
		'textareaField': '<em>Content for the textarea field</em>, but these <script></script>tags will be stripped <strong>out</strong>',
		'richTextField': 'Some content',
		'checkboxField': true,
		'checkboxGroup': [
			{ value: 'first', checked: true },
			{ value: 'second', checked: true },
			{ value: 'third', checked: false },
		],
		'radioField': 'first',
		'selectField': 'second',
		'dateTimeField': '1986-10-18T23:00:00',
		'numberField': 123456
	},
};
