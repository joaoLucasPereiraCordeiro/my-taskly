import axios from "axios";
import secureStorage from "@/utils/secureStorage";

// Define a URL base corretamente
const baseURL = `${process.env.VUE_APP_API_URL || "http://localhost:8000"}/api`;

const api = axios.create({
  baseURL,
  withCredentials: true, 
});

// Interceptor para adicionar o token automaticamente
api.interceptors.request.use((config) => {
  const token = secureStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});


export default api;
