const path = require('path');
const withTypescript = require('@zeit/next-typescript');

const resolveModule = dir => path.join(__dirname, '../node_modules', dir);

module.exports = withTypescript({
  webpack: (config, { defaultLoaders }) => {
    // Transpile Node dependencies.
    config.module.rules.push({
      test: /\.js$/,
      loader: defaultLoaders.babel,
      exclude: /node_modules/,
      include: [
        resolveModule('camelcase'),
        resolveModule('camelcase-keys'),
        resolveModule('map-obj'),
        resolveModule('quick-lru'),
      ],
    });

    return config;
  },
});
