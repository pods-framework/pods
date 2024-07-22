const { merge } = require('webpack-merge');

const common = require('./webpack.common.js');

module.exports = [
	merge( common[ 0 ], {
		mode: 'development',
		devtool: 'eval-source-map',
		watch: true,
	} ),
	merge( common[ 1 ], {
		mode: 'production',
	} ),
];
