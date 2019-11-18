/**
 * External Dependencies
 */
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';

/**
 * Internal Dependencies
 */
import { intercept, EVENT_NAMESPACE } from '@moderntribe/common/hoc/with-block-closer';

export default class PreventBlockClose extends PureComponent {
	static propTypes = {
		children: PropTypes.node.isRequired,
	}

	nodeRef = React.createRef();

	componentDidMount() {
		this.node.addEventListener( EVENT_NAMESPACE, intercept );
	}

	componentWillUnmount() {
		this.node.removeEventListener( EVENT_NAMESPACE, intercept );
	}

	get node() {
		return this.nodeRef.current;
	}

	render() {
		return (
			<div ref={ this.nodeRef }>
				{ this.props.children }
			</div>
		);
	}
}
