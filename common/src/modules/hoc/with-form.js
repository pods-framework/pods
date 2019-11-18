/**
 * External dependencies
 */
import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import { actions, selectors } from '@moderntribe/common/data/forms';

/**
 * HOC that register a new object associated with set of fields for a form
 *
 * @param {function} getName Function used to set the name of the form, has a props param to generate the name
 * @returns {function(*): *} Returns a function that takes a Component as argument and returns a component.
 */
export default ( getName = noop ) => ( WrappedComponent ) => {
	class WithForm extends Component {
		static propTypes = {
			registerForm: PropTypes.func,
			postType: PropTypes.string,
		};

		componentDidMount() {
			const name = getName( this.props );
			const { registerForm, postType } = this.props;
			registerForm( name, postType );
		}

		render() {
			return <WrappedComponent { ...this.props } { ...this.additionalProps() } />;
		}

		additionalProps() {
			const {
				createDraft,
				sendForm,
				setSubmit,
				editEntry,
				maybeRemoveEntry,
			} = this.props;
			const name = getName( this.props );
			return {
				createDraft: ( fieldsObject ) => createDraft( name, fieldsObject ),
				editEntry: ( fieldsObject ) => editEntry( name, fieldsObject ),
				sendForm: ( fieldsObject, callback ) => sendForm( name, fieldsObject, callback ),
				setSubmit: () => setSubmit( name ),
				maybeRemoveEntry: ( details ) => maybeRemoveEntry( name, details ),
			};
		}
	}

	const mapStateToProps = ( state, props ) => {
		const name = getName( props );
		const modifiedProps = { name };
		return {
			edit: selectors.getFormEdit( state, modifiedProps ),
			create: selectors.getFormCreate( state, modifiedProps ),
			fields: selectors.getFormFields( state, modifiedProps ),
			submit: selectors.getFormSubmit( state, modifiedProps ),
		};
	};

	const mapDispatchToProps = ( dispatch ) => bindActionCreators( actions, dispatch );

	return connect( mapStateToProps, mapDispatchToProps )( WithForm );
};
