# CheckboxGroup

Organizes a group of checkboxes with semantic markup.

## Table of contents

1. [Design guidelines](#design-guidelines)
2. [Development guidelines](#development-guidelines)

## Design guidelines

### Usage

Use when you need to render multiple checkboxes grouped together in a `fieldset` element.

## Development guidelines

### Usage

Render an is author checkbox:
```jsx
import { useState } from '@wordpress/element';
import { CheckboxGroup } from 'components/CheckboxGroup';

const MyCheckboxGroup = () => {
	const [ isChecked, setChecked ] = useState( true );
	return (
		<CheckboxGroup
			heading="A set of options"
			help="Which of these options should we select?"
			onChange={ setCheckedSettings }
			options={ [
				{ label: 'First Option', value: 'first' },
				{ label: 'Second Option', value: 'second' },
				{ label: 'Third Option', value: 'third' },
			] }
			values={ [
				{ value: 'first', checked: true },
				{ value: 'second', checked: true },
				{ value: 'third', checked: false },
			] }
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

#### options

An array of checkboxes to create, each containing a `label` and `value`, as strings.

- Type: `Array`
- Required: No

#### values

An array of values for each checkbox. Each should contain a `value`, which matches one of the `options`, and a `checked` boolean. For the `checked` attribute, if checked is true the checkbox will be checked. If checked is false the checkbox will be unchecked.

- Type: `Array`
- Required: No

#### onChange

A function that receives the checked state (boolean) as input.

- Type: `function`
- Required: Yes
