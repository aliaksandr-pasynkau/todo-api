define(function(require, exports, module){
    'use strict';

	var Session = require('lib/session');
    var BaseController = require('controllers/base/controller');
    var PageHomeView = require('views/modules/static/home');
    var PageAboutView = require('views/modules/static/about');

	var StaticController = BaseController.extend({

		home: function () {
			if (Session.logged()) {
				this.redirectTo('crm-project-list');
			} else {
				this.view = new PageHomeView({
					region: "layout/content"
				});
			}
		},

		about: function(){
			this.view = new PageAboutView({
				region: "layout/content"
			});
		}

	});

    return StaticController;
});