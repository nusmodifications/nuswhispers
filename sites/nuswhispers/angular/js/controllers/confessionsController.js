angular.module('nuswhispersApp.controllers')
.controller('ConfessionsController', function ($scope, Confession, controllerOptions) {
    'use strict';

    $scope.getFeatured = function () {
        Confession.getFeatured($scope.timestamp, $scope.offset, $scope.count)
            .success(function (response) {
                console.log(JSON.stringify(response.data.confessions));
                $scope.confessions.push.apply($scope.confessions, response.data.confessions);
                // set up next featured offset
                $scope.offset += $scope.count;
            })
            .error(function (response) {
                console.log(response);
            });
    };

    $scope.timestamp = Math.floor(Date.now() / 1000);
    $scope.offset = 0;
    $scope.count = 10;
    $scope.confessions = [];

    switch (controllerOptions.view) {
        default:
            $scope.getFeatured();
    }

    $scope.processConfessionContent = function (content) {
        var splitContentTags = content.split(/(#\w+)/);
        var processedContent = '';
        for (var i in splitContentTags) {
            if (/(#\w+)/.test(splitContentTags[i])) {
                processedContent += '<a href="/#!home">' + splitContentTags[i] + '</a>';
            } else {
                processedContent += splitContentTags[i];
            }
        }
        return processedContent;
    };
});
