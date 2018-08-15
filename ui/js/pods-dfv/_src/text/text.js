import React from 'react';
import PropTypes from 'prop-types';

export class Text extends React.Component {
	constructor ( props ) {
		super( props );
		console.log( props );
		this.state = {
			value: this.props.fieldItemData[ 0 ]
		};
	}

	static get propTypes () {
		return {
			fieldType: PropTypes.string,
			fieldConfig: PropTypes.object,
			htmlAttr: PropTypes.object,
			fieldItemData: PropTypes.array
		};
	}

	onValueChanged ( event ) {
		this.setState( { value: event.target.value } );
	};

	render () {
		return (
			<input
				type="text"
				name={ this.props.htmlAttr.name }
				id={ this.props.htmlAttr.id }
				className={ this.props.htmlAttr.class }
				data-name-clean={ this.props.htmlAttr.name_clean }
				placeholder="DFV text box"
				maxLength="255"
				value={ this.state.value }
				onChange={ this.onValueChanged.bind( this ) }
			/>
		);
	}
}
