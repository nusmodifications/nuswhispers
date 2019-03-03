import Popper from 'popper.js';
import $ from 'jquery';

// Import bootstrap-sass + dependencies.
window.Popper = Popper;

window.$ = window.jQuery = $;

import 'bootstrap-sass';

// Import application stylesheet.
import '../css/application.scss';
import 'typicons.font/src/font/typicons.css';
import 'angular-loading-bar/build/loading-bar.min.css';

// Bootstrap AngularJS stuffs.
import angular from 'angular';
import LocalStorageModule from 'angular-local-storage';
import ngCookies from 'angular-cookies';
import ngRoute from 'angular-route';
import ngResource from 'angular-resource';
import ngSanitize from 'angular-sanitize';
import ngAnimate from 'angular-animate';
import angularLoadingBar from 'angular-loading-bar';
import angularElastic from 'angular-elastic';
import angularMoment from 'angular-moment';
import ngInfiniteScroll from 'ng-infinite-scroll';
import uiBootstrap from 'angular-ui-bootstrap';
import uiRouter from '@uirouter/angularjs';
import vcRecaptcha from 'angular-recaptcha';

import controllersModuleName from './controllers';
import filters from './filters';

const moduleName = 'nuswhispersApp';

const config = (
  $routeProvider,
  $locationProvider,
  $httpProvider,
  localStorageServiceProvider,
  vcRecaptchaServiceProvider
) => {
  localStorageServiceProvider.setPrefix('nuswhispers');

  const confessionsRouteViewMap = {
    '/home/': 'featured',
    '/popular/': 'popular',
    '/latest/': 'latest',
    '/category/:category': 'category',
    '/tag/:tag': 'tag',
    '/confession/:confession': 'single',
    '/search/:query': 'search',
    '/favourites/': 'favourites',
  };

  Object.keys(confessionsRouteViewMap).forEach(route => {
    $routeProvider.when(route, {
      controller: 'ConfessionsController',
      controllerAs: 'vm',
      template: require('../templates/confessions.html'),
      resolve: {
        controllerOptions: () => ({ view: confessionsRouteViewMap[route] }),
      },
    });
  });

  $routeProvider
    .when('/submit/', {
      controller: 'SubmitController',
      controllerAs: 'vm',
      template: require('../templates/submit.html'),
    })
    .when('/policy/', {
      template: require('../templates/policy.html'),
    })
    .otherwise({
      redirectTo: '/home/',
    });

  $locationProvider.hashPrefix('!');
  $locationProvider.html5Mode(true);

  $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

  vcRecaptchaServiceProvider.setSiteKey(`'${RECAPTCHA_KEY || ''}'`);
};

// Run the AngularJS application!
angular
  .module(moduleName, [
    controllersModuleName,
    filters,
    LocalStorageModule,
    angularLoadingBar,
    angularElastic,
    angularMoment,
    ngCookies,
    ngResource,
    ngSanitize,
    ngRoute,
    ngAnimate,
    ngInfiniteScroll,
    uiBootstrap,
    uiRouter,
    vcRecaptcha,
  ])
  .config(config)
  .run();
