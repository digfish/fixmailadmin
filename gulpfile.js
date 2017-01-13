const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

elixir((mix) => {
    mix.sass('app.scss')
       .webpack('app.js')
       .scripts([
       	'../../../node_modules/mustache/mustache.js']);
       	//'bower-components/jsrender/jsrender.js'
       //	],'public/js/app.js');
});


/*elixir((mix) => {
	mix.sass('app.scss')
		.browserify('app.js');
})*/