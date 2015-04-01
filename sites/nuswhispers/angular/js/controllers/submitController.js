angular.module('nuswhispersApp.controllers')
.controller('SubmitController', function ($scope, $http, Confession, Category, vcRecaptchaService) {
    'use strict';

    // Load all categories onto form
    Category.get().success(function (response) {
        $scope.categories = response.data.categories;
    });

    $scope.confessionData = {};
    $scope.form = {
        imageSelected: false,
        selectedCategoryIDs: [],
        errors: [],
        submitSuccess: false
    };

    $scope.setRecaptchaResponse = function (response) {
        $scope.confessionData.captcha = response;
    };

    $scope.submitConfession = function () {
        $scope.confessionData.categories = $scope.form.selectedCategoryIDs;

        Confession.submit($scope.confessionData)
            .success(function (response) {
                $scope.form.submitSuccess = response.success;
                if (!response.success) {
                    $scope.form.errors = [];
                    for (var error in response.errors) {
                        for (var msg in response.errors[error]) {
                            $scope.form.errors.push(response.errors[error][msg]);
                        }
                    }
                }
            })
            .error(function (response) {
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
        var splitContentTags = $scope.confessionData.content.split(/(#\w+)/);
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
