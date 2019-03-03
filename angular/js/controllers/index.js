import angular from 'angular';
import 'angularjs-facebook';
import servicesModuleName from '../services';
import ConfessionsController from './ConfessionsController';
import MainController from './MainController';
import SubmitController from './SubmitController';

const moduleName = 'nuswhispersApp.controllers';

angular
  .module(moduleName, [servicesModuleName, 'facebook'])
  .config(FacebookProvider => {
    FacebookProvider.init(FB_APP_ID);
  })
  .controller('MainController', MainController.controllerFactory)
  .controller('ConfessionsController', ConfessionsController.controllerFactory)
  .controller('SubmitController', SubmitController.controllerFactory);

export default moduleName;
