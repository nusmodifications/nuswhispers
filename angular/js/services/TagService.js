class TagService {
  constructor($http) {
    this.$http = $http;
  }

  getTop(n) {
    return this.$http.get(`${API_URL}/tags/top/${n}`);
  }

  static serviceFactory($http) {
    TagService.instance = new TagService($http);
    return TagService.instance;
  }
}

export default TagService;
