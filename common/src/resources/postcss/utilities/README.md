# Tribe Common Styles

## What is this repository?

This repository is a set of common PostCSS utilities. It includes variables, icons, and mixins to help follow Modern Tribe products design system. They are consumed by Modern Tribe plugins PostCSS files.

## Why do we need this?

Modern Tribe has a design system for its plugins. Defining a set of variables and style groupings helps maintain consistency and ease of maintenance throughout all the plugins.

For example, if the primary text color was to change due to a design update, doing a search and replace through all the PostCSS files could be time-consuming and error-prone. Instead, changing it in one place changes the color for all styles consuming the variable.

## Repository Structure

The repository structure starts at the root level file `_all.pcss` importing all the repository styles. Variables, icons, and mixins are defined within their respective folders.

### Variables

Variables are any *reusable* property values named specifically for their use case. They are found in the `/variables` folder. Though some variables may hold the same value (e.g. `--border-radius-default: 4px;` and `--spacer-0: 4px`), their use cases are very different and should not be used interchangeably.

SVGs are different from the other variables partial files. They are found in `/variables/_svgs.pcss` and are compiled using the [PostCSS Inline SVG](https://github.com/TrySound/postcss-inline-svg) plugin.

### Icons

Icons are SVG icons included in the design system and are found in the `/icons` folder. They are consumed by the SVG variables partial file `/variables/_svg.pcss`.

### Mixins

Mixins are groupings of *reusable* styles and are found in the `/mixins` folder. These reusable style groupings are often defined by the design system. Mixins are compiled using the [PostCSS Mixins](https://github.com/postcss/postcss-mixins) plugin.

## Making Changes

Making changes to this repository should be done with care. As these utilities are the most upstream in Modern Tribe products PostCSS files, modifications to these files can have a cascading effect downstream.

When making any changes, whether they are additions, alterations, or deletions, **consistency** is key. Follow naming conventions and groupings so that viewing and editing the files are simple.

### Additions

Additions are generally safe, as long as the variable names do not conflict with existing variables. Confirm that the variables or mixins you are adding are reusable and a consistent part of the design system before adding.

### Alterations

Alterations should be done carefully, as they will affect all downstream styles using the variable or mixin being altered. Multiple plugins use these styles and should be cross-checked before making the change.

### Deletions

Deletions should also be done carefully, for the same reasons as **Alterations** above. Removing a variable or mixin that is still being used will create broken styles and/or build failures.

## Installation

You will need to include this package as a submodule to your plugin/project. To install this in in a custom path, in this case `src/resources/postcss/utilities`, add the following to your `.gitmodules` file:

```
[submodule "src/resources/postcss/utilities"]
	path = src/resources/postcss/utilities
	url = git@github.com:moderntribe/tribe-common-styles.git
```

To simply install the package in your project use:

```bash
git submodule update --recursive --init
```
