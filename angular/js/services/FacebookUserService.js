class FacebookUserService {
  constructor($http) {
    this.$http = $http;
  }

  login(accessToken) {
    return this.$http({
      method: 'POST',
      url: `${API_URL}/fbuser/login`,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      data: $.param({ fb_access_token: accessToken }),
    });
  }

  logout() {
    return this.$http({
      method: 'POST',
      url: `${API_URL}/fbuser/logout`,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    });
  }

  set accessToken(accessToken) {
    this.$accessToken = accessToken;
  }

  get accessToken() {
    return this.$accessToken;
  }

  set userId(userId) {
    this.$userId = userId;
  }

  get userId() {
    return this.$userId;
  }

  get pageId() {
    return FB_PAGE_ID || '';
  }

  static serviceFactory($http) {
    FacebookUserService.instance = new FacebookUserService($http);
    return FacebookUserService.instance;
  }
}

export default FacebookUserService;
