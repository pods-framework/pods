/**
 * External dependencies
 */
import React from 'react';
import { omit } from 'lodash';
import PropTypes from 'prop-types';

/**
 * Pods components
 */
import HelpTooltip from 'dfv/src/components/help-tooltip';

/**
 * Other Pods dependencies
 */
import useDependencyCheck from 'dfv/src/hooks/useDependencyCheck';
import { FIELD_PROP_TYPE } from 'dfv/src/config/prop-types';

const BooleanGroupSubfield = ( {
	subfieldConfig,
	checked,
	toggleChange,
	allPodValues,
	allPodFieldsMap,
} ) => {
	const {
		htmlAttr: htmlAttributes = {},
		help,
		label,
		name,
		'depends-on': dependsOn,
		'depends-on-any': dependsOnAny,
		'excludes-on': excludesOn,
		'wildcard-on': wildcardOn,
	} = subfieldConfig;

	const meetsDependencies = useDependencyCheck(
		allPodValues,
		allPodFieldsMap,
		dependsOn,
		dependsOnAny,
		excludesOn,
		wildcardOn,
	);

	const idAttribute = !! htmlAttributes.id ? htmlAttributes.id : name;

	if ( ! meetsDependencies ) {
		return null;
	}

	return (
		<li className="pods-boolean-group__option">
			<div className="pods-field pods-boolean">
				<label
					className="pods-form-ui-label pods-checkbox-pick__option__label"
					htmlFor={ idAttribute }
				>
					<input
						name={ name }
						id={ idAttribute }
						className="pods-form-ui-field-type-pick"
						type="checkbox"
						value={ 1 }
						checked={ checked }
						onChange={ toggleChange }
					/>
					{ label }
				</label>

				{ help && <HelpTooltip helpText={ help } /> }
			</div>
		</li>
	);
};

BooleanGroupSubfield.propTypes = {
	/**
	 * Field config for the subfield being rendered.
	 */
	subfieldConfig: PropTypes.exact(
		omit( FIELD_PROP_TYPE, [ 'id' ] )
	),

	/**
	 * True if checked.
	 */
	checked: PropTypes.bool.isRequired,

	/**
	 * Handles the value change.
	 */
	toggleChange: PropTypes.func.isRequired,

	/**
	 * All field values for the Pod to use for
	 * validating dependencies.
	 */
	allPodValues: PropTypes.object.isRequired,

	/**
	 * All fields from the Pod, including ones that belong to other groups. This
	 * should be a Map object, keyed by the field name, to make lookup easier.
	 */
	allPodFieldsMap: PropTypes.object,
};

BooleanGroupSubfield.defaultProps = {
	help: undefined,
};

export default BooleanGroupSubfield;
