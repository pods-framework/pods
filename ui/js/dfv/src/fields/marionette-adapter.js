import React from 'react';
import PropTypes from 'prop-types';

import { PodsDFVFieldModel } from 'dfv/src/core/pods-field-model';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

class MarionetteAdapter extends React.Component {
	componentDidMount() {
		this.fieldModel = new PodsDFVFieldModel( {
			htmlAttr: this.props.htmlAttr || {},
			fieldConfig: this.props.fieldConfig || {},
		} );

		this.renderMarionetteComponent();
	}

	componentDidUpdate() {
		this.renderMarionetteComponent();
	}

	componentWillUpdate() {
		this.marionetteComponent.destroy();
	}

	componentWillUnmount() {
		this.marionetteComponent.destroy();
	}

	renderMarionetteComponent() {
		const { View } = this.props;

		this.marionetteComponent = new View( {
			model: this.fieldModel,
			// fieldItemData: this.props.data.fieldItemData,
		} );
		this.marionetteComponent.render();
		this.element.appendChild( this.marionetteComponent.el );
	}

	render() {
		return (
			<div
				className={this.props.className}
				ref={(element) => { this.element = element; }}
			/>
		);
	}
}

MarionetteAdapter.propTypes = {
	className: PropTypes.string,
	htmlAttr: PropTypes.object,
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	View: PropTypes.func,
};

// Add an error boundary, because this may not be completely
// reliable and we don't want the whole page to crash.
class MarionetteAdapterWithErrorBoundary extends React.Component {
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
		console.warn( 'Error rendering Marionette component', error, errorInfo );
	}

	render() {
		if ( this.state.hasError ) {
			return (
				<div>There was an error rendering the field.</div>
			);
		}

		return (
			<MarionetteAdapter {...this.props} />
		);
	}
  }

export default MarionetteAdapterWithErrorBoundary;
