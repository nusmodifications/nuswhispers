
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

var app = angular.module('nuswhispersApp', ['nuswhispersApp.controllers', 'angular-loading-bar', 'monospaced.elastic', 'angularMoment', 'infinite-scroll', 'ngCookies', 'ngResource', 'ngSanitize', 'ngRoute', 'ngAnimate', 'ui.utils', 'ui.bootstrap', 'ui.router', 'ngGrid']);

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
        .when('/popular/', {
            templateUrl: 'assets/templates/home.html',
            controller: 'ConfessionsController',
            resolve: {
                controllerOptions: function () {
                    return {
                        view: 'popular',
                    };
                }
            }
        })
        .when('/new/', {
            templateUrl: 'assets/templates/home.html',
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
            templateUrl: 'assets/templates/home.html',
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
.controller('ConfessionsController', function ($scope, $routeParams, Confession, FacebookUser, controllerOptions) {
    'use strict';

    $scope.getConfessions = function () {
        function processConfessionResponse(confessions) {
            if (confessions.length === 0) {
                $scope.doLoadMoreConfessions = false;
            } else {
                var confessionModels = [];
                for (var i in confessions) {
                    confessionModels.push(new Confession(confessions[i]));
                }
                $scope.confessions.push.apply($scope.confessions, confessionModels);
                // set up next featured offset
                $scope.offset += $scope.count;
            }
            $scope.loadingConfessions = false;
        }

        $scope.loadingConfessions = true;
        switch (controllerOptions.view) {
            case 'recent':
                Confession.getRecent($scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
                break;
            case 'popular':
                Confession.getPopular($scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
                break;
            case 'category':
                Confession.getCategory($routeParams.category, $scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
                break;
            default:
                Confession.getFeatured($scope.timestamp, $scope.offset, $scope.count)
                    .success(function (response) {
                        processConfessionResponse(response.data.confessions);
                    });
        }
    };

    $scope.timestamp = Math.floor(Date.now() / 1000);
    $scope.offset = 0;
    $scope.count = 5;
    $scope.loadingConfessions = false;
    $scope.doLoadMoreConfessions = true;
    $scope.confessions = [];
    $scope.fbUser = FacebookUser;

    $scope.getConfessions();

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

    $scope.confessionIsFavourited = function (confession) {
        if (FacebookUser.getAccessToken() !== '') {
            var fbUserID = parseInt(FacebookUser.getUserID());
            for (var i in confession.favourites) {
                if (confession.favourites[i].fb_user_id === fbUserID) {
                    return true;
                }
            }
        }
        return false;
    };

    $scope.toggleFavouriteConfession = function (confession) {
        if (FacebookUser.getAccessToken() !== '') {
            if (confession.isFavourited || $scope.confessionIsFavourited(confession)) {
                confession.unfavourite().success(function (response) {
                    if (response.success) {
                        confession.load();
                    }
                });
            } else {
                confession.favourite().success(function (response) {
                    if (response.success) {
                        confession.load();
                    }
                });
            }
        }
    };
    
});

angular.module('nuswhispersApp.controllers')
.controller('MainController', function ($scope, $location, Facebook, FacebookUser, Tag, Category) {
    'use strict';

    $scope.sidebarOpenedClass = '';
    $scope.isLoggedIn = false;

    // Load all categories onto sidebar
    Category.getAll().success(function (response) {
        $scope.categories = response.data.categories;
    });

    // Load all tags onto sidebar
    Tag.getTop(5).success(function (response) {
        $scope.tags = response.data.tags;
    });

    $scope.toggleSidebar = function () {
        if ($scope.sidebarOpenedClass === '') {
            $scope.sidebarOpenedClass = 'sidebar-opened';
        } else {
            $scope.sidebarOpenedClass = '';
        }
    };

    $scope.isActivePage = function (pageName) {
        return (pageName === $location.path());
    };

    $scope.login = function () {
        Facebook.login(function (response) {
            $scope.getLoginStatus();
        }, {
            // scope: 'publish_actions'
        });
    };

    $scope.logout = function () {
        Facebook.logout(function (response) {
            $scope.getLoginStatus();
        });
    };

    $scope.getLoginStatus = function () {
        Facebook.getLoginStatus(function (response) {
            FacebookUser.setAccessToken('');
            if (response.status === 'connected') {
                FacebookUser.setAccessToken(response.authResponse.accessToken);
                FacebookUser.setUserID(response.authResponse.userID);
                FacebookUser.login()
                    .success(function (response) {
                        $scope.isLoggedIn = true;
                    })
                    .error(function (response) {
                        $scope.isLoggedIn = false;
                    });
            } else {
                $scope.isLoggedIn = false;
            }
        });
    };

    $scope.getLoginStatus();
});
angular.module('nuswhispersApp.controllers')
.controller('SubmitController', function ($scope, $http, Confession, Category, vcRecaptchaService) {
    'use strict';

    // Load all categories onto form
    Category.getAll().success(function (response) {
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
        getAll: function () {
            return $http.get('/api/categories');
        }
    };
});

angular.module('nuswhispersApp.services')
.factory('Confession', function ($http) {
    'use strict';

    function Confession(confessionData) {
        if (confessionData) {
            this.setData(confessionData);
        }
    }

    Confession.submit = function (confessionData) {
        return $http({
            method: 'POST',
            url: '/api/confessions',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data: $.param(confessionData)
        });
    };

    Confession.getFeatured = function (timestamp, offset, count) {
        return $http({
            method: 'GET',
            url: '/api/confessions',
            params: {timestamp: timestamp, offset: offset, count: count}
        });
    };

    Confession.getPopular = function (timestamp, offset, count) {
        return $http({
            method: 'GET',
            url: '/api/confessions/popular',
            params: {timestamp: timestamp, offset: offset, count: count}
        });
    };

    Confession.getRecent = function (timestamp, offset, count) {
        return $http({
            method: 'GET',
            url: '/api/confessions/recent',
            params: {timestamp: timestamp, offset: offset, count: count}
        });
    };

    Confession.getCategory = function (categoryID, timestamp, offset, count) {
        return $http({
            method: 'GET',
            url: '/api/confessions/category/' + categoryID,
            params: {timestamp: timestamp, offset: offset, count: count}
        });
    };

    Confession.prototype = {
        setData: function (confessionData) {
            angular.extend(this, confessionData);
        },
        load: function () {
            var confession = this;
            $http.get('/api/confessions/' + confession.confession_id).success(function (response) {
                if (response.success) {
                    confession.setData(response.data.confession);
                }
            });
        },
        favourite: function () {
            return $http({
                method: 'POST',
                url: '/api/fbuser/favourite',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                data: $.param({'confession_id': this.confession_id})
            });
        },
        unfavourite: function () {
            return $http({
                method: 'POST',
                url: '/api/fbuser/unfavourite',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                data: $.param({'confession_id': this.confession_id})
            });
        }
    };

    return Confession;
});

angular.module('nuswhispersApp.services')
.factory('FacebookUser', function ($http) {
    'use strict';

    var data = {
        accessToken: '',
        userID: ''
    };

    return {
        login: function () {
            return $http({
                method: 'POST',
                url: '/api/fbuser/login/',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                data: $.param({'fb_access_token': data.accessToken})
            });
        },
        setAccessToken: function (accessToken) {
            data.accessToken = accessToken;
        },
        getAccessToken: function () {
            return data.accessToken;
        },
        setUserID: function (userID) {
            data.userID = userID;
        },
        getUserID: function () {
            return data.userID;
        }
    };
});

angular.module('nuswhispersApp.services')
.factory('Tag', function ($http) {
    'use strict';
    return {
        getTop: function (n) {
            return $http.get('/api/tags/top/' + n);
        }
    };
});
