import React from 'react';
import { Controlled as CodeMirror } from 'react-codemirror2';
import PropTypes from 'prop-types';
import 'codemirror/mode/php/php';

import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';

import 'codemirror/lib/codemirror.css';
import './code.scss';

const Code = ( {
	setValue,
	value,
	setHasBlurred,
} ) => (
	<div className="pods-code-field">
		<CodeMirror
			value={ value }
			options={ {
				lineNumbers: true,
				matchBrackets: true,
				mode: 'php',
				indentUnit: 4,
				indentWithTabs: false,
				lineWrapping: true,
				enterMode: 'keep',
				tabMode: 'shift',
			} }
			onBeforeChange={ ( editor, data, newValue ) => {
				setValue( newValue );
			} }
			onBlur={ () => setHasBlurred() }
		/>
	</div>
);

Code.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	value: PropTypes.string,
};

export default Code;
