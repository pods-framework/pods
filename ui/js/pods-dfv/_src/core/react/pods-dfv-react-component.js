import React from 'react';
import PropTypes from 'prop-types';

export class PodsDFVReactComponent extends React.Component {

	static get propTypes () {
		return {
			fieldType: PropTypes.string,
			fieldConfig: {
				text_max_length: PropTypes.string,
				text_placeholder: PropTypes.string,
				readonly: PropTypes.bool
			},
			htmlAttr: PropTypes.object,
			fieldItemData: PropTypes.array
		};
	}
}
