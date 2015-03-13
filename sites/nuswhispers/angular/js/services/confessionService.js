appServices.factory('Confession', function ($http) {
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