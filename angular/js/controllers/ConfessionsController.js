import find from 'lodash/find';
import get from 'lodash/get';

class ConfessionsController {
  constructor(
    $routeParams,
    ConfessionService,
    Facebook,
    FacebookUserService,
    controllerOptions
  ) {
    this.$routeParams = $routeParams;
    this.ConfessionService = ConfessionService;
    this.Facebook = Facebook;
    this.FacebookUserService = FacebookUserService;
    this.controllerOptions = controllerOptions;

    this.timestamp = Math.floor(Date.now() / 1000);
    this.offset = 0;
    this.count = 5;
    this.loadingConfessions = false;
    this.doLoadMoreConfessions = true;
    this.confessions = [];
    this.fbUser = this.FacebookUserService;

    this.getConfessions();
  }

  getConfessions() {
    const processConfessionResponse = confessions => {
      if (!confessions.length) {
        this.doLoadMoreConfessions = false;
      } else {
        this.confessions.push(
          ...confessions.map(confession =>
            this.ConfessionService.hydrate(confession)
          )
        );
        // set up next featured offset
        this.offset += this.count;
      }
      this.loadingConfessions = false;
    };

    this.loadingConfessions = true;
    this.view = this.controllerOptions.view;

    switch (this.view) {
      case 'single':
        this.ConfessionService.getConfessionById(
          this.$routeParams.confession
        ).then(({ data }) => {
          if (data.success) {
            processConfessionResponse([data.data.confession]);
          }
          this.doLoadMoreConfessions = false;
        });
        break;
      case 'recent':
        this.ConfessionService.getRecent(
          this.timestamp,
          this.offset,
          this.count
        ).then(({ data }) => {
          processConfessionResponse(data.data.confessions);
        });
        break;
      case 'popular':
        this.ConfessionService.getPopular(
          this.timestamp,
          this.offset,
          this.count
        ).then(({ data }) => {
          processConfessionResponse(data.data.confessions);
        });
        break;
      case 'category':
        this.ConfessionService.getCategory(
          this.$routeParams.category,
          this.timestamp,
          this.offset,
          this.count
        ).then(({ data }) => {
          processConfessionResponse(data.data.confessions);
        });
        break;
      case 'tag':
        this.ConfessionService.getTag(
          this.$routeParams.tag,
          this.timestamp,
          this.offset,
          this.count
        ).then(({ data }) => {
          processConfessionResponse(data.data.confessions);
        });
        break;
      case 'search':
        this.ConfessionService.search(
          this.$routeParams.query,
          this.timestamp,
          this.offset,
          this.count
        ).then(({ data }) => {
          processConfessionResponse(data.data.confessions);
        });
        break;
      case 'favourites':
        this.ConfessionService.getFavourites(
          this.timestamp,
          this.offset,
          this.count
        ).then(({ data }) => {
          if (data.success) {
            processConfessionResponse(data.data.confessions);
          } else {
            this.doLoadMoreConfessions = false;
            this.loadingConfessions = false;
          }
        });
        break;
      default:
        this.ConfessionService.getFeatured(
          this.timestamp,
          this.offset,
          this.count
        ).then(({ data }) => {
          processConfessionResponse(data.data.confessions);
        });
    }
  }

  confessionIsFavourited(confession) {
    if (this.fbUser.accessToken !== '') {
      const fbUserId = parseInt(this.fbUser.userId, 10);

      return !!find(
        confession.favourites || [],
        favourite => parseInt(favourite.fb_user_id, 10) === fbUserId
      );
    }
    return false;
  }

  toggleFavouriteConfession(confession) {
    if (this.fbUser.accessToken !== '') {
      if (this.confessionIsFavourited(confession)) {
        confession.unfavourite().then(({ data }) => {
          if (data.success) {
            confession.load();
          }
        });
      } else {
        confession.favourite().then(({ data }) => {
          if (data.success) {
            confession.load();
          }
        });
      }
    }
  }

  confessionIsLiked(confession) {
    if (this.fbUser.accessToken !== '') {
      const fbUserId = parseInt(this.fbUser.userId, 10);
      const fbConfessionLikes = get(
        confession,
        'facebook_information.likes.data',
        []
      );

      return !!find(
        fbConfessionLikes,
        like => parseInt(like.id, 10) === fbUserId
      );
    }
    return false;
  }

  toggleLikeConfession(confession) {
    const facebookID = confession.fb_post_id;
    if (this.confessionIsLiked(confession)) {
      this.Facebook.api('/' + facebookID + '/likes', 'POST', ({ data }) => {
        if (data.success) {
          confession.load();
        }
      });
    } else {
      this.Facebook.api('/' + facebookID + '/likes', 'DELETE', ({ data }) => {
        if (data.success) {
          confession.load();
        }
      });
    }
  }

  commentConfession(confession, commentText) {
    const facebookID = confession.fb_post_id;
    this.Facebook.api(
      '/' + facebookID + '/comments',
      'POST',
      { message: commentText },
      ({ data }) => {
        if (data.id) {
          this.highlightNewCommentID = data.id;
          confession.new_comment = '';
          confession.load();
        }
      }
    );
  }

  shareConfessionFB(confessionID) {
    this.Facebook.ui({
      method: 'share',
      href: 'http://nuswhispers.com/confession/' + confessionID,
    });
  }

  static controllerFactory(
    $routeParams,
    ConfessionService,
    Facebook,
    FacebookUserService,
    controllerOptions
  ) {
    ConfessionsController.instance = new ConfessionsController(
      $routeParams,
      ConfessionService,
      Facebook,
      FacebookUserService,
      controllerOptions
    );
    return ConfessionsController.instance;
  }
}

export default ConfessionsController;
