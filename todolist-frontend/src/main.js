import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./services/store";


import axios from "axios";
import secureStorage from "./utils/secureStorage"; // import relativo

// Bootstrap
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

// Configuração da URL base da API
axios.defaults.baseURL = process.env.VUE_APP_API_URL || "http://localhost:8000/api";

// Se houver token salvo, adiciona Authorization automaticamente
const token = secureStorage.getItem("token");
if (token) {
  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}

// Cria a aplicação Vue
const app = createApp(App);

// Vuex e Router
app.use(store);
app.use(router);

// Monta a aplicação
app.mount("#app");

console.log("Aplicação Vue inicializada com sucesso!");
