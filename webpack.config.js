const HtmlWebpackPlugin = require('html-webpack-plugin');
const mixWebpackConfig = require('laravel-mix/setup/webpack.config');
const mapValues = require('lodash/mapValues');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const path = require('path');
const webpack = require('webpack');

/**
 * @typedef {import('webpack').Configuration} Configuration
 */

/** @type {(env: {}, argv: {}) => Configuration} */
const webConfig = (env = {}, argv = {}) => ({
  name: 'web',
  devtool: argv.production ? '' : 'cheap-eval-source-map',
  entry: path.resolve(__dirname, './angular/js/app.js'),
  output: {
    path: path.resolve(__dirname, './public/web'),
    filename: 'app.js',
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
        loader: 'babel-loader',
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
    new webpack.DefinePlugin(
      mapValues(
        {
          'process.env.NODE_ENV': process.env.NODE_ENV || 'development',
          API_URL: env.api || '/api',
          FB_PAGE_ID: env.fbpage || '',
          FB_APP_ID: env.fbapp || '',
          FILESTACK_KEY: env.filestack || '',
          FINGERPRINT_API_KEY: env['fingerprint-api'] || '',
          FINGERPRINT_STORAGE_KEY: env['fingerprint-storage'] || '',
          RECAPTCHA_KEY: env.recaptcha || '',
        },
        value => JSON.stringify(value)
      )
    ),
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
      filename: 'app.css',
      chunkFilename: '[id].css',
    }),
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
