angular.module('nuswhispersApp.services')
.factory('FacebookData', function () {
    'use strict';

    var data = {
        accessToken: '',
        userID: ''
    };

    return {
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
        }
    };
});