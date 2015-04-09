angular.module('nuswhispersApp.controllers')
.controller('ConfessionsController', function ($scope, $routeParams, Confession, Facebook, FacebookUser, controllerOptions) {
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
        $scope.view = controllerOptions.view;
        switch (controllerOptions.view) {
            case 'single':
                Confession.getConfessionById($routeParams.confession)
                    .success(function (response) {
                        if (response.success) {
                            processConfessionResponse([response.data.confession]);
                        }
                        $scope.doLoadMoreConfessions = false;
                        $scope.loadingConfessions = false;
                    });
                break;
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
            case 'tag':
                Confession.getTag($routeParams.tag, $scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
                break;
            case 'search':
                Confession.search($routeParams.query, $scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
                break;
            case 'favourites':
                Confession.getFavourites($scope.timestamp, $scope.offset, $scope.count)
                .success(function (response) {
                    if (response.success) {
                        processConfessionResponse(response.data.confessions);
                    } else {
                        $scope.doLoadMoreConfessions = false;
                        $scope.loadingConfessions = false;
                    }
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
        content = escapeHTML(content);
        var splitContentTags = content.split(/(#\w+)/);
        var processedContent = '';
        for (var i in splitContentTags) {
            if (/(#\w+)/.test(splitContentTags[i])) {
                processedContent += '<a href="/#!tag/' + splitContentTags[i].substring(1) + '">' + splitContentTags[i] + '</a>';
            } else {
                processedContent += splitContentTags[i];
            }
        }
        return processedContent;
    };

    $scope.confessionIsFavourited = function (confession) {
        if (FacebookUser.getAccessToken() !== '') {
            var fbUserID = FacebookUser.getUserID();
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
            if ($scope.confessionIsFavourited(confession)) {
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

    $scope.confessionIsLiked = function (confession) {
        if (FacebookUser.getAccessToken() !== '') {
            var fbUserID = FacebookUser.getUserID();
            var fbConfessionLikes = confession.facebook_information.likes.data;
            for (var i in fbConfessionLikes) {
                if (fbConfessionLikes[i].id === fbUserID) {
                    return true;
                }
            }
        }
        return false;
    };

    $scope.shareConfessionFB = function (confessionID) {
        Facebook.ui({
            method: 'share',
            href: 'http://nuswhispers.com/#!/confession/' + confessionID,
        });
    };

    $scope.LikeConfessionFB = function (facebookID) {
        Facebook.api(
            '/'+facebookID+'/likes',
            'post'
        );
    };
    
});
