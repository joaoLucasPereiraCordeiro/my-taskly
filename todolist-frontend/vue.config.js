const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "./", 
  outputDir: "dist",
  devServer: {
    allowedHosts: "all",
    historyApiFallback: true,
  },
});
