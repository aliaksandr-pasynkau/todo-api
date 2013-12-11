requirejs.config({

	// The path where your JavaScripts are located
	baseUrl: '/temp/js/',

	// Specify the paths of vendor libraries
	paths: {
		jquery: '../bower_components/jquery/jquery',
		underscore: '../bower_components/lodash/dist/lodash',
		backbone: '../bower_components/backbone/backbone',
		handlebars: '../bower_components/handlebars/handlebars',
		text: '../bower_components/requirejs-text/text',
		chaplin: '../bower_components/chaplin/chaplin'
	},

	// Underscore and Backbone are not AMD-capable per default,
	// so we need to use the AMD wrapping of RequireJS
	shim: {
		underscore: {
			exports: '_'
		},
		backbone: {
			deps: [
				'underscore',
				'jquery'
			],
			exports: 'Backbone'
		},
		handlebars: {
			exports: 'Handlebars'
		}
	}

	// For easier development, disable browser caching
	// Of course, this should be removed in a production environment
	//, urlArgs: 'bust=' +  (new Date()).getTime()
});