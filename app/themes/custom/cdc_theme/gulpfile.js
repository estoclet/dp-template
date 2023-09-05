/**
 * Module Themer KI v2.1.2
 * SASS+ / LESS+ / JS?
 * Syntax Javascript ES 6
 */
'use strict';
const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const fs = require('fs');
const path = require('path');
const chalk = require('chalk');
const through2 = require('through2');
const log = require('fancy-log');
const lazypipe = require('lazypipe');
const mergeStream = require('merge-stream');
const currentVersion = require('node-version');

/**
 * ---------------------------------------
 * Configuration
 * ---------------------------------------
 */
let configFile = require('./projects.json');
let _config = configFile.config,
    _projects = configFile.projects;

/**
 * ---------------------------------------
 * Functions utils
 * ---------------------------------------
 */

/**
 * Files preparation & Lazy Pipes
 * Gulp mixin task
 */
let tools = {
    createFilePathList(srcDir, fileNamesArray) {
        let filePathList = [];
        fileNamesArray.forEach((fileName) => {
            const filePath = path.join(srcDir, fileName);
            if (fs.existsSync(filePath)) {
                filePathList.push(filePath);
            } else {
                log(chalk.red('   Oups fichier introuvable...'), filePath);
            }
        });
        return filePathList;
    },
    createProjectsArray(projects) {
        const _this = this;

        projects.forEach((project) => {
            log('  Project Found:', chalk.green(project.name ? project.name : 'n/a'), 'in', chalk.green(project.path), chalk.reset(' '));
            project.filePathList = _this.createFilePathList((path.join(project.path, project.dir.src)), project.files);
        });

        if (projects.length)
            log('-- Config OK! Found', projects.length, 'project(s)');
        else
            log.warn('-- Configuration empty...');
        log(); // Empty line to keep aeration
        return projects;
    },
    lintSassPipelineBuilder(compiler) {
        const sassLintEnabled = (_config.lint.sass && compiler === 'sass');
        return lazypipe()
            .pipe(() => {
                return $.if(sassLintEnabled, $.sassLint())
            })
            .pipe(() => {
                return $.if(sassLintEnabled, $.sassLint.format())
            })
            .pipe(() => {
                return $.if((sassLintEnabled && _config.lint.failOnError), $.sassLint.failOnError())
            });
    },
    cssCompilerPipelineBuilder(compiler, sourcemap, minify) {
        return lazypipe()
            .pipe(() => {
                return $.if(sourcemap, $.sourcemaps.init())
            })
            .pipe(() => {
                return $.if((compiler === 'less'), $.less());
            })
            .pipe(() => {
                return $.if((compiler === 'sass'), $.sass({ precision: 10 }));
            })
            .pipe(() => {
                return $.autoprefixer()
            })
            .pipe(() => {
                return $.if(minify, $.cleanCss())
            })
            .pipe(() => {
                return $.if(sourcemap, $.sourcemaps.write('.'))
            });
    },
    printTaskState(project, type) {
        return through2({ objectMode: true }, function (chunk, enc, callback) {
            // Print project and file involved
            log(chalk.blue.bold('[Compiling] \t'), chalk.green(project.name ? project.name : 'n/a') + chalk.magenta(' =>', path.join(chunk.base, chunk.basename)) + chalk.reset(' '));

            // Transform default outputs
            this.push(chunk);
            callback()
        })
    },
    printLintTaskState(project, type) {
        return through2({ objectMode: true }, function (chunk, enc, callback) {
            // Print project and file involved
            log(chalk.red.bold('[Linting] \t'), chalk.green(project.name ? project.name : 'n/a') + chalk.grey(' =>', path.join(chunk.base, chunk.basename)) + chalk.reset(' '));

            // Transform default outputs
            this.push(chunk);
            callback()
        })
    },
    prepareProjectPipes(projects, sourcemap, minify) {
        const _this = this;
        let mainStream = mergeStream();

        projects.forEach((project) => {
            // Linter
            if (project.lint) {
                const lintStream = gulp.src(
                        path.join(project.path, project.dir.src) + '/**/*' + _config.extension[project.compiler],
                        { base: (path.join(project.path, project.dir.src)),
                        allowEmpty: true })
                    .pipe(_this.printLintTaskState(project)) // print project name and file + path
                    .pipe(_this.lintSassPipelineBuilder(project.compiler)()); // Compilation routine

                // Add in task wrapper
                mainStream.add(lintStream);
            }

            // Compiler
            const tmpStream = gulp.src(project.filePathList, { base: (path.join(project.path, project.dir.src)), allowEmpty: true })
                .pipe(_this.printTaskState(project)) // print project name and file + path
                .pipe(_this.cssCompilerPipelineBuilder(project.compiler, sourcemap, minify)()) // Compilation routine
                .pipe(gulp.dest(path.join(project.path, project.dir.dest)));

            // Add in task wrapper
            mainStream.add(tmpStream);
        });

        return mainStream;
    }
};

/**
 * Start / Init function
 */

log.info(chalk.green('#########################################'));
log.info(chalk.green('##    Module Themer KI SASS & LESS     ##'));
log.info(chalk.green('#########################################'));
log.info('This module version ', configFile.version);
if (currentVersion.major < 10 || currentVersion.major > 12) {
    log.info('Node Version', process.version, chalk.red.bold('Not supported'), chalk.reset(' '));
    log.warn(chalk.reset('-----------------------'));
    log.warn(chalk.red.bold('!! Your Node version is not supported !!'), chalk.reset(' '));
    log.warn(chalk.red.bold('(supported v10 up to v12)'), chalk.reset(' '));
    log.warn(chalk.red.bold('Proceed with caution !'), chalk.reset(' '));
    log.warn(chalk.reset('-----------------------'));
} else {
    log.info('Node Version', process.version, chalk.green.bold('OK'), chalk.reset(' '));
}
log.info(chalk.green('## Preparation...'));

// Prepares projects and there files
let projectsPrepared = tools.createProjectsArray(_projects);

/**
 * ---------------------------------------
 * GULP TASKS
 * ---------------------------------------
 */

/**
 * Compiler tous les projets en mode de dev
 * Minification désactivé
 * SourceMap activé
 *
 * $> gulp dev
 */
gulp.task('dev', () => {
    return tools.prepareProjectPipes(projectsPrepared, true, false)
});

/**
 * Compiler tous les projets en mode de dev
 * Minification désactivé
 * SourceMap désactivé
 *
 * $> gulp dev-sans-sourcemap
 */
gulp.task('dev-sans-sourcemap', () => {
    return tools.prepareProjectPipes(projectsPrepared, false, false)
});

/**
 * Compiler tous les projets en mode de production
 * Minification activé
 * SourceMap désactivé
 *
 * $> gulp prod
 */
gulp.task('prod', () => {
    return tools.prepareProjectPipes(projectsPrepared, false, true)
});

/**
 * Lance le watcher avec la tâche dev
 *
 * $> gulp watcher
 */
gulp.task('watcher-dev', () => {
    // Compiler config
    let sourcemap = true,
        minify = false;


    projectsPrepared.forEach((project) => {
        gulp.watch(
            path.join(project.path, project.dir.src, "**/*" + _config.extension[project.compiler]).replace(/\\/g, '/'),
            { usePolling: _config.partageReseau },
            function compilation () {
                return tools.prepareProjectPipes([project], sourcemap, minify);
            });
    });
});

/**
 * Activation du mode watcher
 * Adapter sur un environnement de dev
 * Minification désactivé
 * SourceMap activé
 *
 * $> gulp
 */
gulp.task('default', gulp.series('dev', 'watcher-dev'));
