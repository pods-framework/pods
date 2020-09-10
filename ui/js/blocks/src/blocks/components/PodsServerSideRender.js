/**
 * External dependencies
 */
import { debounce, isEqual } from 'lodash';
import parse from 'html-react-parser';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { Placeholder, Spinner } from '@wordpress/components';
import { InnerBlocks } from '@wordpress/editor';

export function rendererPath( block, attributes = null, urlQueryArgs = {} ) {
	return addQueryArgs( `/wp/v2/block-renderer/${ block }`, {
		context: 'edit',
		...( null !== attributes ? { attributes } : {} ),
		...urlQueryArgs,
	} );
}

export class PodsServerSideRender extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			response: null,
		};
	}

	componentDidMount() {
		this.isStillMounted = true;
		this.fetch( this.props );
		// Only debounce once the initial fetch occurs to ensure that the first
		// renders show data as soon as possible.
		this.fetch = debounce( this.fetch, 500 );
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	componentDidUpdate( prevProps ) {
		if ( ! isEqual( prevProps, this.props ) ) {
			this.fetch( this.props );
		}
	}

	fetch( props ) {
		if ( ! this.isStillMounted ) {
			return;
		}
		if ( null !== this.state.response ) {
			this.setState( { response: null } );
		}
		const {
			block,
			attributes = null,
			httpMethod = 'GET',
			urlQueryArgs = {},
		} = props;

		// If httpMethod is 'POST', send the attributes in the request body instead of the URL.
		// This allows sending a larger attributes object than in a GET request, where the attributes are in the URL.
		const isPostRequest = 'POST' === httpMethod;
		const urlAttributes = isPostRequest ? null : attributes;
		const path = rendererPath( block, urlAttributes, urlQueryArgs );
		const data = isPostRequest ? { attributes } : null;

		// Store the latest fetch request so that when we process it, we can
		// check if it is the current request, to avoid race conditions on slow networks.
		const fetchRequest = ( this.currentFetchRequest = apiFetch( {
			path,
			data,
			method: isPostRequest ? 'POST' : 'GET',
		} )
			.then( ( response ) => {
				if (
					this.isStillMounted &&
				fetchRequest === this.currentFetchRequest &&
				response
				) {
					this.setState( { response: response.rendered } );
				}
			} )
			.catch( ( error ) => {
				if ( this.isStillMounted && fetchRequest === this.currentFetchRequest ) {
					this.setState( {
						response: {
							error: true,
							errorMsg: error.message,
						},
					} );
				}
			} ) );

		return fetchRequest;
	}

	render() {
		const response = this.state.response;
		const {
			className,
			EmptyResponsePlaceholder,
			ErrorResponsePlaceholder,
			LoadingResponsePlaceholder,
		} = this.props;

		if ( response === '' ) {
			return <EmptyResponsePlaceholder response={ response } { ...this.props } />;
		}
		if ( ! response ) {
			return (
				<LoadingResponsePlaceholder response={ response } { ...this.props } />
			);
		}
		if ( response.error ) {
			return (
				<ErrorResponsePlaceholder response={ response } { ...this.props } />
			);
		}

		return parse( response, {
			replace: ( domNode ) => {
				if ( 'innerblocks' === domNode.name ) {
					// Parse JSON-supported properties.
					if ( 'undefined' !== typeof domNode.attribs.template ) {
						domNode.attribs.template = JSON.parse( domNode.attribs.template );
					}
					if ( 'undefined' !== typeof domNode.attribs.allowedBlocks ) {
						domNode.attribs.allowedBlocks = JSON.parse( domNode.attribs.allowedBlocks );
					}
					if ( 'undefined' !== typeof domNode.attribs.templateLock && 'false' === domNode.attribs.templateLock ) {
						domNode.attribs.templateLock = false;
					}

					return <InnerBlocks className={ className } { ...domNode.attribs } />;
				}
			},
		} );
	}
}

PodsServerSideRender.defaultProps = {
	EmptyResponsePlaceholder: ( { className } ) => (
		<Placeholder className={ className }>
			{ __( 'Block rendered as empty.' ) }
		</Placeholder>
	),
	ErrorResponsePlaceholder: ( { response, className } ) => {
		const errorMessage = sprintf(
			// translators: %s: error message describing the problem
			__( 'Error loading block: %s' ),
			response.errorMsg
		);
		return <Placeholder className={ className }>{ errorMessage }</Placeholder>;
	},
	LoadingResponsePlaceholder: ( { className } ) => {
		return (
			<Placeholder className={ className }>
				<Spinner />
			</Placeholder>
		);
	},
};

export default PodsServerSideRender;
