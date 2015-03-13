appServices.factory('Category', function ($http) {
	return {
		get: function () {
			return $http.get('/api/categories');
		}
	};
});