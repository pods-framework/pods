/**
 * External dependencies
 */
import React, { useState, useEffect, useRef } from 'react';
import { basicSetup, EditorView } from 'codemirror';
import { EditorState, Compartment } from '@codemirror/state';
import { php } from '@codemirror/lang-php';
import PropTypes from 'prop-types';

/**
 * Other Pods dependencies
 */
import { toBool } from 'dfv/src/helpers/booleans';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './code.scss';

const Code = ( {
	fieldConfig,
	setValue,
	value,
	setHasBlurred,
} ) => {
	const {
		name,
		read_only: readOnly,
	} = fieldConfig;

	const editorViewRef = useRef();
	const editorRef = useRef();

	const [ view, setView ] = useState();

	// NOTE: This callback doesn't quite work properly, possibly due to
	// a scope issue when it gets called inside the EditorView object. I (Zack) haven't
	// found a better way to implement this, but it works for typical fields.
	//
	// The scope of setValue() when it is called here is wrong - if it needs
	// to reference variables outside of it's scope, they may be incorrect. In most
	// use cases, this doesn't cause issues.
	//
	// This causes the Code field to break when editing/swapping values in the
	// RepeatableFieldList component, so the Code field is not repeatable (for now).
	const handleViewUpdate = ( viewUpdate ) => {
		if ( viewUpdate.docChanged ) {
			const stringValue = viewUpdate.state.doc.toString();

			setValue( stringValue );
			return;
		}

		if ( viewUpdate.focusChanged && ! viewUpdate.view.hasFocus ) {
			setHasBlurred();
		}
	};

	useEffect( () => {
		if ( ! editorRef.current ) {
			return;
		}

		const stateCurrent = EditorState.create( {
			doc: value,
			extensions: [
				// Basic setup for CodeMirror.
				// @see https://codemirror.net/6/docs/ref/#basic-setup
				basicSetup,
				// Set the language to PHP.
				php(),
				// Set the tab size to 4.
				( new Compartment ).of( EditorState.tabSize.of( 4 ) ),
				// Handle updates and focus changes.
				EditorView.updateListener.of( handleViewUpdate ),
				EditorState.readOnly.of( toBool( readOnly ) ),
				EditorView.editable.of( ! toBool( readOnly ) ),
			],
		} );

		const viewCurrent = new EditorView( {
			state: stateCurrent,
			parent: editorRef.current,
		} );

		setView( viewCurrent );
	}, [] );

	useEffect( () => {
		return () => {
			if ( view ) {
				view.destroy();
			}
		};
	}, [ view ] );

	// Handle receiving new a new value prop.
	useEffect( () => {
		if ( ! view ) {
			return;
		}

		// Don't do anything if the view hasn't been set up yet.
		if ( ! editorViewRef?.current ) {
			return;
		}

		const currentValue = view.state.doc.toString();

		if ( value !== currentValue ) {
			// Replace the whole editor content.
			editorViewRef.current.dispatch(
				{
					changes: {
						from: 0,
						to: editorViewRef.current.state.doc.length,
						insert: value,
					},
				}
			);
		}
	}, [ value, view ] );

	return (
		<div className="pods-code-field">
			<input
				name={ name }
				type="hidden"
				value={ value || '' }
			/>

			<div
				className={ toBool( readOnly ) ? 'pods-code-field__input--readonly' : '' }
				ref={ editorRef }
			/>
		</div>
	);
};

Code.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Code;
