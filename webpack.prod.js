const webpack = require( 'webpack' );
const merge = require( 'webpack-merge' );
const Terser = require( 'terser-webpack-plugin' );

const common = require( './webpack.common.js' );

module.exports = merge( common, {
	mode: 'production',

	optimization: {
		minimizer: [
			new Terser( {
				cache: true,
				parallel: true,
				sourceMap: true, // Must be set to true if using source-maps in production
				terserOptions: {
					output: {
						comments: false,
					}
				}
			} ),
		]
	},
} );
