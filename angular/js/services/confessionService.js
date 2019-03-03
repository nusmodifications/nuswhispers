import forEach from 'lodash/forEach';

class Confession {
  constructor(ConfessionService, data = {}) {
    this.ConfessionService = ConfessionService;
    this.setData(data);
  }

  setData(data) {
    forEach(data, (value, key) => (this[key] = value));
  }

  load() {
    this.ConfessionService.getConfessionById(this.confession_id).then(
      ({ data }) => this.setData(data.data.confession)
    );
  }

  favourite() {
    return this.ConfessionService.favourite(this.confession_id);
  }

  unfavourite() {
    return this.ConfessionService.unfavourite(this.confession_id);
  }
}

class ConfessionService {
  constructor($http) {
    this.$http = $http;
  }

  hydrate(confession) {
    return new Confession(this, confession);
  }

  submit(confessionData) {
    return this.$http({
      method: 'POST',
      url: `${API_URL}/confessions`,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      data: $.param(confessionData),
    });
  }

  getConfessionById(confessionID) {
    return this.$http({
      method: 'GET',
      url: `${API_URL}/confessions/${confessionID}`,
    });
  }

  getFeatured(timestamp, offset, count) {
    return this.$http({
      method: 'GET',
      url: `${API_URL}/confessions`,
      params: { timestamp, offset, count },
    });
  }

  getPopular(timestamp, offset, count) {
    return this.$http({
      method: 'GET',
      url: `${API_URL}/confessions/popular`,
      params: { timestamp, offset, count },
    });
  }

  getRecent(timestamp, offset, count) {
    return this.$http({
      method: 'GET',
      url: `${API_URL}/confessions/recent`,
      params: { timestamp, offset, count },
    });
  }

  getCategory(categoryID, timestamp, offset, count) {
    return this.$http({
      method: 'GET',
      url: `${API_URL}/confessions/category/${categoryID}`,
      params: { timestamp, offset, count },
    });
  }

  getTag(tag, timestamp, offset, count) {
    return this.$http({
      method: 'GET',
      url: `${API_URL}/confessions/tag/${tag}`,
      params: { timestamp, offset, count },
    });
  }

  search(query, timestamp, offset, count) {
    return this.$http({
      method: 'GET',
      url: `${API_URL}/confessions/search/${encodeURIComponent(query)}`,
      params: { timestamp, offset, count },
    });
  }

  getFavourites(timestamp, offset, count) {
    return this.$http({
      method: 'GET',
      url: `${API_URL}/confessions/favourites`,
      params: { timestamp, offset, count },
    });
  }

  favourite(confessionId) {
    return this.$http({
      method: 'POST',
      url: `${API_URL}/fbuser/favourite`,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      data: $.param({ confession_id: confessionId }),
    });
  }

  unfavourite(confessionId) {
    return this.$http({
      method: 'POST',
      url: `${API_URL}/fbuser/unfavourite`,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      data: $.param({ confession_id: confessionId }),
    });
  }

  static serviceFactory($http) {
    ConfessionService.instance = new ConfessionService($http);
    return ConfessionService.instance;
  }
}

export default ConfessionService;
