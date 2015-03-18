angular.module('nuswhispersApp.services')
.factory('Category', function ($http) {
    'use strict';
	return {
		get: function () {
			return $http.get('/api/categories');
		}
	};
});
