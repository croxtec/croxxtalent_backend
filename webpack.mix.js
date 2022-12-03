const mix = require('laravel-mix');

// In order to use your .env variables in webpack.mix.js

require('dotenv').config();  

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

// Copy Images
mix.copy('resources/images/*', 'public/images');
     
if (mix.inProduction()) {
    mix.version();
    // mix.version('public/images/');
}


// mix.options({
//     extractVueStyles: true,
// });

// mix.webpackConfig({
//     optimization: {
//         splitChunks: {
//             chunks: 'async',   //all          
//         }
//     },
//     output: {
//         filename: '[name].js',
//         chunkFilename: '[id].js',
//     },
// });
