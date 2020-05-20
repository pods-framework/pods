import React from 'react';
import * as PropTypes from 'prop-types';

// WordPress Dependencies
import { __, sprintf } from '@wordpress/i18n';

// Pod dependencies
import DependentFieldOption from 'pods-dfv/src/components/dependent-field-option';
import { FIELD_PROP_TYPE_SHAPE } from 'pods-dfv/src/prop-types';
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
		PropTypes.shape( FIELD_PROP_TYPE_SHAPE )
	).isRequired,
	getOptionValue: PropTypes.func.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

export default DynamicTabContent;
