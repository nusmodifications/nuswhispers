angular.module('nuswhispersApp.controllers', ['nuswhispersApp.services', 'vcRecaptcha'])
.controller('SubmitController', function ($scope, $http, Confession, Category, vcRecaptchaService) {
    'use strict';

	$scope.confessionData = {};
	$scope.selectedCategoryIDs = [];

	$scope.loading = true;

	// Load all categories onto form
	Category.get().success(function (data) {
		$scope.categories = data;
		$scope.loading = false;
	});

	$scope.submitConfession = function () {
		$scope.loading = true;
		$scope.confessionData.categories = $scope.selectedCategoryIDs;
		$scope.confessionData.captcha = vcRecaptchaService.getResponse();

		Confession.submit($scope.confessionData)
			.success(function (data) {
				$scope.loading = false;
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

});
