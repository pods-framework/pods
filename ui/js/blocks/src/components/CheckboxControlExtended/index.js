/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	CheckboxControl,
} from '@wordpress/components';

const CheckboxControlExtended = ( {
	id,
	className,
	heading,
									  name,
	label,
	help,
	checked,
	onChange,
} ) => {
	return (
		<fieldset className={ classNames( 'components-block-fields-checkbox-control', className ) }>
			{ heading && <legend>{ heading }</legend> }
			<CheckboxControl
				key={ name }
				label={ label }
				help={ help }
				checked={ checked }
				onChange={ onChange }
			/>
		</fieldset>
	);
};

CheckboxControlExtended.propTypes = {
	id: PropTypes.string,
	className: PropTypes.string,
	heading: PropTypes.string,
	name: PropTypes.string,
	label: PropTypes.string,
	help: PropTypes.string,
	checked: PropTypes.bool,
	onChange: PropTypes.func.isRequired,
};

CheckboxControlExtended.defaultProps = {
	id: '',
	className: null,
	heading: null,
	name: '',
	label: null,
	help: null,
	checked: false,
};

export default CheckboxControlExtended;
