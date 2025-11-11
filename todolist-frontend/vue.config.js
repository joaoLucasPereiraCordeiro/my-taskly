const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
  transpileDependencies: true,

  // Corrige rotas e carregamento no Vercel
  publicPath: "./",

  // Garante compatibilidade de CORS e cookies se necessário
  devServer: {
    allowedHosts: "all",
  },
});
