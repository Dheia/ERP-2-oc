const path = require('path');
const mix = require('laravel-mix');
const HardSourceWebpackPlugin = require('hard-source-webpack-plugin');
require('vuetifyjs-mix-extension');

mix.options({
    hmrOptions: {
        host: 'xe3.test',  // site's host name
        port: 80,
    }
});

mix.webpackConfig({
    output: {
        chunkFilename: 'js/[name].[chunkhash].js',
        publicPath: mix.config.hmr ? '//xe3.test' : 'themes/management/assets/'
    },
    plugins: [
        new HardSourceWebpackPlugin(),
    ],
    resolve: {
        alias: {
            '~': path.resolve(__dirname, 'src', 'js'),
            '@scss': path.resolve(__dirname, 'src', 'scss'),
            '@images': path.resolve(__dirname, 'src', 'images'),
        }
    },
    // add any webpack dev server config here
    devServer: {
        proxy: {
            host: 'xe3.test',  // host machine ip
            port: 80,
        },
        watchOptions:{
            aggregateTimeout:200,
            poll:5000
        },

    }
});

mix.js('./src/js/app.js', './javascript')
    .sass('./src/scss/app.scss', './css')
    .vuetify('vuetify-loader', './src/scss/variables.scss')
    .setResourceRoot('/themes/management/assets')
    .sourceMaps()
    .browserSync(
        {
            proxy: '192.168.10.10:80',
            host: 'xe3.test',
            // => true = the browse opens a new browser window with every npm run watch startup/reload

            files: [
                "./src/*"
            ],

            open: true,
            notify: true,
            ui: false
        }
    );

if (mix.inProduction()) {
    mix.version()
        .extract([
            'vue',
            'vform',
            'axios',
            'vuex',
            'jquery',
            'popper.js',
            'vue-i18n',
            'vue-meta',
            'js-cookie',
            'vue-router',
            'sweetalert2',
            'vuex-router-sync',
            '@fortawesome/fontawesome',
            '@fortawesome/vue-fontawesome'
        ])
}

