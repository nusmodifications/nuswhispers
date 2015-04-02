angular.module('nuswhispersApp.services')
.factory('Tag', function ($http) {
    'use strict';
    return {
        getTop: function (n) {
            return $http.get('/api/tags/top/' + n);
        }
    };
});
