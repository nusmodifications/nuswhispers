
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

var app = angular.module('nuswhispersApp', ['nuswhispersApp.controllers', 'angular-loading-bar', 'monospaced.elastic', 'angularMoment', 'ngCookies', 'ngResource', 'ngSanitize', 'ngRoute', 'ngAnimate', 'ui.utils', 'ui.bootstrap', 'ui.router', 'ngGrid']);

app.config(['$routeProvider', '$locationProvider', '$httpProvider', function ($routeProvider, $locationProvider, $httpProvider) {
    'use strict';

    $routeProvider
        .when('/home/', {
            templateUrl: 'assets/templates/home.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'featured',
                    };
                }
            }
        })
        .when('/trending/', {
            templateUrl: 'assets/templates/home.html'
        })
        .when('/new/', {
            templateUrl: 'assets/templates/home.html'
        })
        .when('/category/:category', {
            templateUrl: 'assets/templates/home.html'
        })
        .when('/tag/:tag', {
            templateUrl: 'assets/templates/home.html'
        })
        .when('/submit/', {
            templateUrl: 'assets/templates/submit.html',
            controller: 'SubmitController'
        })
        .otherwise({
            redirectTo: '/home/'
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
.controller('ConfessionsController', function ($scope, Confession, controllerOptions) {
    'use strict';

    $scope.getFeatured = function () {
        Confession.getFeatured($scope.timestamp, $scope.offset, $scope.count)
            .success(function (response) {
                console.log(JSON.stringify(response.data.confessions));
                $scope.confessions.push.apply($scope.confessions, response.data.confessions);
                // set up next featured offset
                $scope.offset += $scope.count;
            })
            .error(function (response) {
                console.log(response);
            });
    };

    $scope.timestamp = Math.floor(Date.now() / 1000);
    $scope.offset = 0;
    $scope.count = 10;
    $scope.confessions = [];

    switch (controllerOptions.view) {
        default:
            $scope.getFeatured();
    }

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
});

angular.module('nuswhispersApp.controllers')
.controller('MainController', function ($scope, Facebook, FacebookData, Category) {
    'use strict';

    $scope.sidebarOpenedClass = '';
    $scope.isLoggedIn = false;

    // Load all categories onto sidebar
    Category.get().success(function (response) {
        $scope.categories = response.data.categories;
    });

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
        },
        getFeatured: function (timestamp, offset, count) {
            return $http({
                method: 'GET',
                url: '/api/confessions',
                params: {timestamp: timestamp, offset: offset, count: count}
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