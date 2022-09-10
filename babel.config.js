module.exports = {
	"presets": [
		[ "@babel/preset-env" ],
		[ "@babel/preset-react" ]
	],
	"plugins": [
		[
			"babel-plugin-module-resolver",
			{
				"alias": {
					"dfv": "./ui/js/dfv"
				}
			}
		],
		"@babel/transform-runtime",
		"babel-plugin-transform-html-import-to-string",
		"@babel/plugin-transform-strict-mode",
		"@babel/plugin-proposal-optional-chaining"
	],
	"env": {
		"development": {
			"presets": [
				[ "@babel/preset-react", { "development": true } ]
			]
		},
		"production": {
			"plugins": [
				"transform-react-remove-prop-types"
			]
		}
	}
}
