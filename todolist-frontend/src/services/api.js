import axios from "axios";
import secureStorage from "@/utils/secureStorage";

// URL base usando Vue CLI environment variable
const baseURL = process.env.VUE_APP_API_URL || "http://localhost:8000/api";

if (!baseURL) {
  console.error("VUE_APP_API_URL não está definida!");
}

const api = axios.create({
  baseURL: baseURL,
});

// Interceptor para adicionar token automaticamente
api.interceptors.request.use((config) => {
  const token = secureStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
