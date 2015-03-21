angular.module('nuswhispersApp.controllers')
.controller('MainController', function ($scope) {
    'use strict';

    $scope.sidebarOpenedClass = '';

    $scope.toggleSidebar = function () {
        if ($scope.sidebarOpenedClass === '') {
            $scope.sidebarOpenedClass = 'sidebar-opened';
        } else {
            $scope.sidebarOpenedClass = '';
        }
    };
});