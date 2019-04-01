const gulp = require('gulp');
const sass = require('gulp-sass');
const pug = require('gulp-pug');
const csso = require('gulp-csso');
const rename = require('gulp-rename');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');
const changed = require('gulp-changed');
const imagemin = require('gulp-imagemin');
const touch = require('gulp-touch-cmd');
const browserfy = require('browserify');
const vstream = require('vinyl-source-stream');
const vbuffer = require('vinyl-buffer');

// Tasks

gulp.task('html', function (done) {
    let path = 'App/Views';
    let src =  ['Source/html/**/*.pug', '!Source/html/**/_*.pug'];

    gulp.src(src)
        .pipe(pug())
        .pipe(rename(function (path) {
            path.extname = ".php"
        }))
        .pipe(gulp.dest(path))
        .pipe(touch());

    done();
});

gulp.task('sass', function (done) {
    gulp.src('Source/sass/main.sass')
        .pipe(sass())
        .pipe(csso())
        .pipe(rename(function (path) {
            path.basename += ".min";
        }))
        .pipe(gulp.dest('Public/css'))
        .pipe(touch());

    done();
});

gulp.task('js', function (done) {
    browserfy('Source/js/main.js')
        .bundle()
        .pipe(vstream('main.js'))
        .pipe(vbuffer())
        .pipe(babel({
            presets: ['env'],
            compact : true
        }))
        .pipe(uglify())
        .pipe(rename(function (path) {
            path.basename += ".min";
        }))
        .pipe(gulp.dest('Public/js'))
        .pipe(touch());

    done();
});

gulp.task('image', function (done) {
    gulp.src('Source/images/*')
        .pipe(changed('Public/images'))
        .pipe(imagemin())
        .pipe(gulp.dest('Public/images'))
        .pipe(touch());

    done();
});

gulp.task('default', gulp.parallel(['html', 'sass', 'js', 'image']));

// Watchers

gulp.task('watch_html', function() {
    return gulp.watch(['Source/html/**/*.pug'], gulp.series('html'));
});
gulp.task('watch_sass', function() {
    return gulp.watch(['Source/sass/**/*.sass'], gulp.series('sass'));
});
gulp.task('watch_js', function() {
    return gulp.watch(['Source/js/**/*.js'], gulp.series('js'));
});
gulp.task('watch_image', function() {
    return gulp.watch(['Source/images/*.*'], gulp.series('image'));
});

gulp.task('watch', gulp.series(['default', gulp.parallel('watch_html', 'watch_sass', 'watch_js', 'watch_image')]));
