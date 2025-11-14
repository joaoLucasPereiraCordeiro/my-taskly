const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "./", // ✅ caminhos relativos
  outputDir: "dist",
});
