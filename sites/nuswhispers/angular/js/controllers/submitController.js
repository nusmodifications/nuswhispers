angular.module('nuswhispersApp.controllers')
.controller('SubmitController', function ($scope, $http, Confession, Category, vcRecaptchaService) {
    'use strict';

    $scope.confessionData = {};
    $scope.imageSelected = false;
    $scope.selectedCategoryIDs = [];

    // Load all categories onto form
    Category.get().success(function (data) {
        $scope.categories = data;
    });

    $scope.submitConfession = function () {
        $scope.confessionData.categories = $scope.selectedCategoryIDs;
        $scope.confessionData.captcha = vcRecaptchaService.getResponse();

        Confession.submit($scope.confessionData)
            .success(function (data) {
                console.log(data);
            })
            .error(function (data) {
                console.log(data);
            });
    };

    $scope.uploadConfessionImage = function () {
        filepicker.pick({
            mimetypes: ['image/*'],
            container: 'window',
        },
        function (fp) {
            $scope.confessionData.image = fp.url;
            $scope.imageSelected = true;
            $scope.$apply();
        },
        function (fpError) {
            console.log(fpError.toString());
        });
    };

    $scope.toggleCategorySelection = function (category) {
        var index = $scope.selectedCategoryIDs.indexOf(category.confession_category_id);

        // if category is selected
        if (index > -1) {
            // deselect it by removing it from the selection
            $scope.selectedCategoryIDs.splice(index, 1);
        } else {
            // add it to the selection
            $scope.selectedCategoryIDs.push(category.confession_category_id);
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
