/**
 * External dependencies
 */
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { noop, partial, find } from 'lodash';
import classnames from 'classnames';
import {
	Dropdown,
	Dashicon,
} from '@wordpress/components';
import { ScrollTo, ScrollArea } from 'react-scroll-to';

/**
 * Internal dependencies
 */
import { PreventBlockClose } from '@moderntribe/common/components';
import './style.pcss';

export default class Select extends PureComponent {
	static propTypes = {
		options: PropTypes.shape( {
			label: PropTypes.string,
			value: PropTypes.any,
		} ),
		onOptionClick: PropTypes.func.isRequired,
		optionClassName: PropTypes.string,
		isOpen: PropTypes.bool.isRequired,
		value: PropTypes.any,
		className: PropTypes.string,
	}

	static defaultProps = {
		onOptionClick: noop,
		isOpen: true,
		optionClassName: '',
	}

	_onOptionClick = ( onClose, value, e ) => {
		this.props.onOptionClick( value, e );
		onClose();
	}

	get selected() {
		return find( this.props.options, option => option.value === this.props.value );
	}

	get label() {
		const selected = this.selected;
		return selected && selected.label;
	}

	renderOptions = ( onClose ) => (
		this.props.options.map( ( option ) => (
			<button
				className={ classnames(
					'tribe-common-form-select__options__option',
					this.props.optionClassName
				) }
				key={ option.value }
				onClick={ partial( this._onOptionClick, onClose, option.value ) }
				role="menuitem"
				type="button"
				value={ option.value }
			>
				{ option.label }
			</button>
		) )
	)

	renderToggle = ( { onToggle, isOpen } ) => (
		<div className="tribe-common-form-select__toggle">
			<button
				type="button"
				aria-expanded={ isOpen }
				onClick={ onToggle }
			>
				<span>{ this.label }</span>
				<Dashicon
					className="btn--icon"
					icon={ isOpen ? 'arrow-up' : 'arrow-down' }
				/>
			</button>
		</div>
	)

	renderContent = ( { onClose } ) => (
		<ScrollTo>
			{ () => (
				<PreventBlockClose>
					<ScrollArea
						role="menu"
						className={ classnames( 'tribe-common-form-select__options' ) }
					>
						{ this.renderOptions( onClose ) }
					</ScrollArea>
				</PreventBlockClose>
			) }
		</ScrollTo>

	);

	render() {
		return (
			<Dropdown
				className={ classnames( 'tribe-common-form-select',
					this.props.className
				) }
				position="bottom center"
				contentClassName="tribe-common-form-select__content"
				renderToggle={ this.renderToggle }
				renderContent={ this.renderContent }
			/>
		);
	}
}
