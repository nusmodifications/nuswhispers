class CategoryService {
  constructor($http) {
    this.$http = $http;
  }

  getAll() {
    return this.$http.get(`${API_URL}/categories`);
  }

  static serviceFactory($http) {
    CategoryService.instance = new CategoryService($http);
    return CategoryService.instance;
  }
}

export default CategoryService;
