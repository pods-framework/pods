const { merge } = require( 'webpack-merge' );
const Terser = require( 'terser-webpack-plugin' );

const common = require( './webpack.common.js' );

module.exports = merge( common, {
	mode: 'production',

	optimization: {
		minimize: true,
		minimizer: [
			new Terser( {
				parallel: true,
				extractComments: false,
				terserOptions: {
					sourceMap: false,
					format: {
						comments: false,
					},
				},
			} ),
		],
	},
} );
