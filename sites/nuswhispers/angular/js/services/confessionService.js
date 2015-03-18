angular.module('nuswhispersApp.services')
.factory('Confession', function ($http) {
    'use strict';
	return {
		submit: function (confessionData) {
			return $http({
				method: 'POST',
				url: '/api/confessions',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				data: $.param(confessionData)
			});
		}
	};
});
