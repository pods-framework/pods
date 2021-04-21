const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

const path = require( 'path' );

module.exports = {
	entry: {
		'dfv/pods-dfv': './ui/js/dfv/src/pods-dfv.js',
		'blocks/pods-blocks-api': './ui/js/blocks/src/index.js',
	},
	output: {
		path: path.resolve( __dirname, 'ui/js' ),
		filename: '[name].min.js',
	},

	externals: {
		'jquery': 'jQuery',
		'underscore': '_',
		'backbone': 'Backbone',
		'backbone.marionette': 'Marionette',
		'react': 'React',
		'react-dom': 'ReactDOM',
		'lodash': 'lodash',
	},

	resolve: {
		extensions: [ '*', '.js', '.jsx' ],
		modules: [
			'node_modules',
			path.resolve( __dirname, 'ui/js' )
		],
	},

	module: {
		rules: [
			{
				test: /\.(js|jsx)$/,
				exclude: /node_modules/,
				use: [ 'babel-loader' ],
			},
			{
				test: /\.scss$/,
				exclude: /node_modules/,
				use: [
					// creates style nodes from JS strings
					{ loader: 'style-loader' },
					// translates CSS into CommonJS
					{ loader: 'css-loader' },
					// compiles Sass to CSS
					{ loader: 'sass-loader' },
				],
			},
			{
				test: /\.css$/,
				use: [
					// creates style nodes from JS strings
					{ loader: 'style-loader' },
					// translates CSS into CommonJS
					{ loader: 'css-loader' },
				],
			},
		],
	},

	plugins: [
		new DependencyExtractionWebpackPlugin( {
			outputFormat: 'json',
		} ),
	],
};
