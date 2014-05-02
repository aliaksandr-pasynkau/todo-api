'use strict';

var response = {
	'id:decimal': null,
	'username:string': null,
	'email:string': null
};

module.exports = {

	controller: 'SessionUserController',

	'.getOne': {
		routes: [
			'get /session/user/'
		],
		response: {
			statuses: [200, 404],
			data: response
		}
	},

	'.createOne': {
		routes: [
			'post /session/user/'
		],
		request: {
			body: {
				'username:string{3,50}': 'required',
				'password:string{6,50}': 'required',
				'remember:boolean': 'optional'
			}
		},
		response: {
			statuses: [201, 400],
			data: response
		}
	},

	'.deleteOne': {
		routes: [
			'delete /session/user/'
		],
		response: {
			statuses: [200, 410]
		}
	}

//	'.test': {
//		response: {
//
//		},
//		request: {
//			'username:string{3,50}': 'required',
//			'password:string{6,50}': 'required',
//			'data:object': {
//				'username:string{3,50}': 'required',
//				'password:string{6,50}': 'required',
//				'data:array': {
//					'username:string{3,50}': 'required',
//					'password:string{6,50}': 'required',
//					'data:object': {
//						'username:string{3,50}': 'required',
//						'password:string{6,50}': 'required'
//					}
//				}
//			}
//		}
//	}

};