const { merge } = require( 'webpack-merge' );
const Terser = require( 'terser-webpack-plugin' );

const common = require( './webpack.common.js' );

module.exports = [
	merge( common[ 0 ], {
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
	} ),
	merge( common[ 1 ], {
		mode: 'production',
	} ),
];
