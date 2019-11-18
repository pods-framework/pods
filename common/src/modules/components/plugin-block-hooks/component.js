/**
 * External dependencies
 */
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { map, reduce, includes, isArray } from 'lodash';
import { InnerBlocks } from '@wordpress/editor';
import { select } from '@wordpress/data';
import './style.pcss';

/**
 * Allows for dynamic plugin templates based on current plugins available
 * utilizing InnerBlocks api
 *
 * @export
 * @class PluginBlockHooks
 * @extends {PureComponent}
 */
export default class PluginBlockHooks extends PureComponent {
	static propTypes = {
		allowedBlocks: PropTypes.arrayOf( PropTypes.string ),
		layouts: PropTypes.oneOfType( [
			PropTypes.object,
			PropTypes.arrayOf( PropTypes.object ),
		] ),
		/**
		 * Plugins to be used
		*/
		plugins: PropTypes.arrayOf( PropTypes.string ).isRequired,
		/**
		 * Plugin template structure needed to properly
		 * register new templates for each plugin
		 *
		 *
		 * ```js
		 * {
		 *		'events': [
		 *			[ 'tribe/event-datetime', {}],
		 * 		],
		 *		'events-pro': [
		 *  		[ 'tribe/event-pro-recurring', {}],
		 *			[ 'tribe/event-pro-exclusion', {}],
		 *		],
		 *		'events-cool': [
		 *	 		[ 'tribe/event-cool-container', {}, [
		 *	  			[ 'tribe/event-cool-column', {}],
		 *				[ 'tribe/event-cool-column', {}],
		 *			]]
		 *		],
		 *	}
		 *	```
		 */
		pluginTemplates: PropTypes.objectOf( PropTypes.arrayOf( PropTypes.array ) ),
		templateInsertUpdatesSelection: PropTypes.bool.isRequired,
		templateLock: PropTypes.oneOf( [
			'all',
			'insert',
			false,
		] ),
	}

	static defaultProps = {
		templateInsertUpdatesSelection: false,
	}

	/**
	 * Registered block names from core
	 *
	 * @readonly
	 * @memberof PluginBlockHooks
	 * @returns {Array} block names
	 */
	get registeredBlockNames() {
		const blockTypes = select( 'core/blocks' ).getBlockTypes();
		return map( blockTypes, block => block.name );
	}

	/**
	 * Template for InnerBlocks
	 *
	 * @readonly
	 * @memberof PluginBlockHooks
	 * @returns {Array} template
	 */
	get template() {
		const blockNames = this.registeredBlockNames;
		return this.props.plugins.reduce( ( acc, plugin ) => {
			const pluginTemplate = this.props.pluginTemplates[ plugin ];
			if ( pluginTemplate ) {
				// Block needs to be registered, otherwise it's dropped
				const blockTemplates = this.filterPluginTemplates( blockNames, pluginTemplate );
				return [
					...acc,
					...blockTemplates,
				];
			}
			return acc;
		}, [] );
	}

	/**
	 *	Recursively filters out unregistered blocks
	 *
	 * @param {Array} blockNames block names currently registered
	 * @param {Array} pluginTemplate Template for plugins
	 * @returns {Array} Array of plugin template
	 */
	filterPluginTemplates( blockNames, pluginTemplate ) {
		return reduce( pluginTemplate, ( acc, [ name, attributes, nestedBlockTemplates ] ) => {
			if ( includes( blockNames, name ) ) {
				const blockTemplate = isArray( nestedBlockTemplates )
					? [ name, attributes, /* Recursive call */ this.filterPluginTemplates( blockNames, nestedBlockTemplates ) ] // eslint-disable-line max-len
					: [ name, attributes ];

				return [
					...acc,
					blockTemplate,
				];
			}

			return acc;
		}, [] );
	}

	render() {
		return (
			<div className="tribe-common__plugin-block-hook">
				<InnerBlocks
					allowedBlocks={ this.props.allowedBlocks }
					layouts={ this.props.layouts }
					template={ this.template }
					templateInsertUpdatesSelection={ this.props.templateInsertUpdatesSelection }
					templateLock={ this.props.templateLock }
				/>
			</div>
		);
	}
}
