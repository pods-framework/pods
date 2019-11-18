# Common PostCSS Styles

## Why common styles?

Historically, CSS for Modern Tribe plugins have not held up to the highest standards for structuring CSS and naming CSS classes. These common styles help to build a foundation for standardizing class naming as well as following the Modern Tribe products design system.

## Class naming consistency and BEM

A couple of issues we've had previously with templates for Modern Tribe plugins was inconsistent class naming and the class naming structure. To deal with this, we've adopted the use of [BEM](http://getbem.com/naming/) for class naming, combined with the use of `tribe-common-` as a block prefix.

First is the use of [BEM](http://getbem.com/naming/) for class naming (see link for more details). BEM stands for Block Element Modifier. We've used BEM as a guide to help us name classes and maintain consistency. This helps us structure the CSS around the HTML that we are styling without running into class naming chaos.

Secondly, we've added prefixes to our classes. The first prefix we've used is `tribe-common-`. This is mainly to avoid styles clashing with other theme styles. For example, if we used a class `h1`, a theme that the user may apply may also use a class `h1` and the theme styles may unintentionally affect the plugin styles. Instead, we use `tribe-common-h1`. The second prefix we've used is context-based prefixes. Some of these prefixes include `a11y-` for accessibility, `g-` for grid, `l-` for layout, and `c-` for component. These prefixes help determine the context of these reusable style classes. For example, the `tribe-common-a11y-hidden` can be applied to hide content from sighted users and screenreaders. The `tribe-common-c-btn` can be applied to a link or button to apply button styles.

## View/block wrapper class

Aside from classes that apply styles to elements, we also apply resets and base styles. In order to not override theme styles and elements outside of Modern Tribe plugins, we've added a wrapper class `tribe-common` around all of Modern Tribe plugins blocks and views. For example, the markup for a specific view or block might look like the following:

```
<div class="tribe-common">
	...
	<button class="tribe-common-c-btn">Test Button</button>
	...
</div>
```

Given this markup, the PostCSS will look like the following:

```
.tribe-common {
	...

	button {
		/* base button styles here */
	}

	...

	.tribe-common-c-btn {
		/* component button styles here */
	}

	...
}
```

This allows us to target only the buttons within the Modern Tribe plugin views.

## CSS specificity

Given the above structure of using a wrapper class, we've increased the [CSS specificity](https://developer.mozilla.org/en-US/docs/Web/CSS/Specificity) needed for theme developers to override our styles. For resets and base styles, the minimum specificity required is 1 class and 1 element. For class-based styles, the minimum specificity required is 2 classes. With some modifiers, the minimum specificity required may be 3 classes. For example:

```
.tribe-common {
	...

	.tribe-common-form-control-toggle--vertical {

		.tribe-common-form-control-toggle__label {
			/* toggle label styles */
		}
	}

	...
}
```

In this case, the label is an element of the toggle. However, the `--vertical` modifier is applied to the top level block. Given this structure, our minimum specificity becomes 3 classes.

For overriding styles, it is recommended to only use classes to keep overriding specificity consistent. All elements should have classes and should be targetted using those classes.

## Modifiers, pseudo-classes, and media queries

As you get into building upon these styles and creating new styles, the order of modifiers, pseudo-classes, and media queries comes into question. The general rule is to apply them in the following order: media queries, pseudo-classes, modifiers. See the examples below:

```
.tribe-common {
	...

	.tribe-common-form-control-toggle {
		/* toggle styles */

		@media (--viewport-medium) {
			/* viewport medium toggle styles */
		}

		&:after {
			/* :after pseudo-class styles */

			@media (--viewport-medium) {
				/* viewport medium :after pseudo-class styles */
			}
		}
	}

	.tribe-common-form-control-toggle--vertical {
		/* vertical toggle styles */

		@media (--viewport-medium) {
			/* viewport medium vertical toggle styles */
		}

		&:after {
			/* :after pseudo-class styles */

			@media (--viewport-medium) {
				/* viewport medium :after pseudo-class styles */
			}
		}
	}

	...
}
```

In the case of an element, we might get the following scenario:

```
.tribe-common {
	...

	.tribe-common-form-control-toggle__input {
		/* toggle input styles */

		@media (--viewport-medium) {
			/* viewport medium toggle input styles */
		}

		&:after {
			/* :after pseudo-class styles */

			@media (--viewport-medium) {
				/* viewport medium :after pseudo-class styles */
			}
		}
	}

	.tribe-common-form-control-toggle--vertical {

		.tribe-common-form-control-toggle__input {
			/* vertical toggle input styles */

			@media (--viewport-medium) {
				/* viewport medium vertical toggle input styles */
			}

			&:after {
				/* :after pseudo-class styles */

				@media (--viewport-medium) {
					/* viewport medium :after pseudo-class styles */
				}
			}
		}
	}

	...
}
```

## Structure of common styles

The common styles are comprised of 2 files: `reset.pcss` and `common.pcss`. The reset styles cover cross-browser style normalizations for Modern Tribe plugins and the common styles cover base styles and common components used throughout the plugins.

The common styles are broken into 5 main sections: reset, utilities, base, a11y, and components.

Reset styles and common styles both have a reset applied to them. This is due to The Events Calendar having 2 style options: skeleton and full. Skeleton is mainly layout-focused, while full is the application of the entire suite of styles from the design system.

### Reset

The reset styles are meant to normalize cross-browser style differences. These are resets for only layout-focused styles.

### Common reset

These reset styles are also meant to normalize cross-browser style differences. However, common reset styles are more style focused, such as color and font.

### Utilities

The utilities are a set of common PostCSS variables, icons, and mixins used throughout the plugins. These come from the Tribe Common Styles repository. See Tribe Common Styles for more details.

### Base

The base styles are base element styles, both on the element target (e.g. `button`) and class target (e.g. `.tribe-common-l-container`). These provide a base on which to build component and block/view styles.

A large portion of the base styles are forms, grid, and typography.

#### Forms

Base form styles are for things such as checkboxes, radios, text inputs, sliders, and toggles. These include a `form-control-` prefix (e.g. `.tribe-common-form-control-checkbox`). The combination of form styles and markup work to match the design system for form elements.

#### Grid

Base grid styles are for layout and grids provided by the design system. Prefixed by `g-` (e.g. `.tribe-common-g-row`), they are a combination of rows and columns to build a consistent grid structure.

#### Typography

Base typography styles are for anything typography-related. These include anchors, body text, call to actions, headings, and lists. For body text, we've used the classes `.tribe-common-b1` to `.tribe-common-b3`. These body text classes are used to mimic the design system body text styles. For headings, we've used the classes `.tribe-common-h1` to `.tribe-common-h8`. These heading classes are also used to mimic the design system heading styles.

There are also classes in body text and heading styles with the `--min-medium` modifier. Each body text and heading class has a style for mobile and desktop (`@media (--viewport-medium)`). However, the designs may not follow the styles exactly for each class upon reaching the `--viewport-medium` breakpoint, but instead use another class style. For this reason, we've added the `--min-medium` modifier for each body text and heading class to apply a different style upon reaching this breakpoint. See example below:

```
<h2 class="tribe-common-h6 tribe-common-h5--min-medium">Test heading</h2>
```

In this case, the heading will use the mobile `.tribe-common-h6` styles and desktop `.tribe-common-h5` styles.

### A11y

Accessibility styles are utility classes for repeatable patterns regarding accessibility. The most common are those concerning visibility and screenreader access to content.

### Components

Components are groups of reusable markup and styles. The component style structure is meant to mirror the markup structure.

### Media queries

These styles use a mobile-first approach. Given this, there are only `min-width:` breakpoints, never `max-width:` breakpoints. This also lends to using the `--min-medium` modifier.

## Theme overrides

Modern Tribe plugins support a handful of themes. Some themes provide stylesheets that have high specificity for elements and override the common styles. To counter this, we've included theme overrides to ensure our plugin styles display as expected with the supported themes.

The specificity to override the styles are matched to those applied to the theme. This means that if, for example, a theme applied an ID and 2 extra classes to a `button` style, we might see the following theme override:

```
.tribe-common {

	/* -------------------------------------------------------------------------
	 * Button: Theme Overrides
	 * ------------------------------------------------------------------------- */

	#id-1 .class-1 .class-2 & {

		button {
			/* button theme override styles */
		}
	}
}
```

### Reset

The reset theme overrides are used to reapply the reset styles that have been overridden by theme styles. These are found in their own partials in the resets folder.

### Common

Common theme overrides, mainly in base and components, are applied to the bottom of each affected file.

## How to contribute

You want to contribute to these styles? Great! There are a couple things to consider when making changes:

1. These styles are the base layer to a number of Modern Tribe plugins. Make changes with care.
2. Consider whether these styles may be reuseable or not. If they are good candidates for a component for more than one plugin, then it's probably a good idea to put them into these styles.

### Additions

Additions are generally safe, as long as the selectors do not conflict with existing selectors. Confirm that the styles you are adding are reuseable and are a consistent part of the design system before adding.

### Alterations

Alterations should be done carefully, as they will affect all downstream styles using the selectors being altered. Multiple plugins use these styles and should be cross-checked before making the change.

### Deletions

Deletions should also be done carefully, for the same reasons as **Alterations** above. Removing a style from a selector that is still being used will result in unintended styles.
