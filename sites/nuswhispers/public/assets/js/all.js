
/*================================================================
=>                  App = nuswhispersApp
==================================================================*/
/*global angular*/
filepicker.setKey('AnsmRtYIsR9qh79Hxxrpez');

angular.module('nuswhispersApp.services', ['facebook']).config(
    function (FacebookProvider) {
        'use strict';
        FacebookProvider.init('563666707081891');
    }
);
angular.module('nuswhispersApp.controllers', ['nuswhispersApp.services', 'vcRecaptcha']);

var app = angular.module('nuswhispersApp', ['nuswhispersApp.controllers', 'angular-loading-bar', 'monospaced.elastic', 'ngCookies', 'ngResource', 'ngSanitize', 'ngRoute', 'ngAnimate', 'ui.utils', 'ui.bootstrap', 'ui.router', 'ngGrid']);

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

angular.module('nuswhispersApp.services')
.factory('FacebookData', function () {
    'use strict';

    var data = {
        accessToken: ''
    };

    return {
        setAccessToken: function (accessToken) {
            data.accessToken = accessToken;
        },
        getAccessToken: function () {
            return data.accessToken;
        }
    };
});
angular.module('nuswhispersApp.controllers')
.controller('MainController', function ($scope, Facebook, FacebookData) {
    'use strict';

    $scope.sidebarOpenedClass = '';
    $scope.isLoggedIn = false;

    $scope.toggleSidebar = function () {
        if ($scope.sidebarOpenedClass === '') {
            $scope.sidebarOpenedClass = 'sidebar-opened';
        } else {
            $scope.sidebarOpenedClass = '';
        }
    };

    $scope.login = function () {
        Facebook.login(function (response) {
            $scope.getLoginStatus();
        }, {
            scope: 'publish_actions'
        });
    };

    $scope.logout = function () {
        Facebook.logout(function (response) {
            $scope.getLoginStatus();
        });
    };

    $scope.getLoginStatus = function () {
        Facebook.getLoginStatus(function (response) {
            $scope.isLoggedIn = response.status === 'connected';
            if ($scope.isLoggedIn) {
                FacebookData.setAccessToken(response.authResponse.accessToken);
            }
        });
    };

    $scope.getLoginStatus();
});
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
        var splitContentTags = $scope.confessionData.content.split(/(#\w+)/);
        $scope.contentTagHighlights = '';
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
