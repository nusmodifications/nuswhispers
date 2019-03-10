const forEach = require('lodash/forEach');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const mixWebpackConfig = require('laravel-mix/setup/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const path = require('path');
const webpack = require('webpack');

/**
 * @typedef {import('webpack').Configuration} Configuration
 */

const buildDefinitions = env => {
  const defineMap = {
    API_URL: { key: 'api', default: '/api' },
    FB_PAGE_ID: { key: 'fbpage', default: '' },
    FB_APP_ID: { key: 'fbapp', default: '' },
    FILESTACK_KEY: { key: 'filestack', default: '' },
    FINGERPRINT_API_KEY: { key: 'fingerprint-api', default: '' },
    FINGERPRINT_STORAGE_KEY: { key: 'fingerprint-storage', default: '' },
    RECAPTCHA_KEY: { key: 'recaptcha', default: null },
  };

  let definitions = {
    'process.env.NODE_ENV': JSON.stringify(
      process.env.NODE_ENV || 'development'
    ),
  };

  forEach(defineMap, (config, variable) => {
    let value = config.default;

    if (env[config.key]) {
      value = env[config.key];
    } else if (process.env[variable]) {
      value = process.env[variable];
    }

    definitions[variable] = JSON.stringify(value);
  });

  return definitions;
};

/** @type {(env: {}, argv: {}) => Configuration} */
const webConfig = (env = {}, argv = {}) => ({
  name: 'web',
  devtool: argv.production ? '' : 'cheap-eval-source-map',
  entry: {
    app: path.resolve(__dirname, './angular/js/app.js'),
  },
  output: {
    path: path.resolve(__dirname, './public/assets/web'),
    filename: '[name].js',
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'eslint-loader',
          options: {
            emitWarning: !argv.production,
          },
        },
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            cacheDirectory: true,
          },
        },
      },
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'resolve-url-loader',
          'postcss-loader',
          {
            loader: 'sass-loader',
            options: {
              implementation: require('sass'),
            },
          },
        ],
      },
      {
        test: /\.(png|jpg|gif)$/,
        use: {
          loader: 'url-loader',
          options: {
            limit: 8192,
          },
        },
      },
      {
        test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[ext]',
            },
          },
        ],
      },
      {
        test: /\.html$/,
        use: {
          loader: 'html-loader',
          options: {
            minimize: !!argv.production,
          },
        },
      },
    ],
  },
  resolve: {},
  plugins: [
    new webpack.DefinePlugin(buildDefinitions(env)),
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
    }),
    new HtmlWebpackPlugin({
      template: path.resolve(__dirname, './angular/app.html'),
      filename: path.resolve(
        __dirname,
        `./public/${env.devserver ? 'index' : 'app'}.html`
      ),
    }),
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
    new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
    !argv.production ? new webpack.HotModuleReplacementPlugin() : '',
  ].filter(v => v),
  devServer: {
    allowedHosts: ['localhost', 'nuswhispers.local', 'nuswhispers.com'],
    contentBase: path.resolve(__dirname, './public'),
    hot: true,
    historyApiFallback: true,
  },
  optimization: {
    minimizer: [new OptimizeCSSAssetsPlugin()],
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /node_modules/,
          chunks: 'initial',
          name: 'vendor',
          enforce: true,
        },
      },
    },
  },
});

/** @type {Configuration} */
const adminConfig = { ...mixWebpackConfig, name: 'admin' };

module.exports = [webConfig, adminConfig];
