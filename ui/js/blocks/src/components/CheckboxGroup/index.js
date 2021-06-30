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

const CheckboxGroup = ( {
	id,
	className,
	heading,
	help,
	options,
	values,
	onChange,
} ) => {
	// Update the CheckboxGroup's value whenever an individual checkbox has
	// been changed.
	const handleCheckboxControlChange = ( value, checked ) => {
		const updatedValues = [ ...values ];

		const updatedIndex = updatedValues.findIndex( ( field ) => field.value === value );

		if ( -1 !== updatedIndex ) {
			updatedValues[ updatedIndex ].checked = checked;
		} else {
			updatedValues.push( {
				value,
				checked,
			} );
		}

		onChange( updatedValues );
	};

	return (
		<fieldset className={ classNames( 'components-block-fields-checkbox-group', className ) }>
			{ heading && <legend>{ heading }</legend> }

			{ options.map( ( option ) => {
				const matchingValue = values.find( ( value ) => value.value === option.value ) || false;

				return (
					<CheckboxControl
						key={ option.value }
						label={ option.label }
						checked={ matchingValue.checked || false }
						onChange={ ( newChecked ) => handleCheckboxControlChange( option.value, newChecked ) }
					/>
				);
			} ) }

			{ !! help && (
				<p
					id={ id + '__help' }
					className="components-block-fields-checkbox-group__help"
				>
					{ help }
				</p>
			) }
		</fieldset>
	);
};

CheckboxGroup.propTypes = {
	id: PropTypes.string,
	className: PropTypes.string,
	heading: PropTypes.string,
	help: PropTypes.string,
	options: PropTypes.arrayOf(
		PropTypes.shape( {
			label: PropTypes.string.isRequired,
			value: PropTypes.string.isRequired,
		} )
	),
	values: PropTypes.arrayOf(
		PropTypes.shape( {
			value: PropTypes.string.isRequired,
			checked: PropTypes.bool,
		} )
	),
	onChange: PropTypes.func.isRequired,
};

CheckboxGroup.defaultProps = {
	id: '',
	className: null,
	heading: null,
	help: null,
	options: [],
	values: [],
};

export default CheckboxGroup;
