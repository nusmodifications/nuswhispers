'use strict';

const gulp = require('gulp');
const bowerFiles = require('main-bower-files');
const del = require('del');
const stylish = require('jshint-stylish');
const path = require('path');
const opn = require('opn');
const fs = require('fs');
const chalk = require('chalk');
const args = require('yargs').argv;
const map = require('map-stream');
const runSequence = require('run-sequence');
const gulpPlugins = require('gulp-load-plugins')();

// chalk config
const errorLog = chalk.red.bold;
const hintLog = chalk.blue;
const changeLog = chalk.red;

const SETTINGS = {
  src: {
    app: 'angular/',
    css: 'angular/css/',
    js: 'angular/js/',
    templates: 'angular/templates/',
    images: 'angular/img/',
    fonts: 'angular/fonts/',
    bower: 'bower_components/',
  },
  build: {
    app: 'public/',
    css: 'public/assets/css/',
    js: 'public/assets/js/',
    templates: 'public/assets/templates/',
    images: 'public/assets/img/',
    fonts: 'public/assets/fonts/',
    bower: 'public/assets/bower/', // If you change this, you will have to change in index.html as well.
  },
  scss: 'scss/',
};

const bowerConfig = {
  paths: {
    bowerDirectory: SETTINGS.src.bower,
    bowerrc: '.bowerrc',
    bowerJson: 'bower.json',
  },
};

// server and live reload config
const serverConfig = {
  root: SETTINGS.build.app,
  host: 'localhost',
  port: 9000,
  livereload: true,
};

// jsHint Options.
const hintOptions = JSON.parse(fs.readFileSync('.jshintrc', 'utf8'));

// Flag for generating production code.
let isProduction = args.type === 'production';

/*============================================================
=>                          Server
============================================================*/
gulp.task('server', () => {
  console.log(
    '------------------>>>> firing server  <<<<-----------------------'
  );
  gulpPlugins.connect.server(serverConfig);

  console.log(
    'Started connect web server on http://nuswhispers.local:' +
      serverConfig.port +
      '.'
  );
  opn('http://nuswhispers.local:' + serverConfig.port);
});

gulp.task('tasks', gulpPlugins.taskListing);

/*============================================================
=                          JS-HINT                          =
============================================================*/

gulp.task('js:hint', () => {
  console.log('-------------------------------------------------- JS - HINT');
  return gulp
    .src([
      SETTINGS.src.js + 'app.js',
      '!' + SETTINGS.src.js + 'plugins/*.js',
      SETTINGS.src.js + '**/*.js',
      'resources/js/*.js',
      'gulpfile.js',
    ])
    .pipe(gulpPlugins.jshint(hintOptions))
    .pipe(gulpPlugins.jshint.reporter(stylish));
});

/*============================================================
=                          Concat                           =
============================================================*/

gulp.task('concat:bower', () => {
  console.log(
    '-------------------------------------------------- CONCAT :bower'
  );

  const jsFilter = gulpPlugins.filter('**/*.js', { restore: true });
  const cssFilter = gulpPlugins.filter('**/*.css', { restore: true });
  const assetsFilter = gulpPlugins.filter(
    ['!**/*.js', '!**/*.css', '!**/*.scss'],
    {
      restore: true,
    }
  );

  return gulp
    .src(bowerFiles(bowerConfig), { base: SETTINGS.src.bower })
    .pipe(jsFilter)
    .pipe(gulpPlugins.concat('_bower.js'))
    .pipe(gulpPlugins.if(isProduction, gulpPlugins.uglify()))
    .pipe(gulp.dest(SETTINGS.build.bower))
    .pipe(jsFilter.restore)
    .pipe(cssFilter)
    .pipe(gulpPlugins.sass())
    .pipe(
      map(function(file, callback) {
        var relativePath = path.dirname(
          path.relative(path.resolve(SETTINGS.src.bower), file.path)
        );

        // CSS path resolving
        // Taken from https://github.com/enyojs/enyo/blob/master/tools/minifier/minify.js
        const contents = file.contents
          .toString()
          .replace(/url\([^)]*\)/g, function(match) {
            // find the url path, ignore quotes in url string
            const matches = /url\s*\(\s*(('([^']*)')|("([^"]*)")|([^'"]*))\s*\)/.exec(
                match
              ),
              url = matches[3] || matches[5] || matches[6];

            // Don't modify data and http(s) urls
            if (/^data:/.test(url) || /^http(:?s)?:/.test(url)) {
              return 'url(' + url + ')';
            }
            return (
              'url(' +
              path.join(
                path.relative(SETTINGS.build.bower, SETTINGS.build.app),
                'assets/bower',
                relativePath,
                url
              ) +
              ')'
            );
          });
        file.contents = new Buffer.from(contents);

        callback(null, file);
      })
    )
    .pipe(gulpPlugins.concat('_bower.css'))
    .pipe(gulpPlugins.if(isProduction, gulpPlugins.cssnano()))
    .pipe(gulp.dest(SETTINGS.build.bower))
    .pipe(cssFilter.restore)
    .pipe(assetsFilter)
    .pipe(gulpPlugins.if(isProduction, gulpPlugins.cssnano()))
    .pipe(gulp.dest(SETTINGS.build.bower))
    .pipe(assetsFilter.restore)
    .pipe(gulpPlugins.connect.reload());
});

gulp.task(
  'concat:js',
  gulp.series('js:hint', () => {
    console.log(
      '-------------------------------------------------- CONCAT :js'
    );
    return gulp
      .src([
        SETTINGS.src.js + 'plugins/*.js',
        SETTINGS.src.js + 'app.js',
        SETTINGS.src.js + '*.js',
        SETTINGS.src.js + '**/*.js',
      ])
      .pipe(
        gulpPlugins.if(
          isProduction,
          gulpPlugins.preprocess({
            context: { NODE_ENV: 'production', DEBUG: false },
          })
        )
      )
      .pipe(
        gulpPlugins.if(
          !isProduction,
          gulpPlugins.preprocess({
            context: { NODE_ENV: 'development', DEBUG: true },
          })
        )
      )
      .pipe(gulpPlugins.concat('all.js'))
      .pipe(gulpPlugins.if(isProduction, gulpPlugins.uglify({ mangle: false })))
      .pipe(gulp.dest(SETTINGS.build.js))
      .pipe(gulpPlugins.connect.reload());
  })
);

gulp.task('convert:scss', () => {
  console.log(
    '-------------------------------------------------- COVERT - scss'
  );

  // Callback to show sass error
  const showError = err => {
    console.log(
      errorLog(
        '\n SASS file has error clear it to see changes, see below log ------------->>> \n'
      )
    );
    console.log(errorLog(err));
  };

  return gulp
    .src([SETTINGS.src.css + 'application.scss'])
    .pipe(
      gulpPlugins.sass({ includePaths: [SETTINGS.src.css], onError: showError })
    )
    .pipe(gulp.dest(SETTINGS.scss))
    .pipe(gulpPlugins.connect.reload());
});

gulp.task(
  'concat:css',
  gulp.series('convert:scss', () => {
    console.log(
      '-------------------------------------------------- CONCAT :css '
    );
    return gulp
      .src([
        SETTINGS.src.css + 'fonts.css',
        SETTINGS.scss + 'application.css',
        SETTINGS.src.css + '*.css',
      ])
      .pipe(gulpPlugins.concat('styles.css'))
      .pipe(gulpPlugins.if(isProduction, gulpPlugins.cssnano()))
      .pipe(gulp.dest(SETTINGS.build.css))
      .pipe(gulpPlugins.connect.reload());
  })
);

gulp.task('concat', gulp.series('concat:bower', 'concat:js', 'concat:css'));

/*============================================================
=                          Minify                           =
============================================================*/

gulp.task('image:min', () => {
  return gulp
    .src(SETTINGS.src.images + '**')
    .pipe(gulpPlugins.imagemin())
    .pipe(gulp.dest(SETTINGS.build.images))
    .pipe(gulpPlugins.connect.reload());
});

/*============================================================
=                           Copy                            =
============================================================*/

gulp.task('copy:html', () => {
  console.log('-------------------------------------------------- COPY :html');
  return gulp
    .src([
      SETTINGS.src.templates + '*.html',
      SETTINGS.src.templates + '**/*.html',
    ])
    .pipe(
      gulpPlugins.if(
        isProduction,
        gulpPlugins.preprocess({
          context: { NODE_ENV: 'production', DEBUG: false },
        })
      )
    )
    .pipe(
      gulpPlugins.if(
        !isProduction,
        gulpPlugins.preprocess({
          context: { NODE_ENV: 'development', DEBUG: true },
        })
      )
    )
    .pipe(
      gulpPlugins.if(
        isProduction,
        gulpPlugins.htmlmin({ collapseWhitespace: true })
      )
    )
    .pipe(gulp.dest(SETTINGS.build.templates))
    .pipe(gulpPlugins.connect.reload());
});

gulp.task('copy:html:root', () => {
  console.log(
    '-------------------------------------------------- COPY :html:root'
  );
  return gulp
    .src(SETTINGS.src.app + '*.html')
    .pipe(
      gulpPlugins.if(
        isProduction,
        gulpPlugins.preprocess({
          context: { NODE_ENV: 'production', DEBUG: false },
        })
      )
    )
    .pipe(
      gulpPlugins.if(
        !isProduction,
        gulpPlugins.preprocess({
          context: { NODE_ENV: 'development', DEBUG: true },
        })
      )
    )
    .pipe(
      gulpPlugins.if(
        isProduction,
        gulpPlugins.htmlmin({ collapseWhitespace: true })
      )
    )
    .pipe(gulp.dest(SETTINGS.build.app))
    .pipe(gulpPlugins.connect.reload());
});

gulp.task('copy:images', () => {
  console.log(
    '-------------------------------------------------- COPY :images'
  );
  return gulp
    .src([SETTINGS.src.images + '*.*', SETTINGS.src.images + '**/*.*'])
    .pipe(gulp.dest(SETTINGS.build.images));
});

gulp.task('copy:fonts', (done) => {
  console.log('-------------------------------------------------- COPY :fonts');

  // Bootstrap
  gulp
    .src(
      SETTINGS.src.bower + 'bootstrap-sass-official/assets/fonts/bootstrap/**.*'
    )
    .pipe(gulp.dest(SETTINGS.build.bower + 'bootstrap/assets/fonts'));

  // Typicons
  gulp
    .src(SETTINGS.src.bower + 'typicons/src/font/**.*')
    .pipe(gulp.dest(SETTINGS.build.bower + 'typicons/src/font'));

  gulp
    .src([SETTINGS.src.fonts + '*', SETTINGS.src.fonts + '**/*'])
    .pipe(gulp.dest(SETTINGS.build.fonts));

  done();
});

gulp.task(
  'copy',
  gulp.series('copy:html', 'copy:images', 'copy:fonts', 'copy:html:root')
);

/*=========================================================================================================
=                                               Watch

    Incase the watch fails due to limited number of watches available on your sysmtem, the execute this
    command on terminal

    $ echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p
=========================================================================================================*/

gulp.task('watch', () => {
  console.log('watching all the files.....');

  let watchedFiles = [];

  watchedFiles.push(
    gulp.watch(
      [SETTINGS.src.css + '*.css', SETTINGS.src.css + '**/*.css'],
      { interval: 500 },
      ['concat:css']
    )
  );

  watchedFiles.push(
    gulp.watch(
      [SETTINGS.src.css + '*.scss', SETTINGS.src.css + '**/*.scss'],
      { interval: 500 },
      ['concat:css']
    )
  );

  watchedFiles.push(
    gulp.watch(
      [SETTINGS.src.js + '*.js', SETTINGS.src.js + '**/*.js'],
      { interval: 500 },
      ['concat:js']
    )
  );

  watchedFiles.push(
    gulp.watch([SETTINGS.src.app + '*.html'], { interval: 500 }, [
      'copy:html:root',
    ])
  );

  watchedFiles.push(
    gulp.watch(
      [SETTINGS.src.images + '*.*', SETTINGS.src.images + '**/*.*'],
      { interval: 500 },
      ['copy:images']
    )
  );

  watchedFiles.push(
    gulp.watch(
      [SETTINGS.src.fonts + '*.*', SETTINGS.src.fonts + '**/*.*'],
      { interval: 500 },
      ['copy:fonts']
    )
  );

  watchedFiles.push(
    gulp.watch(
      [SETTINGS.src.bower + '*.js', SETTINGS.src.bower + '**/*.js'],
      { interval: 500 },
      ['concat:bower']
    )
  );

  watchedFiles.push(
    gulp.watch(
      [SETTINGS.src.templates + '*.html', SETTINGS.src.templates + '**/*.html'],
      { interval: 500 },
      ['copy:html']
    )
  );

  watchedFiles.push(
    gulp.watch(['resources/js/*.js'], { interval: 500 }, ['concat:js'])
  );

  // Just to add log messages on Terminal, in case any file is changed
  const onChange = (event) => {
    if (event.type === 'deleted') {
      runSequence('clean');
      setTimeout(() => {
        runSequence('copy', 'concat', 'watch');
      }, 500);
    }
    console.log(
      changeLog(
        '-------------------------------------------------->>>> File ' +
          event.path +
          ' was ------->>>> ' +
          event.type
      )
    );
  };

  watchedFiles.forEach(function(watchedFile) {
    watchedFile.on('change', onChange);
  });
});

/*============================================================
=                             Clean                          =
============================================================*/

const cleanFiles = function(files, logMessage, done) {
  console.log(
    '-------------------------------------------------- CLEAN :' + logMessage
  );
  del(files).then(function(paths) {
    console.log('Deleted files and folders: \n', paths.join('\n'));
    done();
  });
};

gulp.task('clean', (done) => {
  cleanFiles([SETTINGS.build.app], 'all files', done);
});

gulp.task('clean:css', (done) => {
  cleanFiles([SETTINGS.build.css], 'css', done);
});

gulp.task('clean:js', (done) => {
  cleanFiles([SETTINGS.build.js], 'js', done);
});

gulp.task('clean:html', (done) => {
  cleanFiles([SETTINGS.build.templates], 'html', done);
});

gulp.task('clean:images', (done) => {
  cleanFiles([SETTINGS.build.images], 'images', done);
});

gulp.task('clean:fonts', (done) => {
  cleanFiles(
    [SETTINGS.build.fonts + '*.*', SETTINGS.build.fonts + '**/*.*'],
    'fonts',
    done
  );
});

gulp.task('clean:zip', (done) => {
  cleanFiles(['zip/**/*', '!zip/build-*.zip'], 'zip', done);
});

/*============================================================
=                             Zip                          =
============================================================*/

gulp.task('zip', () => {
  gulp
    .src([SETTINGS.build.app + '*', SETTINGS.build.app + '**/*'])
    .pipe(gulpPlugins.zip('build-' + new Date() + '.zip'))
    .pipe(gulp.dest('./zip/'));

  setTimeout(() => {
    runSequence('clean:zip');
  }, 500); // wait for file creation
});

/*============================================================
=                             Start                          =
============================================================*/

gulp.task('build', gulp.series((done) => {
  console.log(
    hintLog(
      '-------------------------------------------------- BUILD - Development Mode'
    )
  );
  done();
}, 'copy', 'concat', 'watch'))

gulp.task('build:prod', gulp.series((done) => {
  console.log(
    hintLog(
      '-------------------------------------------------- BUILD - Production Mode'
    )
  );
  isProduction = true;
  done();
}, 'copy', 'concat'));

gulp.task('default', gulp.series('build'));

// Just in case you are too lazy to type: $ gulp --type production
gulp.task('prod', gulp.series('build:prod'));
