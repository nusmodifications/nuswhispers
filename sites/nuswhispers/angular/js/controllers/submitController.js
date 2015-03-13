appControllers.controller('SubmitController', function ($scope, $http, Confession) {
	$scope.confessionData = {};

	$scope.submitConfession = function () {
		$scope.loading = true;

		Confession.submit($scope.confessionData)
			.success(function (data) {
				$scope.loading = false;
				console.log(data);
			})
			. error(function (data) {
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
	}
	
});