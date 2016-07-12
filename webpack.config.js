var webpack = require('webpack'),
    path = require('path'),
    autoprefixer = require('autoprefixer'),
    ExtractTextPlugin = require("extract-text-webpack-plugin");

module.exports = {
    context: __dirname,
    debug: true,
    entry: {
        app: './resources/app/scripts/index.js',
        dash: './resources/dash/scripts/index.js'
    },
    output: {
        path: path.join(__dirname, 'public'),
        filename: '/scripts/[name].js'
    },
    module: {
        loaders: [{
            test: /\.html$/,
            loader: "html"
        }, {
            test: /\.js$/,
            loader: 'babel',
            exclude: '/node_modules/',
            query: {
                presets: ['es2015']
            }
        }, {
            test: /\.scss$/,
            loader: ExtractTextPlugin.extract(['css-loader?-autoprefixer,safe', 'postcss-loader', 'sass-loader'])
        }, {
            test: /\.css$/,
            loader: 'style-loader!css-loader'
        }, {
            test: /\.woff(\?v=\d+\.\d+\.\d+)?$/,
            loader: "url-loader?limit=10000&mimetype=application/font-woff&name=/stylesheets/[hash].[ext]"
        }, {
            test: /\.woff2(\?v=\d+\.\d+\.\d+)?$/,
            loader: "url-loader?limit=10000&mimetype=application/font-woff&name=/stylesheets/[hash].[ext]"
        }, {
            test: /\.ttf(\?v=\d+\.\d+\.\d+)?$/,
            loader: "url-loader?limit=10000&mimetype=application/octet-stream&name=/stylesheets/[hash].[ext]"
        }, {
            test: /\.eot(\?v=\d+\.\d+\.\d+)?$/,
            loader: "file-loader?name=/stylesheets/[hash].[ext]"
        }, {
            test: /\.svg(\?v=\d+\.\d+\.\d+)?$/,
            loader: "url-loader?limit=10000&mimetype=image/svg+xml&name=/stylesheets/[hash].[ext]"
        }]
    },
    postcss: [
        autoprefixer({browsers: ["last 2 version", "> 10%", "ie 8"]})
    ],
    plugins: [
        new ExtractTextPlugin('/stylesheets/[name].css')
    ]
};