import { escapeHtml } from '../utils';
import { init } from 'filestack-js';

class SubmitController {
  constructor(
    $scope,
    $http,
    ConfessionService,
    CategoryService,
    localStorageService
  ) {
    this.$scope = $scope;
    this.$http = $http;
    this.ConfessionService = ConfessionService;
    this.CategoryService = CategoryService;
    this.localStorageService = localStorageService;

    this.categories = [];
    this.confessionData = {};
    this.contentTagHighlights = '';

    this.form = {
      imageSelected: false,
      selectedCategoryIDs: [],
      errors: [],
      submitSuccess: false,
      submitButtonDisabled: this.hasConfessionLimitExceeded(),
    };

    // Load all categories onto form.
    this.CategoryService.getAll().then(({ data }) => {
      this.categories = data.data.categories;
    });

    // Init filestack.
    this.filestack = init(FILESTACK_KEY || '');
  }

  getConfessionLimit() {
    var doResetLimit =
      !this.localStorageService.get('confessionLimit.date') ||
      this.localStorageService.get('confessionLimit.date') !==
        new Date().toDateString();
    if (doResetLimit) {
      this.localStorageService.set('confessionLimit.count', 3);
      this.localStorageService.set(
        'confessionLimit.date',
        new Date().toDateString()
      );
    }
    return this.localStorageService.get('confessionLimit.count', 0);
  }

  hasConfessionLimitExceeded() {
    return this.getConfessionLimit() <= 0;
  }

  decreaseConfessionLimit() {
    this.localStorageService.set(
      'confessionLimit.count',
      this.getConfessionLimit() - 1
    );
  }

  setRecaptchaResponse(response) {
    this.confessionData.captcha = response;
  }

  submitConfession() {
    this.form.submitButtonDisabled = true;
    this.confessionData.categories = this.form.selectedCategoryIDs;
    this.confessionData[FINGERPRINT_API_KEY] = this.localStorageService.get(
      FINGERPRINT_STORAGE_KEY
    );

    if (this.hasConfessionLimitExceeded()) {
      return;
    }

    this.confessionData.content = this.confessionData.content
      .replace(/nus\s*whispers?\b/gi, 'NUSWhispers')
      .replace(/nus\s*mods?\b/gi, 'NUSMods');

    this.ConfessionService.submit(this.confessionData)
      .then(({ data }) => {
        this.form.submitSuccess = data.success;

        if (!data.success) {
          this.form.submitButtonDisabled = false;

          if (data.data && data.data.errors) {
            this.processErrors(data.data.errors);
          }
        }
        this.decreaseConfessionLimit();
        this.localStorageService.set(
          FINGERPRINT_STORAGE_KEY,
          data[FINGERPRINT_API_KEY]
        );
      })
      .catch(err => {
        this.form.submitButtonDisabled = false;
        if (err.data && err.data.errors) {
          this.processErrors(err.data.errors);
        }
      });
  }

  processErrors(errors) {
    this.form.errors = [];

    for (let error in errors) {
      for (let msg in errors[error]) {
        this.form.errors.push(errors[error][msg]);
      }
    }
  }

  uploadConfessionImage() {
    this.filestack
      .picker({
        accept: 'image/*',
        onFileUploadFinished: file => {
          this.confessionData.image = file.url;
          this.form.imageSelected = true;
          this.$scope.$apply();
        },
      })
      .open();
  }

  toggleCategorySelection(category) {
    const index = this.form.selectedCategoryIDs.indexOf(
      category.confession_category_id
    );

    // if category is selected
    if (index > -1) {
      // deselect it by removing it from the selection
      this.form.selectedCategoryIDs.splice(index, 1);
    } else {
      // add it to the selection
      this.form.selectedCategoryIDs.push(category.confession_category_id);
    }
  }

  highlightTags() {
    if (this.confessionData.content === undefined) {
      return;
    }

    const splitContentTags = escapeHtml(this.confessionData.content).split(
      /(#\w+)/
    );

    for (let i in splitContentTags) {
      if (/(#\w+)/.test(splitContentTags[i])) {
        this.contentTagHighlights += '<b>' + splitContentTags[i] + '</b>';
      } else {
        this.contentTagHighlights += splitContentTags[i];
      }
    }

    this.contentTagHighlights = this.contentTagHighlights.replace(
      /(?:\r\n|\r|\n)/g,
      '<br>'
    );
  }

  static controllerFactory(
    $scope,
    $http,
    ConfessionService,
    CategoryService,
    localStorageService
  ) {
    SubmitController.instance = new SubmitController(
      $scope,
      $http,
      ConfessionService,
      CategoryService,
      localStorageService
    );
    return SubmitController.instance;
  }
}

export default SubmitController;
