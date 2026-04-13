# CheckboxControlExtended

Shows a checkbox with a heading and label.

## Table of contents

1. [Design guidelines](#design-guidelines)
2. [Development guidelines](#development-guidelines)

## Design guidelines

### Usage

Use when you need to render a checkbox with a heading, placed within a `fieldset` element and a `legend` for the heading.

## Development guidelines

### Usage

Render an is author checkbox:
```jsx
import { useState } from '@wordpress/element';
import { CheckboxControlExtended } from 'components/CheckboxControlExtended';

const MyCheckboxGroup = () => {
	const [ isChecked, setChecked ] = useState( true );
	return (
		<CheckboxControlExtended
			heading="My checkbox field"
            key="field_name"
            label="Check this to confirm"
			help="You can check this if you want."
            checked=false
			onChange={ setCheckedSettings }
		/>
	)
};
```

### Props

The set of props accepted by the component will be specified below.
Props not included in this set will be applied to the input element.

#### heading

A heading for the fieldset, that appears above the checkboxes. If the prop is not passed no heading will be rendered.

- Type: `String`
- Required: No

#### help

If this property is added, a help text will be generated using help property as the content.

- Type: `String|WPElement`
- Required: No

#### key

The key name of the control.

- Type: `string`
- Required: Yes

#### checked

The state of the control.

- Type: `Boolean`
- Required: No

#### onChange

A function that receives the checked state (boolean) as input.

- Type: `function`
- Required: Yes
