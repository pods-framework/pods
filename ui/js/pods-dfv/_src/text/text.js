import React from 'react';
import PropTypes from 'prop-types';
import { PodsDFVPropTypes } from 'pods-dfv/_src/core/react/pods-dfv-prop-types';


export class PodsDFVText extends React.Component {
	constructor ( props ) {
		super( props );
		this.state = {
			value: this.props.fieldItemData[ 0 ]
		};
	}

	static get propTypes () {
		return {
			...PodsDFVPropTypes,
			fieldConfig: {
				text_max_length: PropTypes.string,
				text_placeholder: PropTypes.string,
				readonly: PropTypes.bool
			}
		}
	}

	onChanged ( event ) {
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
				placeholder={ this.props.fieldConfig.text_placeholder }
				maxLength={ this.props.fieldConfig.text_max_length }
				value={ this.state.value }
				onChange={ this.onChanged.bind( this ) }
				readOnly={ !!this.props.fieldConfig.readonly }
			/>
		);
	}
}
