var path = require('path');
var webpack = require('webpack');

module.exports = {
    cache: true,
    entry: {
        //react : './custom-assets/js/react/react.jsx',
        custom : './assets/js/custom/compile/custom.js'
    },
    output: {
        path: path.join(__dirname, "assets/js/custom/dist"),
        publicPath: "assets/js/custom/dist/",
        filename: "[name].js",
        chunkFilename: "[chunkhash].js"
        // path: __dirname, filename: 'bundle.js'
    },
    module: {
        loaders: [
            {
                test: /.jsx?$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                query: {
                    //presets: ['es2015', 'react'],
                    presets: ['es2015'],
                    cacheDirectory: true
                }
            }
        ]
    },
    externals: {
        //don't bundle the 'react' npm package with our bundle.js
        //but get it from a global 'React' variable
        //'react': 'React',
        'jquery': '$',
        'jquery.smartmenus':'SmartMenus'
    },
    plugins: [
        /*
        new webpack.ProvidePlugin({
            // Automtically detect jQuery and $ as free var in modules
            // and inject the jquery library
            // This is required by many jquery plugins
            jQuery: "jquery",
            $: "jquery"
        }),
        */
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('production')
            }
        }),
        //https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
        new webpack.optimize.UglifyJsPlugin({
            output: {
                comments: false
            },
            compress: {
                warnings: false,
                booleans: false,
                unused: false
            }
        })
    ]
};