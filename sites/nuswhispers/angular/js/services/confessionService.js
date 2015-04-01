angular.module('nuswhispersApp.services')
.factory('Confession', function ($http) {
    'use strict';

    function Confession(confessionData) {
        if (confessionData) {
            this.setData(confessionData);
        }
    }

    Confession.submit = function (confessionData) {
        return $http({
            method: 'POST',
            url: '/api/confessions',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data: $.param(confessionData)
        });
    };

    Confession.getFeatured = function (timestamp, offset, count) {
        return $http({
            method: 'GET',
            url: '/api/confessions',
            params: {timestamp: timestamp, offset: offset, count: count}
        });
    };

    Confession.prototype = {
        setData: function (confessionData) {
            angular.extend(this, confessionData);
        },
        favourite: function () {
            return $http({
                method: 'POST',
                url: '/api/fbuser/favourite',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                data: $.param({'confession_id': this.confession_id})
            });
        }
    };

    return Confession;
});
