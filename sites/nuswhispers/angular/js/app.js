
/*================================================================
=>                  App = nuswhispersApp
==================================================================*/
/*global angular*/
filepicker.setKey('AnsmRtYIsR9qh79Hxxrpez');

angular.module('nuswhispersApp.services', ['facebook']).config(
    function (FacebookProvider) {
        'use strict';
        // @if NODE_ENV = 'development'
        FacebookProvider.init('563666707081891');
        // @endif
        // @if NODE_ENV = 'production'
        FacebookProvider.init('1577825682475577');
        // @endif
    }
);
angular.module('nuswhispersApp.controllers', ['nuswhispersApp.services', 'vcRecaptcha']);

var app = angular.module('nuswhispersApp', ['nuswhispersApp.controllers', 'filters', 'LocalStorageModule', 'angular-loading-bar', 'monospaced.elastic', 'angularMoment', 'infinite-scroll', 'ngCookies', 'ngResource', 'ngSanitize', 'ngRoute', 'ngAnimate', 'ui.utils', 'ui.bootstrap', 'ui.router']);

app.config(['$routeProvider', '$locationProvider', '$httpProvider', 'localStorageServiceProvider', function ($routeProvider, $locationProvider, $httpProvider, localStorageServiceProvider) {
    'use strict';

    localStorageServiceProvider.setPrefix('nuswhispers');

    $routeProvider
        .when('/home/', {
            templateUrl: 'assets/templates/confessions.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'featured',
                    };
                }
            }
        })
        .when('/popular/', {
            templateUrl: 'assets/templates/confessions.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'popular',
                    };
                }
            }
        })
        .when('/latest/', {
            templateUrl: 'assets/templates/confessions.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'recent',
                    };
                }
            }
        })
        .when('/category/:category', {
            templateUrl: 'assets/templates/confessions.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'category'
                    };
                }
            }
        })
        .when('/tag/:tag', {
            templateUrl: 'assets/templates/confessions.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'tag'
                    };
                }
            }
        })
        .when('/confession/:confession', {
            templateUrl: 'assets/templates/confessions.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'single'
                    };
                }
            }
        })
        .when('/search/:query', {
            templateUrl: 'assets/templates/confessions.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'search'
                    };
                }
            }
        })
        .when('/favourites/', {
            templateUrl: 'assets/templates/confessions.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'favourites'
                    };
                }
            }
        })
        .when('/submit/', {
            templateUrl: 'assets/templates/submit.html',
            controller: 'SubmitController'
        })
        .when('/mobile_submit/', {
            templateUrl: 'assets/templates/submit.html',
            controller: 'SubmitController'
        })
        .when('/policy/', {
            templateUrl: 'assets/templates/policy.html',
        })
        .otherwise({
            redirectTo: '/home/'
        });

    $locationProvider.hashPrefix('!');
    $locationProvider.html5Mode(true);

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

function escapeHTML(content) {
    'use strict';
    return content.replace(/[&<"']/g, function (m) {
        switch (m) {
            case '&':
                return '&amp;';
            case '<':
                return '&lt;';
            case '"':
                return '&quot;';
            default:
                return m;
        }
    });
}


/* ---> Do not delete this comment (Values) <--- */

/* ---> Do not delete this comment (Constants) <--- */
