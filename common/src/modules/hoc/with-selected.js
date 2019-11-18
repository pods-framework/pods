/**
 * External dependencies
 */
import React, { Component } from 'react';
import PropTypes from 'prop-types';
import {
	noop,
} from 'lodash';

/**
 * Higher order component that executes two functions:
 *
 * - `onBlockFocus` when the block is selected
 * - `onBlockBlur` when the block losses focus after being selected
 *
 * @returns {function} Return a new HOC
 */
export default () => ( WrappedComponent ) => {
	class WithSelected extends Component {
		static defaultProps = {
			isSelected: false,
			onBlockFocus: noop,
			onBlockBlur: noop,
		};

		static propTypes = {
			onBlockFocus: PropTypes.func,
			onBlockBlur: PropTypes.func,
			isSelected: PropTypes.bool,
		};

		componentDidMount() {
			const { isSelected, onBlockFocus, onBlockBlur } = this.props;
			if ( isSelected ) {
				onBlockFocus();
			} else {
				onBlockBlur();
			}
		}

		componentDidUpdate( prevProps ) {
			const { isSelected, onBlockFocus, onBlockBlur } = this.props;

			if ( prevProps.isSelected === isSelected ) {
				return;
			}

			if ( isSelected ) {
				onBlockFocus();
			} else {
				onBlockBlur();
			}
		}

		render() {
			return <WrappedComponent { ...this.props } />;
		}
	}

	WithSelected.displayName = `WithIsSelected( ${ WrappedComponent.displayName || WrappedComponent.name || 'Component ' }`;

	return WithSelected;
};

