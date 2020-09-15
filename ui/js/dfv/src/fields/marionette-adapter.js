import React from 'react';
import PropTypes from 'prop-types';

import { PodsDFVFieldModel } from 'dfv/src/core/pods-field-model';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

class MarionetteAdapter extends React.Component {
	componentDidMount() {
		const {
			htmlAttr = {},
			fieldConfig = {},
		} = this.props;

		this.fieldModel = new PodsDFVFieldModel( {
			htmlAttr,
			fieldConfig,
		} );

		this.renderMarionetteComponent();
	}

	componentDidUpdate() {
		if ( this.marionetteComponent ) {
			this.marionetteComponent.destroy();
		}

		this.renderMarionetteComponent();
	}

	componentWillUnmount() {
		this.marionetteComponent.destroy();
	}

	renderMarionetteComponent() {
		const {
			View,
			value,
		} = this.props;

		this.marionetteComponent = new View( {
			model: this.fieldModel,
			fieldItemData: value,
		} );

		this.marionetteComponent.render();

		this.element.appendChild( this.marionetteComponent.el );

		// @todo does this work? What if it's a model not a collection?
		this.marionetteComponent.collection.on( 'all', ( eventName, collection ) => {
			if ( ! [ 'update', 'remove', 'reset' ].includes( eventName ) ) {
				return;
			}

			console.log( 'collection changed', eventName, collection, collection.models );

			this.props.setValue( collection.models || [] );
		} );

		// for debugging
		window.marionetteViews = window.marionetteViews || {};
		window.marionetteViews[ this.props.fieldConfig.name ] = this.marionetteComponent;
	}

	render() {
		const { className } = this.props;

		return (
			<div className="pods-marionette-adapter-wrapper">
				<div
					className={ className }
					ref={ ( element ) => this.element = element }
				/>
			</div>
		);
	}
}

MarionetteAdapter.propTypes = {
	className: PropTypes.string,
	htmlAttr: PropTypes.object,
	fieldConfig: FIELD_PROP_TYPE_SHAPE.isRequired,
	setValue: PropTypes.func.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.array,
		PropTypes.object,
	] ),
	View: PropTypes.func.isRequired,
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

	render() {
		if ( this.state.hasError ) {
			return (
				<div>There was an error rendering the field.</div>
			);
		}

		return (
			<MarionetteAdapter { ...this.props } />
		);
	}
}

export default MarionetteAdapterWithErrorBoundary;
