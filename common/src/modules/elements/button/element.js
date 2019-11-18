/**
 * External dependencies
 */
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import './style.pcss';

class Button extends PureComponent {
	static defaultProps = {
		onClick: noop,
		type: 'button',
	};

	static propTypes = {
		className: PropTypes.oneOfType( [
			PropTypes.string,
			PropTypes.arrayOf( PropTypes.string ),
			PropTypes.object,
		] ),
		isDisabled: PropTypes.bool,
		children: PropTypes.node,
		onClick: PropTypes.func,
		type: PropTypes.string,
	};

	render() {
		const {
			children,
			className,
			isDisabled,
			onClick,
			type,
			...rest
		} = this.props;

		return (
			<button
				className={ classNames( 'tribe-editor__button', className ) }
				disabled={ isDisabled }
				type={ type }
				onClick={ onClick }
				{ ...rest }
			>
				{ children }
			</button>
		);
	}
}

export default Button;
