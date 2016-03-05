var gulp   = require('gulp'),
    less   = require('gulp-less'),
    csso   = require('gulp-minify-css'),
    sm     = require('gulp-sourcemaps'),
    prefix = require('gulp-autoprefixer'),
    npmcss = require("less-plugin-npm-import"),

    browfy = require('gulp-browserify'),
    uglify = require('gulp-uglify'),
    ng     = require('gulp-ng-annotate'),

    live   = require('gulp-livereload'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat');

var files = {
    texts:   ['*/**.html', '*/**.php'],
    styles:  ['styles/style.less'],
    scripts: ['app/main.js']
};

gulp.task('text', function() {
    gulp.src(files.texts)
        .pipe(live());
});

gulp.task('script', function() {

    gulp.src(files.scripts)
        .pipe(browfy({
            debug: true,
            read: false,
            transform: ['babelify']
        }))
        .pipe(rename('main.min.js'))
        .pipe(gulp.dest('app'))
        .pipe(live());

    // gulp.src(files.admin_scripts)
    //     .pipe(concat('admin.min.js'))
    //     .pipe(gulp.dest('./web'))
    //     .pipe(live());
});

gulp.task('script-prod', function() {

    gulp.src(files.scripts)
        .pipe(browfy({
            transform: ['babelify']
        }))
        .pipe(ng())
        .pipe(uglify({
            compress: true,
            mangle: true
        }))
        .pipe(rename('main.min.js'))
        .pipe(gulp.dest('app'));

    // gulp.src(files.admin_scripts)
    //     .pipe(concat('admin.min.js'))
    //     .pipe(gulp.dest('./web'));
});

gulp.task('style', function() {

    gulp.src(files.styles)
        .pipe(sm.init())
            .pipe(less({
                plugins: [new npmcss()]
            }))
            .pipe(csso())
        .pipe(sm.write())
        .pipe(rename('style.min.css'))
        .pipe(gulp.dest('styles'))
        .pipe(live());

});

gulp.task('style-prod', function() {

    gulp.src(files.styles)
        .pipe(less({
            plugins: [new npmcss()]
        }))
        .pipe(prefix())
        .pipe(csso())
        .pipe(rename('style.min.css'))
        .pipe(gulp.dest('styles'));

});

gulp.task('build', ['style', 'script']);
gulp.task('build-prod', ['style-prod', 'script-prod']);

gulp.task('default', ['build'], function() {

    live.listen();
    gulp.watch(files.texts,   ['text']);
    gulp.watch(files.styles,  ['style']);
    gulp.watch(files.scripts, ['script']);
    // gulp.watch(files.admin_scripts, ['script']);

});