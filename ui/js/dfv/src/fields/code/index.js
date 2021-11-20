import React, { useMemo, useState, useEffect } from 'react';
import CodeMirror from 'rodemirror';
import { basicSetup } from '@codemirror/basic-setup';
import { EditorState, Compartment } from '@codemirror/state';
import { php } from '@codemirror/lang-php';

import PropTypes from 'prop-types';

import 'codemirror/mode/php/php';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import './code.scss';

const Code = ( {
	fieldConfig,
	setValue,
	value,
	setHasBlurred,
} ) => {
	const { name } = fieldConfig;

	const extensions = useMemo(
		() => {
			const language = new Compartment;
			const tabSize = new Compartment;

			return [
				basicSetup,
				language.of( php() ),
				tabSize.of( EditorState.tabSize.of( 4 ) ),
			];
		},
		[]
	);

	const [ readValue, setReadValue ] = useState( value );

	return (
		<div className="pods-code-field">
			<input
				name={ name }
				type="hidden"
				value={ value || '' }
			/>

			<CodeMirror
				value={ readValue }
				onUpdate={ ( view ) => {
					if ( view.docChanged ) {
						setReadValue( view.state.doc.toString() );
					}

					if ( view.focusChanged ) {
						console.log( 'blurred code field' );
						setValue( view.state.doc.toString() );
						setHasBlurred();
					}
				} }
				extensions={ extensions }
			/>
		</div>
	);
};

Code.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Code;
