import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import axios from "axios";
import secureStorage from "@/utils/secureStorage"; // 🔐 importa o armazenamento seguro

import "bootstrap/dist/css/bootstrap.min.css";
import 'bootstrap/dist/js/bootstrap.bundle.min.js' // <— necessário para Offcanvas, Dropdowns etc.

// 🔧 Define a URL base da sua API Laravel
axios.defaults.baseURL = import.meta.env.VITE_API_URL;

// 🔐 Se houver token salvo, define o Authorization automaticamente
const token = secureStorage.getItem("token");
if (token) {
  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}

const app = createApp(App);

app.use(store);
app.use(router);

app.mount("#app");
