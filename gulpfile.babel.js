'use strict';

import gulp from 'gulp';
import cleanCss from 'gulp-clean-css';
import sourcemaps from 'gulp-sourcemaps';
import zip from 'gulp-zip';
import uglify from 'gulp-uglify';
import del from 'del';
import babel from 'gulp-babel';
import imagemin from 'gulp-imagemin';

gulp.task('styles', () => {
    return gulp.src('src/css/**/*.css')
        .pipe(sourcemaps.init())
        .pipe(cleanCss())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('build/css/'));
});

gulp.task('php', () => {
    return gulp.src('src/**/*.php')
        .pipe(gulp.dest('build'));
});

gulp.task('scripts', () => {
    return gulp.src('src/js/**/*.js')
        .pipe(babel({
            presets: ['es2015']
        }))
        .pipe(uglify())
        .pipe(gulp.dest('build/js/'));
});

gulp.task('images', () => {
    return gulp.src('src/images/**/*.+(png|jpg|gif|svg)')
        .pipe(imagemin())
        .pipe(gulp.dest('build/images/'));
});

gulp.task('clean', function () {
    return del.sync(['build']);
});

gulp.task('zip', function () {
    return gulp.src('build/**/*.*')
        .pipe(zip('web-pagination.zip'))
        .pipe(gulp.dest('build'))
});

gulp.task('default', ['clean', 'php', 'styles', 'scripts'], () => {
    console.log('Generating zip files ....')
    gulp.start('zip');
    console.log('Building task complete');
});