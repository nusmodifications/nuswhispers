angular.module('nuswhispersApp.services')
.factory('FbUser', function ($http) {
    'use strict';
    return {
        login: function (accessToken) {
            return $http({
                method: 'POST',
                url: '/api/fbuser/login/',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                data: $.param({'fb_access_token': accessToken})
            });
        }
    };
});
