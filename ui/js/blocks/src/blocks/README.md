# createBlock

Dynamically creates a block based on field data.

## Table of contents

1. [Adding New Controls](#adding-new-controls)

## Adding New Controls

To add support for a new type of control, you'll need to create any custom components (if needed), update the config file, and update the `createBlockEditComponent()` and `renderBlockWithValues()` functions along with their tests.

To begin:

### 1. Create any components.

This can be skipped if there is a Gutenberg control that does exactly what you need it to do. See the [WordPress Block Editor Components Reference](https://developer.wordpress.org/block-editor/components/).

The components should live in the `components` directory, similar to the CheckboxGroup component.

### 2. Update test data.
The file `testData.js` contains fixtures that are used by the tests for both `createBlockEditComponent()` and eventually for `renderBlockWithValues()`.

#### Create Test Field Data
The field data under the "Fields" heading in this file is in the format that the block creator expects to be passed in the API.

The `type` property should match the name of the component that you created (or the component from Gutenberg core).

The `name` is specific to this field instance, and will be used in the template to render the block.

The `fieldOptions` contains props that are passed to the field's control component.

The `attributeOptions` are used to create the [attributes](https://developer.wordpress.org/block-editor/developers/block-api/block-attributes/) in the block.

#### Update the Template and Block Definition
You could create an extra template and a new block definition if you'd create a completely new test, or you can add on to the block that already exists to test every supported type of field.

To add to the existing block, find the `templateWithEveryFieldType` const and add a brief summary of what the new component is, and a template tag that matches the `name` you set in the previous step.

Then in the `allFieldsBlock` block object, under the `fields` property, add the variable name that you created to hold the field data in the previous step.

In the `allFieldsBlockProps` object, under the `attributes` property, add a key with the `name` of the field along with a valid value for the field.

### 3. Update createBlockEditComponent().
In `createBlockEditComponent.js`, add the dependency to the new component at the top of the file. Then in the `renderField()` function, find the switch statement that has a case for each of our supported controls. Add a new case for the new control name. It would also be good to create a scope for this case, as the others are here. You'll then render the component.

Any type of component here should take a `key` prop which is set to the `name` and an `onChange` prop set to `changeHandler` - these are already provided in the function.

In the test file for `createBlockEditComponent()`, find the existing field tests and add one to check that the field has successfully been rendered.

### 4. Update renderBlockWithValues().
These steps are similar to `createBlockEditComponent()`, but result in the value being rendered instead of the field.

In the `renderField()` function, find the switch statement that has a case for each of our supported controls. Add a new case for the new control name. It would also be good to create a scope for this case, as the others are here. You'll then render the component.

The component being rendered here will vary depending on the control, but any HTML element being returned here should have a `key` property set to the name, as well as a `className` in the convention of `"field--${ typeOfField }"`.
