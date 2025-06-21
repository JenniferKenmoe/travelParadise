const Encore = require("@symfony/webpack-encore");

Encore.setOutputPath("public/build/")
  .setPublicPath("/build")
  .addEntry("app", "./assets/app.js")
  .addStyleEntry("easyadmin-custom", "./public/assets/easyadmin-custom.css")
  .enableSourceMaps(!Encore.isProduction())
  .cleanupOutputBeforeBuild()
  .enableVersioning(Encore.isProduction())
  .enableSingleRuntimeChunk(); // ✅ Ajout obligatoire

module.exports = Encore.getWebpackConfig();
