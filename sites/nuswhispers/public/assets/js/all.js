
/*================================================================
=>                  App = nuswhispersApp
==================================================================*/
/*global angular*/
filepicker.setKey('AnsmRtYIsR9qh79Hxxrpez');

angular.module('nuswhispersApp.services', []);
angular.module('nuswhispersApp.controllers', ['nuswhispersApp.services', 'vcRecaptcha']);

var app = angular.module('nuswhispersApp', ['nuswhispersApp.controllers', 'ngCookies', 'ngResource', 'ngSanitize', 'ngRoute', 'ngAnimate', 'ui.utils', 'ui.bootstrap', 'ui.router', 'ngGrid']);

app.config(['$routeProvider', '$locationProvider', '$httpProvider', function ($routeProvider, $locationProvider, $httpProvider) {
    'use strict';

    $routeProvider
        .when('/home', {
            templateUrl: 'assets/templates/home.html'
        })
        .when('/submit', {
            templateUrl: 'assets/templates/submit.html',
            controller: 'SubmitController'
        })
        .otherwise({
            redirectTo: '/home'
        });

    $locationProvider.hashPrefix('!');

    // This is required for Browser Sync to work poperly
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}]);


/*================================================================
=>                  nuswhispersApp App Run()
==================================================================*/

app.run(['$rootScope', function ($rootScope) {

    'use strict';

    console.log('Angular.js run() function...');
}]);




/* ---> Do not delete this comment (Values) <--- */

/* ---> Do not delete this comment (Constants) <--- */

angular.module('nuswhispersApp.controllers')
.controller('MainController', function ($scope) {
    'use strict';

    $scope.sidebarOpenedClass = '';

    $scope.toggleSidebar = function () {
        if ($scope.sidebarOpenedClass === '') {
            $scope.sidebarOpenedClass = 'sidebar-opened';
        } else {
            $scope.sidebarOpenedClass = '';
        }
    };
});
angular.module('nuswhispersApp.controllers')
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

angular.module('nuswhispersApp.services')
.factory('Category', function ($http) {
    'use strict';
    return {
        get: function () {
            return $http.get('/api/categories');
        }
    };
});

angular.module('nuswhispersApp.services')
.factory('Confession', function ($http) {
    'use strict';
    return {
        submit: function (confessionData) {
            return $http({
                method: 'POST',
                url: '/api/confessions',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                data: $.param(confessionData)
            });
        }
    };
});
