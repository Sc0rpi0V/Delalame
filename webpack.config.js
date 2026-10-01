const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'development');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/js/app.js')
    .addEntry('gallery', './assets/js/gallery.js')
    .addEntry('map', './assets/js/map.js')
    .addEntry('admin-quill', './assets/js/admin-quill.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // Toujours actif (même en dev) : Nginx sert les JS/CSS avec un cache "immutable" 1 an,
    // il faut donc un nom de fichier qui change à chaque build pour éviter de servir du JS/CSS périmé.
    .enableVersioning(true)
    .enablePostCssLoader()
    .configureCssLoader((config) => {
        config.url = false;
    })
;

module.exports = Encore.getWebpackConfig();
