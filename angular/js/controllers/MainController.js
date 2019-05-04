class MainController {
  constructor(
    $location,
    Facebook,
    FacebookUserService,
    TagService,
    CategoryService
  ) {
    this.$location = $location;
    this.Facebook = Facebook;
    this.FacebookUserService = FacebookUserService;
    this.TagService = TagService;
    this.CategoryService = CategoryService;

    this.sidebarOpenedClass = '';
    this.isLoggedIn = false;

    this.categories = [];
    this.tags = [];
    this.fbUser = {};

    this.currentYear = new Date().getFullYear();

    // Load all categories onto sidebar
    this.CategoryService.getAll().then(({ data }) => {
      this.categories = data.data.categories;
    });

    // Load all tags onto sidebar
    this.TagService.getTop(5).then(({ data }) => {
      this.tags = data.data.tags;
    });

    this.getLoginStatus();
  }

  isActivePage(pageName) {
    return pageName === this.$location.path();
  }

  login() {
    this.Facebook.login(() => {
      this.getLoginStatus();
    });
  }

  logout() {
    this.Facebook.logout(() => {
      this.FacebookUserService.logout();
      this.getLoginStatus();
    });
  }

  getLoginStatus() {
    this.Facebook.getLoginStatus(response => {
      this.FacebookUserService.setAccessToken('');
      if (response.status === 'connected') {
        this.FacebookUserService.accessToken =
          response.authResponse.accessToken;
        this.FacebookUserService.userId = response.authResponse.userID;

        this.FacebookUserService.login(this.FacebookUserService.accessToken)
          .success(() => (this.isLoggedIn = true))
          .error(() => (this.isLoggedIn = false));
      } else {
        this.isLoggedIn = false;
      }
    });
  }

  searchConfessions(query) {
    this.$location.path(`/search/${query}`);
  }

  static controllerFactory(
    $location,
    Facebook,
    FacebookUserService,
    TagService,
    CategoryService
  ) {
    MainController.instance = new MainController(
      $location,
      Facebook,
      FacebookUserService,
      TagService,
      CategoryService
    );
    return MainController.instance;
  }
}

export default MainController;
