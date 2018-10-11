var gulp = require('gulp');
var minifier = require('gulp-minifier');
var gulpClean = require('gulp-clean');
var concat = require('gulp-concat');

function css() {
    return gulp.src([
          'public/template/plugins/bootstrap/css/bootstrap.min.css',
          'public/template/plugins/bootstrap/css/bootstrap-responsive.min.css',
          'public/template/plugins/font-awesome/css/font-awesome.min.css',
          'public/template/css/style-metro.css',
          'public/template/css/style.css',
          'public/template/css/style-responsive.css',
          'public/template/css/themes/default.css',
          'public/template/plugins/uniform/css/uniform.default.css',
          'public/template/plugins/select2/select2_metro.css',
          'public/template/plugins/chosen-bootstrap/chosen/chosen.css',
          'public/template/plugins/data-tables/DT_bootstrap.css',
          'public/template/plugins/bootstrap-datepicker/css/datepicker.css',
          'public/template/plugins/bootstrap-datetimepicker/css/datetimepicker.css',
          'public/template/plugins/bootstrap-timepicker/compiled/timepicker.css',
          'public/template/plugins/bootstrap-daterangepicker/daterangepicker.css',
          'public/template/plugins/bootstrap-toggle-buttons/static/stylesheets/bootstrap-toggle-buttons.css',
          'public/template/plugins/jquery-ui/jquery-ui-1.10.1.custom.min.css',
          'public/template/plugins/jquery-multi-select/css/multi-select-metro.css',
          'public/template/plugins/glyphicons/css/glyphicons.css',
          'public/template/plugins/glyphicons_halflings/css/halflings.css',
          'public/styles/timeline.css',
          'public/styles/app.css',
        ])
        .pipe(minifier({
            minify: true,
            minifyCSS: true,
            collapseWhitespace: true
        }))
        .pipe(concat('style.min.css'))
        .pipe(gulp.dest('public/dist/css'));
}

function js() {
    return gulp.src([
          'public/template/plugins/jquery-1.10.1.min.js',
          'public/template/plugins/jquery-migrate-1.2.1.min.js',
          'public/template/plugins/jquery-ui/jquery-ui-1.10.1.custom.min.js',
          'public/template/plugins/jquery-validation/dist/jquery.validate.min.js',
          'public/template/plugins/bootstrap/js/bootstrap.min.js',
          'public/template/plugins/jquery.cookie.min.js',
          'public/template/plugins/jquery.blockui.min.js',
          'public/template/plugins/uniform/jquery.uniform.min.js',
          'public/template/plugins/select2/select2.js',
          'public/template/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
          'public/template/plugins/bootstrap-daterangepicker/date.js',
          'public/template/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
          'public/template/plugins/bootstrap-daterangepicker/daterangepicker.js',
          'public/template/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
          'public/template/plugins/bootstrap-toggle-buttons/static/js/jquery.toggle.buttons.js',
          'public/template/plugins/data-tables/jquery.dataTables.js',
          'public/template/plugins/data-tables/DT_bootstrap.js',
          'public/template/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js',
          'public/template/plugins/jquery-multi-select/js/jquery.multi-select.js',
          'public/template/plugins/jquery.pulsate.min.js',
          'public/template/plugins/jquery-slimscroll/jquery.slimscroll.min.js',
          'public/template/plugins/ckeditor/ckeditor.js',
          'public/template/scripts/app.js',
          'public/scripts/plugins/highcharts/highcharts.js',
          'public/scripts/plugins/highcharts/modules/exporting.js',
          'public/scripts/plugins/jquery.populate.js',
          'public/scripts/plugins/jquery.maxlength.min.js',
          'public/scripts/plugins/jquery.maskmoney.js',
          'public/scripts/plugins/jquery.dataTables.dateSorting.js',
          'public/scripts/app/general.js',
          'public/scripts/app/message.js',
          'public/scripts/app/form.js',
          'public/scripts/app/portlet.js',
        ])
        .pipe(minifier({
            minify: true,
            minifyJS: true,
            collapseWhitespace: true
        }))
        .pipe(concat('scripts.min.js'))
        .pipe(gulp.dest('public/dist/js'));
}

function clean() {
    return gulp.src([
        'public/dist/js',
        'public/dist/css',
    ], {
        read: false,
        allowEmpty: true
    }).pipe(gulpClean({
        force: true
    }));
}

exports.clean = clean;
exports.css = css;
exports.js = js;

gulp.task('css', css);
gulp.task('js', js);
gulp.task('clean', clean);

gulp.task('default', ['clean', 'css', 'js']);