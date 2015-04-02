angular.module('nuswhispersApp.controllers')
.controller('ConfessionsController', function ($scope, $routeParams, Confession, FacebookUser, controllerOptions) {
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
            case 'recent':
                Confession.getRecent($scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
                break;
            case 'popular':
                Confession.getPopular($scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
                break;
            case 'category':
                Confession.getCategory($routeParams.category, $scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
                break;
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

    $scope.confessionIsFavourited = function (confession) {
        if (FacebookUser.getAccessToken() !== '') {
            var fbUserID = parseInt(FacebookUser.getUserID());
            for (var i in confession.favourites) {
                if (confession.favourites[i].fb_user_id === fbUserID) {
                    return true;
                }
            }
        }
        return false;
    };

    $scope.toggleFavouriteConfession = function (confession) {
        if (FacebookUser.getAccessToken() !== '') {
            if (confession.isFavourited || $scope.confessionIsFavourited(confession)) {
                confession.unfavourite().success(function (response) {
                    if (response.success) {
                        confession.load();
                    }
                });
            } else {
                confession.favourite().success(function (response) {
                    if (response.success) {
                        confession.load();
                    }
                });
            }
        }
    };
    
});
