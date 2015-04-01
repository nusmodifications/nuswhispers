angular.module('nuswhispersApp.controllers')
.controller('MainController', function ($scope, Facebook, FacebookUser, Category) {
    'use strict';

    $scope.sidebarOpenedClass = '';
    $scope.isLoggedIn = false;

    // Load all categories onto sidebar
    Category.get().success(function (response) {
        $scope.categories = response.data.categories;
    });

    $scope.toggleSidebar = function () {
        if ($scope.sidebarOpenedClass === '') {
            $scope.sidebarOpenedClass = 'sidebar-opened';
        } else {
            $scope.sidebarOpenedClass = '';
        }
    };

    $scope.login = function () {
        Facebook.login(function (response) {
            $scope.getLoginStatus();
        }, {
            // scope: 'publish_actions'
        });
    };

    $scope.logout = function () {
        Facebook.logout(function (response) {
            $scope.getLoginStatus();
        });
    };

    $scope.getLoginStatus = function () {
        Facebook.getLoginStatus(function (response) {
            FacebookUser.setAccessToken('');
            if (response.status === 'connected') {
                FacebookUser.setAccessToken(response.authResponse.accessToken);
                FacebookUser.setUserID(response.authResponse.userID);
                FacebookUser.login()
                    .success(function (response) {
                        $scope.isLoggedIn = true;
                    })
                    .error(function (response) {
                        $scope.isLoggedIn = false;
                    });
            } else {
                $scope.isLoggedIn = false;
            }
        });
    };

    $scope.getLoginStatus();
});