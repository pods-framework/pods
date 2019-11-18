/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import { RadioInput } from '@moderntribe/common/elements';

const Radio = ( { checked, className, disabled, id, label, onChange, name, value } ) => (
	<div className={ classNames( 'tribe-editor__radio', className ) }>
		<RadioInput
			checked={ checked }
			className="tribe-editor__radio__input"
			disabled={ disabled }
			id={ id }
			name={ name }
			onChange={ onChange }
			value={ value }
		/>
		<label
			className="tribe-editor__radio_label"
			htmlFor={ id }
		>
			{ label }
		</label>
	</div>
);

Radio.defaultProps = {
	checked: false,
	onChange: noop,
};

Radio.propTypes = {
	checked: PropTypes.bool.isRequired,
	className: PropTypes.string,
	disabled: PropTypes.bool,
	id: PropTypes.string,
	label: PropTypes.string,
	name: PropTypes.string,
	onChange: PropTypes.func,
	value: PropTypes.string,
};

export default Radio;
