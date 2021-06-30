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
	className,
	heading,
	label,
	help,
	checked,
	onChange,
} ) => {
	return (
		<fieldset className={ classNames( 'components-block-fields-checkbox-control', className ) }>
			{ heading && <legend>{ heading }</legend> }
			<CheckboxControl
				label={ label }
				help={ help }
				checked={ checked }
				onChange={ onChange }
			/>
		</fieldset>
	);
};

CheckboxControlExtended.propTypes = {
	className: PropTypes.string,
	heading: PropTypes.string,
	label: PropTypes.string,
	help: PropTypes.string,
	checked: PropTypes.bool,
	onChange: PropTypes.func.isRequired,
};

CheckboxControlExtended.defaultProps = {
	className: null,
	heading: null,
	label: null,
	help: null,
	checked: false,
};

export default CheckboxControlExtended;
