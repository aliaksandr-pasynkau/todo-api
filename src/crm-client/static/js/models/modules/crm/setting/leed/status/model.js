define(function(require){
	'use strict';

	var BaseModel = require('models/base/model');

	var CrmSettingLeedTypeModel = BaseModel.extend({

		urlRoot: '/crm/setting/leed/status/',

		idAttribute: "id"

	});

	return CrmSettingLeedTypeModel;

});