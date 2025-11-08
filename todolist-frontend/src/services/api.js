import axios from "axios";
import secureStorage from "@/utils/secureStorage";

// Define a URL base corretamente
const baseURL = `${process.env.VUE_APP_API_URL || "http://localhost:8000"}/api`;

// Log de depuração opcional
console.log("🔗 API Base URL:", baseURL);

const api = axios.create({
  baseURL,
  withCredentials: true, // <--- ESSENCIAL para Laravel Sanctum
});

// Interceptor para adicionar o token automaticamente
api.interceptors.request.use((config) => {
  const token = secureStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor opcional para tratar erros de rede
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      console.error(
        "❌ Erro na resposta da API:",
        error.response.status,
        error.response.data
      );
    } else if (error.request) {
      console.error("⚠️ Nenhuma resposta do servidor:", error.request);
    } else {
      console.error("Erro ao configurar a requisição:", error.message);
    }
    return Promise.reject(error);
  }
);

export default api;
