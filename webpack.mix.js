const mix = require('laravel-mix');

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

mix.copyDirectory('resources/plugins', 'public/plugins');

mix.js('resources/js/app.js', 'public/js')
	.js('resources/js/iframeResizer.js', 'public/js')
	.js('resources/js/iframeResizercontentWindow.js', 'public/js')
	.js('resources/js/widget.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

if (mix.inProduction()) {
    mix.version();
}