import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import './div-field-layout.scss';

const DivFieldLayout = ( {
	fieldType,
	labelComponent,
	descriptionComponent,
	inputComponent,
	validationMessagesComponent,
} ) => {
	const dfvContainerClass = classnames(
		'pods-dfv-container',
		`pods-dfv-container-${ fieldType }`
	);

	return (
		<div className="pods-field-option">
			{ labelComponent ? labelComponent : undefined }

			<div className="pods-field-option__field">
				<div className={ dfvContainerClass }>
					{ inputComponent }
					{ validationMessagesComponent }
				</div>

				{ descriptionComponent ? descriptionComponent : undefined }
			</div>
		</div>
	);
};

DivFieldLayout.defaultProps = {
	labelComponent: undefined,
	descriptionComponent: undefined,
};

DivFieldLayout.propTypes = {
	fieldType: PropTypes.string.isRequired,
	labelComponent: PropTypes.element,
	descriptionComponent: PropTypes.element,
	inputComponent: PropTypes.element.isRequired,
	validationMessagesComponent: PropTypes.element,
};

export default DivFieldLayout;
