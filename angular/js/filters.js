import angular from 'angular';
import { escapeHtml } from './utils';

const moduleName = 'filters';

angular
  .module(moduleName, [])
  .filter('parseUrl', function() {
    const urls = /(\b(https?|ftp):\/\/[A-Z0-9+&@#/%?=~_|!:,.;-]*[-A-Z0-9+&@#/%=~_|])/gim;

    return function(text) {
      if (text.match(urls)) {
        text = text.replace(urls, '<a href="$1" target="_blank">$1</a>');
      }

      return text;
    };
  })
  .filter('linkTags', function() {
    return function(content) {
      const splitContentTags = content.split(/(#\w+)/);

      let processedContent = '';
      for (let i in splitContentTags) {
        if (/(#\w+)/.test(splitContentTags[i])) {
          processedContent +=
            '<a href="/#!tag/' +
            splitContentTags[i].substring(1) +
            '">' +
            splitContentTags[i] +
            '</a>';
        } else {
          processedContent += splitContentTags[i];
        }
      }
      return processedContent;
    };
  })
  .filter('escapeHtml', () => escapeHtml)
  .filter('truncate', function() {
    return function(text, length, end) {
      if (isNaN(length)) {
        length = 10;
      }

      if (end === undefined) {
        end = '...';
      }

      if (text.length <= length || text.length - end.length <= length) {
        return text;
      } else {
        return String(text).substring(0, length - end.length) + end;
      }
    };
  });

export default moduleName;
