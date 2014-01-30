"use strict";

module.exports = function(grunt, options){

	//

	return {
		'api-test': {
			options: {
				excludeEmpty: true
			},
			files: [
				{
					expand: true,
					cwd: global.SRC + '/api-test',
					src: [
						'*.{php,html,htaccess,hbs,js,css,eot,svg,ttf,woff,otf}',
						'**/*.{php,html,htaccess,hbs,js,css,eot,svg,ttf,woff,otf}',
					],
					dest: global.BUILD + '/api-test'
				}
			]
		}
	};

};