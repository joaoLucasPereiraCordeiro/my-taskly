const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: process.env.NODE_ENV === "production" ? "/" : "/",
  devServer: {
    allowedHosts: "all",
    historyApiFallback: true, // garante que o Vue Router funcione nas rotas internas
  },
});
