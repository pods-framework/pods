import React, { useState, useEffect, useRef } from 'react';
// import CodeMirror from 'rodemirror';
import { basicSetup } from '@codemirror/basic-setup';
import { EditorState, Compartment } from '@codemirror/state';
import { EditorView } from '@codemirror/view';
import { php } from '@codemirror/lang-php';

import PropTypes from 'prop-types';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './code.scss';

const Code = ( {
	fieldConfig,
	setValue,
	value,
	setHasBlurred,
} ) => {
	const { name } = fieldConfig;

	const editorViewRef = useRef();
	const editorRef = useRef();

	const [ localValue, setLocalValue ] = useState( value );

	useEffect( () => {
		if ( ! editorRef.current ) {
			return;
		}

		const state = EditorState.create( {
			doc: localValue,
			extensions: [
				// Basic setup for CodeMirror.
				// @see https://codemirror.net/6/docs/ref/#basic-setup
				basicSetup,
				// Set the language to PHP.
				php(),
				// Set the tab size to 4.
				( new Compartment ).of( EditorState.tabSize.of( 4 ) ),
				// Handle updates and focus changes.
				EditorView.updateListener.of( ( viewUpdate ) => {
					if ( viewUpdate.docChanged ) {
						const stringValue = viewUpdate.state.doc.toString();
						console.log( 'docChanged', stringValue );

						setLocalValue( stringValue );
						setValue( stringValue );
						return;
					}

					if ( viewUpdate.focusChanged && ! viewUpdate.view.hasFocus ) {
						setHasBlurred();
					}
				} ),
			],
		} );

		editorViewRef.current = new EditorView( {
			state,
			parent: editorRef.current,
		} );

		return () => {
			editorViewRef.current.destroy();
		};
	}, [ editorRef.current ] );

	// Handle receiving new a new value prop.
	useEffect( () => {
		if ( value !== localValue ) {
			console.log( 'value was changed', localValue, value );

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
	}, [ value ] );

	return (
		<div className="pods-code-field">
			<input
				name={ name }
				type="hidden"
				value={ value || '' }
			/>

			<div ref={ editorRef } />
		</div>
	);
};

Code.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Code;
