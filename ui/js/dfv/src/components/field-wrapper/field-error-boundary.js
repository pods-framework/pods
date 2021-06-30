/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

class FieldErrorBoundary extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			hasError: false,
			error: null,
		};
	}

	static getDerivedStateFromError( error ) {
		return {
			hasError: true,
			error,
		};
	}

	componentDidCatch( error, errorInfo ) {
		// eslint-disable-next-line no-console
		console.warn(
			'There was an error rendering this field.',
			error,
			errorInfo
		);
	}

	render() {
		if ( this.state.hasError ) {
			return (
				<div>There was an error rendering the field.</div>
			);
		}

		return <>{ this.props.children }</>;
	}
}

FieldErrorBoundary.propTypes = {
	children: PropTypes.element.isRequired,
};

export default FieldErrorBoundary;
