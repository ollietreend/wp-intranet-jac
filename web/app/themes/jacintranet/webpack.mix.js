const mix = require('laravel-mix');

mix.setPublicPath('./dist/');

mix.js(['assets/scripts/main.js', "assets/scripts/legacy.js"], 'scripts/main.js')
  .sass('assets/styles/main.scss', 'styles/main.css')
  .options({
    processCssUrls: false
  })
  .sass('assets/styles/editor-style.scss', 'styles/editor-style.css')
  .sass('assets/styles/print.scss', 'styles/print.css');

if (mix.inProduction()) {
  mix.version();
} else {
  mix.sourceMaps();
}
