import angular from 'angular';
import CategoryService from './CategoryService';
import ConfessionService from './ConfessionService';
import FacebookUserService from './FacebookUserService';
import TagService from './TagService';

const moduleName = 'nuswhispersApp.services';

angular
  .module(moduleName, [])
  .factory('ConfessionService', ConfessionService.serviceFactory)
  .factory('CategoryService', CategoryService.serviceFactory)
  .factory('FacebookUserService', FacebookUserService.serviceFactory)
  .factory('TagService', TagService.serviceFactory);

export default moduleName;
