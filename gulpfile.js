// generated on 2016-11-16 using generator-webapp 2.3.2
const gulp = require('gulp');
const gulpLoadPlugins = require('gulp-load-plugins');

const del = require('del');
const wiredep = require('wiredep').stream;
const runSequence = require('run-sequence');
const cleanCSS = require('gulp-clean-css');
const jsmin = require('gulp-jsmin');

const wbBuild = require('workbox-build');
const sftp = require('gulp-sftp');

const $ = gulpLoadPlugins();

var dev = true;

gulp.task('styles', () => {
    return gulp.src('src/wp-content/themes/wlinoz/**/*.css')
        .pipe($.plumber())
        .pipe($.sourcemaps.init())
        .pipe($.autoprefixer({ browsers: ['> 1%', 'last 2 versions', 'Firefox ESR'] }))
        .pipe($.sourcemaps.write())
        .pipe(cleanCSS({ compatibility: 'ie8' }))
        .pipe(gulp.dest('build/wp-content/themes/wlinoz'));
});

gulp.task('scripts', () => {
    return gulp.src('src/wp-content/themes/wlinoz/**/*.js')
        .pipe($.plumber())
        .pipe($.sourcemaps.init())
        .pipe(jsmin())
        .pipe(gulp.dest('build/wp-content/themes/wlinoz'));
});

gulp.task('images', () => {
    return gulp.src('src/images/**/*.+(png|jpg|gif|svg)')
        .pipe($.cache($.imagemin()))
        .pipe(gulp.dest('build/images'));
});

gulp.task('php', () => {
    return gulp.src('src/wp-content/themes/wlinoz/**/*.php')
        .pipe($.cache($.imagemin()))
        .pipe(gulp.dest('build/wp-content/themes/wlinoz'));
});

gulp.task('clean', del.bind(null, ['.tmp', 'build']));


gulp.task('build', ['php', 'styles', 'scripts', 'images'], () => {
    return gulp.src('build').pipe($.size({ title: 'build', gzip: true }));
});

gulp.task('send', ()=>{
    return gulp.src('build/wp-content/themes/wlinoz/assets/js/wlinoz.js')
        .pipe(sftp({
            host: '172.20.202.171',
            user: 'web-wlinoz',
            pass: 'wl1//n0z/77!',
            port: 22,
            remotePath: 'html/wp-content/themes/wlinoz/assets/js/'
        }));
});

gulp.task('default', () => {
    return new Promise(resolve => {
        dev = false;
        runSequence(['clean'], 'build', 'bundle-sw','send', resolve);
    });
});

gulp.task('bundle-sw', () => {
    return wbBuild.generateSW({
        cacheId: 'wlinoz',
        globDirectory: './build/',
        globPatterns: ['**\/*.{js,css,jpg,png,svg}'],
        swDest: './build/sw.js',
    })
    .then(() => {
        console.log('Service worker generated.');
    })
    .catch((err) => {
        console.log('[ERROR] This happened: ' + err);
    });
})