const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "/", // raiz do site
  outputDir: "dist", // pasta de build
  assetsDir: "assets", // arquivos JS/CSS/Imagens dentro de /assets
  productionSourceMap: false,
});
