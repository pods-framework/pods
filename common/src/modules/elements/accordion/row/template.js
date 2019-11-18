/**
 * External dependencies
 */
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import Button from '@moderntribe/common/elements/button/element';
import { slide } from '@moderntribe/common/utils';

class Row extends PureComponent {
	static propTypes = {
		accordionId: PropTypes.string.isRequired,
		content: PropTypes.node,
		contentAttrs: PropTypes.object,
		contentClassName: PropTypes.string,
		header: PropTypes.node,
		headerAttrs: PropTypes.object,
		headerClassName: PropTypes.string,
		onClick: PropTypes.func,
		onClose: PropTypes.func,
		onOpen: PropTypes.func,
	};

	static defaultProps = {
		contentAttrs: {},
		headerAttrs: {},
	};

	constructor( props ) {
		super( props );
		this.state = {
			isActive: false,
		};
		this.headerId = `accordion-header-${ this.props.accordionId }`;
		this.contentId = `accordion-content-${ this.props.accordionId }`;
	}

	getHeaderAttrs = () => {
		const _isActive = this.state.isActive ? 'true' : 'false';
		return {
			'aria-controls': this.contentId,
			'aria-expanded': _isActive,
			'aria-selected': _isActive,
			id: this.headerId,
			role: 'tab',
			...this.props.headerAttrs,
		};
	};

	getContentAttrs = () => ( {
		'aria-hidden': this.state.isActive ? 'false' : 'true',
		'aria-labelledby': this.headerId,
		id: this.contentId,
		role: 'tabpanel',
		...this.props.contentAttrs,
	} );

	onClose = ( parent, e ) => () => {
		parent.classList.remove( 'closing' );
		parent.classList.add( 'closed' );
		this.props.onClose && this.props.onClose( e );
	};

	onOpen = ( parent, e ) => () => {
		parent.classList.remove( 'opening' );
		parent.classList.add( 'open' );
		this.props.onOpen && this.props.onOpen( e );
	};

	onClick = ( e ) => {
		const { contentId, onClick } = this.props;
		const parent = e.currentTarget.parentNode;
		const content = e.currentTarget.nextElementSibling;

		this.state.isActive
			? parent.classList.add( 'closing' )
			: parent.classList.add( 'opening' );
		this.state.isActive
			? slide.up( content, contentId, 200, this.onClose( parent, e ) )
			: slide.down( content, contentId, 200, this.onOpen( parent, e ) );

		onClick && onClick( e );
		this.setState( ( state ) => ( { isActive: ! state.isActive } ) );
	};

	render() {
		const {
			content,
			contentClassName,
			header,
			headerClassName,
		} = this.props;

		return (
			<article
				className={ classNames(
					'tribe-editor__accordion__row',
					{ active: this.state.isActive },
				) }
			>
				<Button
					className={ classNames(
						'tribe-editor__accordion__row-header',
						headerClassName,
					) }
					onClick={ ( e ) => this.onClick( e ) }
					{ ...this.getHeaderAttrs() }
				>
					{ header }
				</Button>
				<div
					className={ classNames(
						'tribe-editor__accordion__row-content',
						contentClassName,
					) }
					{ ...this.getContentAttrs() }
				>
					{ content }
				</div>
			</article>
		);
	}
}

export default Row;
