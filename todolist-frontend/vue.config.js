const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
  // Garante que dependências modernas sejam transpileadas para compatibilidade
  transpileDependencies: true,

  // PUBLIC PATH: define a URL base para buscar os arquivos JS, CSS e assets
  // "/" significa raiz do domínio, garantindo que o navegador encontre os arquivos no deploy
  publicPath: "/",

  // OUTPUT DIR: pasta onde o build de produção será gerado
  // O Vercel vai servir essa pasta como raiz do site
  outputDir: "dist",

  // Configure a geração de source maps para produção (opcional, pode ajudar a debugar)
  productionSourceMap: false,

  // Configure assetsDir para separar assets estáticos dentro do dist (opcional)
  assetsDir: "assets",
});
