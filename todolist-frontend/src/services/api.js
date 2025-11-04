import axios from "axios";
import secureStorage from "@/utils/secureStorage";

// Para Vue CLI, usar process.env.VUE_APP_API_URL
const baseURL = process.env.VUE_APP_API_URL;

if (!baseURL) {
  console.error("VUE_APP_API_URL não está definida!");
}

const api = axios.create({
  baseURL: baseURL,
});

api.interceptors.request.use((config) => {
  const token = secureStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
