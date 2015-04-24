angular.module('filters', [])
.filter('parseUrl', function () {
    'use strict';
    var urls = /(\b(https?|ftp):\/\/[A-Z0-9+&@#\/%?=~_|!:,.;-]*[-A-Z0-9+&@#\/%=~_|])/gim;

    return function (text) {
        if (text.match(urls)) {
            text = text.replace(urls, '<a href=\"$1\" target=\"_blank\">$1</a>');
        }

        return text;
    };
})
.filter('truncate', function () {
    'use strict';
    return function (text, length, end) {
        if (isNaN(length)) {
            length = 10;
        }

        if (end === undefined) {
            end = '...';
        }

        if (text.length <= length || text.length - end.length <= length) {
            return text;
        }
        else {
            return String(text).substring(0, length - end.length) + end;
        }
    };
});