angular.module('nuswhispersApp.services')
.factory('Category', function ($http) {
    'use strict';
    return {
        getAll: function () {
            return $http.get('/api/categories');
        }
    };
});
