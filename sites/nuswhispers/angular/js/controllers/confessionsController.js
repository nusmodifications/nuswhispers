angular.module('nuswhispersApp.controllers')
.controller('ConfessionsController', function ($scope, Confession, controllerOptions) {
    'use strict';

    $scope.getConfessions = function () {
        $scope.loadingConfessions = true;
        switch (controllerOptions.view) {
            default:
                Confession.getFeatured($scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        if (response.data.confessions.length === 0) {
                            $scope.doLoadMoreConfessions = false;
                        } else {
                            $scope.confessions.push.apply($scope.confessions, response.data.confessions);
                            // set up next featured offset
                            $scope.offset += $scope.count;
                        }
                        $scope.loadingConfessions = false;
                    });
        }
    }

    $scope.timestamp = Math.floor(Date.now() / 1000);
    $scope.offset = 0;
    $scope.count = 5;
    $scope.loadingConfessions = false;
    $scope.doLoadMoreConfessions = true;
    $scope.confessions = [];

    $scope.getConfessions();

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
