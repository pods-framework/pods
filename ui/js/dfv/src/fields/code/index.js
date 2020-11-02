import React from 'react';
import { Controlled as CodeMirror } from 'react-codemirror2';
import PropTypes from 'prop-types';
import 'codemirror/mode/php/php';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import 'codemirror/lib/codemirror.css';
import './code.scss';

const Code = ( props ) => {
	const {
		setValue,
		value,
	} = props;

	return (
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
			/>
		</div>
	);
};

Code.propTypes = {
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.string,
};

export default Code;
