angular.module('nuswhispersApp.controllers')
.controller('ConfessionsController', function ($scope, Confession, FacebookUser, controllerOptions) {
    'use strict';

    $scope.getConfessions = function () {
        function processConfessionResponse(confessions) {
            if (confessions.length === 0) {
                $scope.doLoadMoreConfessions = false;
            } else {
                var confessionModels = [];
                for (var i in confessions) {
                    confessionModels.push(new Confession(confessions[i]));
                }
                $scope.confessions.push.apply($scope.confessions, confessionModels);
                // set up next featured offset
                $scope.offset += $scope.count;
            }
            $scope.loadingConfessions = false;
        }

        $scope.loadingConfessions = true;
        switch (controllerOptions.view) {
            default:
                Confession.getFeatured($scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
        }
    };

    $scope.timestamp = Math.floor(Date.now() / 1000);
    $scope.offset = 0;
    $scope.count = 5;
    $scope.loadingConfessions = false;
    $scope.doLoadMoreConfessions = true;
    $scope.confessions = [];
    $scope.fbUser = FacebookUser;

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

    $scope.favouriteConfession = function (confession) {
        if (FacebookUser.getAccessToken() !== '') {
            confession.favourite().success(function (response) {
                confession.isFavourited = true;
            });
        }
    }
    
});
