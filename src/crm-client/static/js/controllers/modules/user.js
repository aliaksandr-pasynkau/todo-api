define(function(require, exports, module){
    'use strict';

	var $ = require('jquery');
    var BaseController = require('controllers/base/controller'),
		UserModel = require('models/user/model'),
		PageUserRegisterView = require('views/modules/user/register'),
		PageUserLoginView = require('views/modules/user/login'),
		PageUserProfileView = require('views/modules/user/profile');

	var UserController = BaseController.extend({

		initialize: function () {
			UserController.__super__.initialize.apply(this, arguments);
			this.listenTo(this.user, 'user:login', function () {
				this.redirectTo('todo-lists');
			});
		},

		login: function () {
			this.view = new PageUserLoginView({
				region: "layout/content"
			});
			this.listenTo(this.view, 'trigger:login', function (data) {
				this.userModel = new UserModel(data.data, {parse: true});
				this.user.login(this.userModel);
				window.location.href = '/';
			});
		},

		logout: function () {
			this.user.logout();
		},

		profile: function(){
			this.view = new PageUserProfileView({
				model: this.user.model(),
				region: "layout/content"
			});
		},

		register: function(){
			this.view = new PageUserRegisterView({region: "layout/content"});
			this.listenTo(this.view, 'registered', function(data){
				this.redirectTo('user-login');
			});
		}

	});

    return UserController;
});