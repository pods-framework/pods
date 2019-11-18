/* eslint-disable max-len */
/**
 * External dependencies
 */
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { noop } from 'lodash';

const ESCAPE_KEY = 27;
export const EVENT_NAMESPACE = 'tribe:click:proxy';
export const dispatch = ( e ) => {
	e.target.dispatchEvent( new CustomEvent( EVENT_NAMESPACE, { bubbles: true } ) );
};
export const intercept = e => e.stopPropagation();

export default ( WrappedComponent ) => {
	/**
	 * Prevents clicks on block or blacklisted DOM elements
	 * from closing the block
	 *
	 * @class WithBlockCloser
	 * @extends {PureComponent}
	 */
	class WithBlockCloser extends PureComponent {
		static displayName = `WithBlockCloser( ${ WrappedComponent.displayName || WrappedComponent.name || 'Component ' }`

		static propTypes = {
			onClose: PropTypes.func.isRequired,
			classNameClickBlacklist: PropTypes.arrayOf( PropTypes.string ).isRequired,
			isOpen: PropTypes.bool.isRequired,
		};

		static defaultProps = {
			classNameClickBlacklist: [ '.edit-post-sidebar' ],
			onClose: noop,
			isOpen: false,
		}

		nodeRef = React.createRef();
		_eventNamespace = EVENT_NAMESPACE;

		/**
		 * dispatches custom events
		 *
		 * @memberof WithBlockCloser
		 * @param {Event} e event
		 */
		_dispatchClickProxyEvent = dispatch;

		// Prevent CustomEvents from propagating to document proxy listeners
		_interceptClickProxyEvent = intercept;

		/**
		 * keydown handler
		 *
		 * @memberof WithBlockCloser
		 * @param {Event} e event
		 */
		handleKeyDown = ( e ) => {
			if ( e.keyCode === ESCAPE_KEY ) {
				this.props.onClose();
			}
		}

		handleClick = () => this.props.onClose()

		componentDidMount() {
			this.props.isOpen && this._addEventListeners();
		}

		componentDidUpdate( prevProps ) {
			if ( prevProps.isOpen !== this.props.isOpen ) {
				this.props.isOpen
					? this._addEventListeners()
					: this._removeEventListeners();
			}
		}

		componentWillUnmount() {
			this._removeEventListeners();
		}

		get blacklistedNodes() {
			const classNames = this.props.classNameClickBlacklist.join( ', ' );
			return Array.from( document.querySelectorAll( classNames ) );
		}

		get node() {
			return this.nodeRef.current;
		}

		_addEventListeners() {
			// Intercept custom events bubbled in block or blacklisted nodes
			this.node.addEventListener( this._eventNamespace, this._interceptClickProxyEvent );
			this.blacklistedNodes.forEach(
				node => node.addEventListener( this._eventNamespace, this._interceptClickProxyEvent )
			);

			// Wait to receive custom events, if not intercepted, then go to click handler
			document.addEventListener( this._eventNamespace, this.handleClick );
			// Dispatch custom event on regular clicks
			document.addEventListener( 'click', this._dispatchClickProxyEvent );

			// Close on certain keypresses
			document.addEventListener( 'keydown', this.handleKeyDown );
		}

		_removeEventListeners() {
			this.node.removeEventListener( this._eventNamespace, this._interceptClickProxyEvent );
			this.blacklistedNodes.forEach(
				node => node.removeEventListener( this._eventNamespace, this._interceptClickProxyEvent )
			);

			document.removeEventListener( 'keydown', this.handleKeyDown );
			document.removeEventListener( this._eventNamespace, this.handleClick );
			document.removeEventListener( 'click', this._dispatchClickProxyEvent );
		}

		render() {
			return (
				<div ref={ this.nodeRef }>
					<WrappedComponent { ...this.props } />
				</div>
			);
		}
	}

	return WithBlockCloser;
};
