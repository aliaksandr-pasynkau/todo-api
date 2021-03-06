define(function(require, exports, module){
    'use strict';

	var BaseModel = require('models/base/model');

	return BaseModel.extend({

		modelName: 'user',

		defaults: {
//			id: null,
//			password: '',
			username: '',
			dateRegister: null,
			email: '',
			activated: false
		},

		idAttribute: "id"

	});
});