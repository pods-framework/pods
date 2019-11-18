var gulp = require('gulp');
var path = require('path');
var filesystem = require('fs');
var wpPot = require('gulp-wp-pot');
var gettext = require('gulp-gettext');
var sort = require('gulp-sort');
var pofill = require('gulp-pofill');
var rename = require('gulp-rename');
var clean = require('gulp-clean');

var languagesFolder = './languages/';

var options = require('./transifex-config.json');

function getFolders(dir) {
    return filesystem.readdirSync(dir)
        .filter(function (file) {
            return filesystem.statSync(path.join(dir, file)).isDirectory();
        });
}

var transifex = require('gulp-transifex').createClient(options);

// Create POT out of i18n.php.
gulp.task('prepare-source', function () {
    gulp.src('**/*.php')
        .pipe(sort())
        .pipe(wpPot({
            destFile        : 'freemius.pot',
            package         : 'freemius',
            bugReport       : 'https://github.com/Freemius/wordpress-sdk/issues',
            lastTranslator  : 'Vova Feldman <vova@freemius.com>',
            team            : 'Freemius Team <admin@freemius.com>',
            /*gettextMethods: {
                instances: ['this', '_fs'],
                methods: [
                    'get_text_inline'
                ]
            },*/
            gettextFunctions: [
                {name: 'get_text_inline'},

                {name: 'fs_text_inline'},
                {name: 'fs_echo_inline'},
                {name: 'fs_esc_js_inline'},
                {name: 'fs_esc_attr_inline'},
                {name: 'fs_esc_attr_echo_inline'},
                {name: 'fs_esc_html_inline'},
                {name: 'fs_esc_html_echo_inline'},

                {name: 'get_text_x_inline', context: 2},
                {name: 'fs_text_x_inline', context: 2},
                {name: 'fs_echo_x_inline', context: 2},
                {name: 'fs_esc_attr_x_inline', context: 2},
                {name: 'fs_esc_js_x_inline', context: 2},
                {name: 'fs_esc_js_echo_x_inline', context: 2},
                {name: 'fs_esc_html_x_inline', context: 2},
                {name: 'fs_esc_html_echo_x_inline', context: 2}
                /*,


                {name: '_fs_text'},
                {name: '_fs_x', context: 2},
                {name: '_fs_echo'},
                {name: '_fs_esc_attr'},
                {name: '_fs_esc_attr_echo'},
                {name: '_fs_esc_html'},
                {name: '_fs_esc_html_echo'},
                {name: '_fs_ex', context: 2},
                {name: '_fs_esc_attr_x', context: 2},
                {name: '_fs_esc_html_x', context: 2},

                {name: '_fs_n', plural: 2},
                {name: '_fs_n_noop', plural: 2},
                {name: '_fs_nx', plural: 2, context: 4},
                {name: '_fs_nx_noop', plural: 2, context: 3}*/
            ]
        }))
        .pipe(gulp.dest(languagesFolder + 'freemius.pot'));

    // Create English PO out of the POT.
    return gulp.src(languagesFolder + 'freemius.pot')
        .pipe(pofill({
            items: function (item) {
                // If msgstr is empty, use identity translation
                if (!item.msgstr.length) {
                    item.msgstr = [''];
                }
                if (!item.msgstr[0]) {
                    item.msgstr[0] = item.msgid;
                }
                return item;
            }
        }))
        .pipe(rename('freemius-en.po'))
        .pipe(gulp.dest(languagesFolder));
});

// Push updated po resource to transifex.
gulp.task('update-transifex', ['prepare-source'], function () {
    return gulp.src(languagesFolder + 'freemius-en.po')
        .pipe(transifex.pushResource());
});

// Download latest *.po translations.
gulp.task('download-translations', ['update-transifex'], function () {
    return gulp.src(languagesFolder + 'freemius-en.po')
        .pipe(transifex.pullResource());
});

// Move translations to languages root.
gulp.task('prepare-translations', ['download-translations'], function () {
    var folders = getFolders(languagesFolder);

    return folders.map(function (folder) {
        return gulp.src(path.join(languagesFolder, folder, 'freemius-en.po'))
            .pipe(rename('freemius-' + folder + '.po'))
            .pipe(gulp.dest(languagesFolder));
    });
});

// Feel up empty translations with English.
gulp.task('translations-feelup', ['prepare-translations'], function () {
    return gulp.src(languagesFolder + '*.po')
        .pipe(pofill({
            items: function (item) {
                // If msgstr is empty, use identity translation
                if (0 == item.msgstr.length) {
                    item.msgstr = [''];
                }
                if (0 == item.msgstr[0].length) {
//                    item.msgid[0] = item.msgid;
                    item.msgstr[0] = item.msgid;
                }
                return item;
            }
        }))
        .pipe(gulp.dest(languagesFolder));
});

// Cleanup temporary translation folders.
gulp.task('cleanup', ['prepare-translations'], function () {
    var folders = getFolders(languagesFolder);

    return folders.map(function (folder) {
        return gulp.src(path.join(languagesFolder, folder), {read: false})
            .pipe(clean());
    });
});

// Compile *.po to *.mo binaries for usage.
gulp.task('compile-translations', ['translations-feelup'], function () {
    // Compile POs to MOs.
    return gulp.src(languagesFolder + '*.po')
        .pipe(gettext())
        .pipe(gulp.dest(languagesFolder))
});

gulp.task('default', [], function () {
    gulp.run('prepare-source');
    gulp.run('update-transifex');
    gulp.run('download-translations');
    gulp.run('prepare-translations');
    gulp.run('translations-feelup');
    gulp.run('cleanup');
    gulp.run('compile-translations');
});