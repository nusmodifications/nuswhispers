angular.module('nuswhispersApp.controllers')
.controller('SubmitController', function ($scope, $http, Confession, Category, localStorageService, vcRecaptchaService) {
    'use strict';

    // functions for controlling confession submission limit
    function getConfessionLimit() {
        var doResetLimit = !localStorageService.get('confessionLimit.date') ||
            localStorageService.get('confessionLimit.date') !== (new Date()).toDateString();
        if (doResetLimit) {
            localStorageService.set('confessionLimit.count', 3);
            localStorageService.set('confessionLimit.date', (new Date()).toDateString());
        }
        return localStorageService.get('confessionLimit.count');
    }

    $scope.hasConfessionLimitExceeded = function () {
        var confessionLimit = getConfessionLimit();
        return (confessionLimit <= 0);
    };

    function decreaseConfessionLimit() {
        var confessionLimit = getConfessionLimit();
        confessionLimit--;
        localStorageService.set('confessionLimit.count', confessionLimit);
    }

    // Load all categories onto form
    Category.getAll().success(function (response) {
        $scope.categories = response.data.categories;
    });

    $scope.confessionData = {};
    $scope.form = {
        imageSelected: false,
        selectedCategoryIDs: [],
        errors: [],
        submitSuccess: false,
        submitButtonDisabled: $scope.hasConfessionLimitExceeded()
    };

    $scope.setRecaptchaResponse = function (response) {
        $scope.confessionData.captcha = response;
    };

    $scope.submitConfession = function () {
        $scope.form.submitButtonDisabled = true;
        $scope.confessionData.categories = $scope.form.selectedCategoryIDs;

        if ($scope.hasConfessionLimitExceeded()) {
            return;
        }
        $scope.confessionData.content = $scope.confessionData.content
            .replace(/nus\s*whispers?/gi, 'NUSWhispers')
            .replace(/nus\s*mods?/gi, 'NUSMods');
        Confession.submit($scope.confessionData)
            .success(function (response) {
                $scope.form.submitSuccess = response.success;
                if (!response.success) {
                    $scope.form.submitButtonDisabled = false;
                    $scope.form.errors = [];
                    for (var error in response.errors) {
                        for (var msg in response.errors[error]) {
                            console.log(response.errors[error][msg]);
                            $scope.form.errors.push(response.errors[error][msg]);
                        }
                    }
                }
                decreaseConfessionLimit();
            })
            .error(function (response) {
                $scope.form.submitButtonDisabled = false;
                console.log(response);
            });
    };

    $scope.uploadConfessionImage = function () {
        filepicker.pick({
            extensions: ['.png', '.jpg', '.jpeg'],
            container: 'window'
        },
        function (fp) {
            $scope.confessionData.image = fp.url;
            $scope.form.imageSelected = true;
            $scope.$apply();
        },
        function (fpError) {
            console.log(fpError.toString());
        });
    };

    $scope.toggleCategorySelection = function (category) {
        var index = $scope.form.selectedCategoryIDs.indexOf(category.confession_category_id);

        // if category is selected
        if (index > -1) {
            // deselect it by removing it from the selection
            $scope.form.selectedCategoryIDs.splice(index, 1);
        } else {
            // add it to the selection
            $scope.form.selectedCategoryIDs.push(category.confession_category_id);
        }
    };

    $scope.highlightTags = function () {
        $scope.contentTagHighlights = '';
        if ($scope.confessionData.content === undefined) {
            return;
        }
        var splitContentTags = escapeHTML($scope.confessionData.content).split(/(#\w+)/);
        for (var i in splitContentTags) {
            if (/(#\w+)/.test(splitContentTags[i])) {
                $scope.contentTagHighlights += '<b>' + splitContentTags[i] + '</b>';
            } else {
                $scope.contentTagHighlights += splitContentTags[i];
            }
        }
        $scope.contentTagHighlights = $scope.contentTagHighlights.replace(/(?:\r\n|\r|\n)/g, '<br>');
    };

});
