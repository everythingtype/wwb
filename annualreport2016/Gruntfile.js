module.exports = function(grunt) {
    require('jit-grunt')(grunt);
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-webpack');
    var webpack = require("webpack");
    var webpackConfig = require("./webpack.config.js");
    grunt.initConfig({
        //see https://github.com/webpack/webpack-with-common-libs/blob/master/package.json
        webpack: {
            options: webpackConfig,
            "build-dev": {
                // devtool: "sourcemap",
                //debug: true
            }
        },
        concat: {
            basic: {
                src: [
                    'node_modules/jquery/jquery.min.js'
                    ,'node_modules/bootstrap/dist/js/bootstrap.min.js'
                    ,'node_modules/smartmenus/src/jquery.smartmenus.js'
                    ,'node_modules/smartmenus-bootstrap/jquery.smartmenus.bootstrap.min.js'
                    ,'node_modules/jasny-bootstrap/dist/js/jasny-bootstrap.min.js'
                    ,'node_modules/jquery.scrollto/jquery.scrollTo.min.js'
                    ,'node_modules/jquery.localscroll/jquery.localScroll.min.js'
                    ,'node_modules/waypoints/lib/jquery.waypoints.js'
                    ,'node_modules/leaflet/dist/leaflet.js'
                    // run git clone https://github.com/erictheise/rrose.git
                    ,'node_modules/rrose/dist/leaflet.rrose-src.js'
                    ,'assets/js/custom/dist/custom.js'
                ],
                dest: 'assets/js/dist/dist.js'
            },
            extras: {
                src: ['node_modules/html5shiv/dist/html5shiv.min.js'
                    , 'node_modules/respond.js/dest/respond.min.js'
                ],
                dest: 'assets/js/dist/ie.js',
            }
        },
        compass: {
            compile: {
                options: {
                    sassDir: "assets/sass/",
                    cssDir: "assets/dist/",
                    outputStyle: "compact"
                }//options
            }//dev
        }, //compass
        less: {
            development: {
                options: {
                    compress: false,
                    yuicompress: true,
                    optimization: 2,
                    dumpLineNumbers: "comments",
                    sourceMap: false
                },
                files: {
                    "./assets/css/themes/default/style.css": "./assets/less/themes/default/style.less", // destination file and source file
                    //describe every additional style, in this case an additional css theme
                    //"./assets/css/themes/flatly/style.css": "./assets/less/themes/flatly/style.less", // destination file and source file
                }
            }
        },
        watch: {
            options: {
                //livereload: true,
            },
            sass: {
                //files: ['css_unversioned/sass/**/*.scss'],
                files: ['./assets/sass/**/*.scss'],
                tasks: ['compass:compile']
            }, //sass
            less: {
                files: ['./assets/less/**/*.less'], // which files to watch
                tasks: ['less'],
                options: {
                    nospawn: true
                }
            },
            js: {
                files: ['./assets/js/custom/compile/**/*.js'],
                tasks: ["webpack:build-dev","concat:basic","concat:extras"],
                // tasks: ["webpack:build-dev","webpack-dev-server"],
                options: {
                    spawn: false,
                }
            }
        }
    });
};