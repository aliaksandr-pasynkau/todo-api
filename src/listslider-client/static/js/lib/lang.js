define(function(require, exports, module){
    'use strict';

	var request = require('lib/request');
	var DEFAULT_LANG = 'ru';

	var lang;
	location.pathname.replace(/^\/(en|ru)\//, function ($0, $1) {
		lang = $1;
	});

	if (!lang) {
		location.href = '/' + DEFAULT_LANG + '/' + location.pathname.replace(/^\/+/, '');
	}

	var translates = {};
	request.load('/var/lang/' + lang + '.json', 'self', true).then(function (json) {
		translates = json;
	});

    return {
		lang: lang,
		translates: translates
	};
});