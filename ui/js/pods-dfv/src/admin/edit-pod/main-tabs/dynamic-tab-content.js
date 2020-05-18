import React from 'react';
import * as PropTypes from 'prop-types';

// WordPress Dependencies
import { __, sprintf } from '@wordpress/i18n';

// Pod dependencies
import DependentFieldOption from 'pods-dfv/src/components/dependent-field-option';
// import { getOption } from 'backbone.marionette';

const MISSING = __( '[MISSING DEFAULT]', 'pods' );

const DynamicTabContent = ( props ) => {
	const {
		tabOptions,
		getOptionValue,
		setOptionValue,
	} = props;

	const getLabelValue = ( labelFormat, paramOption, paramDefault ) => {
		if ( ! paramOption ) {
			return labelFormat;
		}

		const param = getOptionValue( paramOption ) || paramDefault || MISSING;
		return sprintf( labelFormat, param );
	};

	return tabOptions.map( ( {
		name,
		default: defaultValue,
		type,
		label,
		help,
		'depends-on': dependsOn,
	} ) => (
		<DependentFieldOption
			key={ name }
			fieldType={ type }
			name={ name }
			label={ getLabelValue( label, 'label', defaultValue ) }
			value={ getOptionValue( name ) || defaultValue }
			dependents={ dependsOn }
			helpText={ help }
			getOptionValue={ getOptionValue }
			setOptionValue={ setOptionValue }
		/>
	) );
};

DynamicTabContent.propTypes = {
	tabOptions: PropTypes.arrayOf(
		// @todo break out PropTypes, this can be re-used as a "Field"
		PropTypes.shape( {
			boolean_yes_label: PropTypes.string,
			default: PropTypes.oneOfType( [
				PropTypes.string,
				PropTypes.bool,
				PropTypes.number,
			] ),
			'depends-on': PropTypes.object,
			description: PropTypes.string.isRequired,
			group: PropTypes.string.isRequired,
			help: PropTypes.string.isRequired,
			id: PropTypes.string.isRequired,
			label: PropTypes.string.isRequired,
			name: PropTypes.string.isRequired,
			object_type: PropTypes.string.isRequired,
			parent: PropTypes.string.isRequired,
			storage_type: PropTypes.string.isRequired,
			text_max_length: PropTypes.number,
			type: PropTypes.string.isRequired,
		} )
	).isRequired,
	getOptionValue: PropTypes.func.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

export default DynamicTabContent;
