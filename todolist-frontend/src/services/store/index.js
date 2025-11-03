import { createStore } from "vuex";
import secureStorage from "@/utils/secureStorage";
import axios from "axios";

export default createStore({
  state() {
    return {
      user: secureStorage.getItem("user") || null,
      token: secureStorage.getItem("token") || null,
      isAuthenticated: !!secureStorage.getItem("token"),
      tasks: [],
      isLoading: true,
    };
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
      state.isAuthenticated = !!user;
      secureStorage.setItem("user", user);
    },
    setToken(state, token) {
      state.token = token;
      secureStorage.setItem("token", token);
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    },
    setTasks(state, tasks) {
      state.tasks = tasks;
    },
    clearTasks(state) {
      state.tasks = [];
    },
    setLoading(state, value) {
      state.isLoading = value;
    },
    setLoadingTrue(state) {
      state.isLoading = true;
    },
    setLoadingFalse(state) {
      state.isLoading = false;
    },
    logout(state) {
      state.user = null;
      state.token = null;
      state.isAuthenticated = false;
      state.tasks = [];
      secureStorage.removeItem("user");
      secureStorage.removeItem("token");
      delete axios.defaults.headers.common["Authorization"];
    },
  },

  actions: {
    async login({ commit, dispatch }, { email, password }) {
      try {
        commit("clearTasks");
        commit("setUser", null);
        commit("setToken", null);

        const response = await axios.post("/api/login", { email, password });

        const token = response.data.token;
        const user = response.data.user;

        commit("setToken", token);
        commit("setUser", user);

        if (user.role === "user") {
          await dispatch("fetchTasks");
        }
      } catch (error) {
        console.error("Erro no login:", error);
        throw error;
      }
    },

    async fetchTasks({ commit }) {
      try {
        const response = await axios.get("/api/tasks");
        commit("setTasks", response.data);
      } catch (error) {
        console.error("Erro ao buscar tarefas:", error);
        commit("setTasks", []);
      }
    },

    async fetchUser({ commit, state }) {
      if (!state.token) throw new Error("Token ausente");
      try {
        const response = await axios.get("/api/user");
        commit("setUser", response.data);
      } catch (error) {
        console.error("Erro ao buscar usuário:", error);
        throw error;
      }
    },

    async checkAuth({ commit, dispatch, state }) {
      try {
        if (state.token) {
          axios.defaults.headers.common[
            "Authorization"
          ] = `Bearer ${state.token}`;
          await dispatch("fetchUser");
          if (state.user.role === "user") {
            await dispatch("fetchTasks");
          }
        } else {
          commit("logout");
        }
      } catch (error) {
        commit("logout");
      } finally {
        commit("setLoading", false);
      }
    },

    logout({ commit }) {
      commit("logout");
    },
  },
  getters: {
    isAuthenticated: (state) => state.isAuthenticated,
    user: (state) => state.user,
    token: (state) => state.token,
    tasks: (state) => state.tasks,
    isLoading: (state) => state.isLoading,
  },
});
