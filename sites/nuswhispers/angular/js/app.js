
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
