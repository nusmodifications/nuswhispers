angular.module('nuswhispersApp.services')
.factory('FacebookUser', function ($http) {
    'use strict';

    var data = {
        // @if NODE_ENV = 'development'
        pageID: '1448006645491039',
        // @endif
        // @if NODE_ENV = 'production'
        pageID: '695707917166339',
        // @endif
        accessToken: '',
        userID: ''
    };

    return {
        login: function () {
            return $http({
                method: 'POST',
                url: '/api/fbuser/login/',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                data: $.param({'fb_access_token': data.accessToken})
            });
        },
        logout: function () {
            return $http({
                method: 'POST',
                url: '/api/fbuser/logout/',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            });
        },
        setAccessToken: function (accessToken) {
            data.accessToken = accessToken;
        },
        getAccessToken: function () {
            return data.accessToken;
        },
        setUserID: function (userID) {
            data.userID = userID;
        },
        getUserID: function () {
            return data.userID;
        },
        getPageID: function () {
            return data.pageID;
        },
    };
});
