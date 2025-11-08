<template>
  <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card shadow-lg border-0 m-3" style="max-width: 420px; width: 100%;">
      <div class="card-body p-5">
        <!-- Título -->
        <h2 class="text-center fw-bold mb-4">Criar Conta</h2>
        <p class="text-center text-muted mb-4">Preencha os campos para se cadastrar</p>

        <!-- Formulário -->
        <form @submit.prevent="handleRegister">
          <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nome</label>
            <input
              v-model="name"
              type="text"
              id="name"
              class="form-control form-control-lg"
              placeholder="Digite seu nome"
              required
            />
          </div>

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input
              v-model="email"
              type="email"
              id="email"
              class="form-control form-control-lg"
              placeholder="Digite seu email"
              required
            />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Senha</label>
            <input
              v-model="password"
              type="password"
              id="password"
              class="form-control form-control-lg"
              placeholder="Crie uma senha"
              required
            />
          </div>

          <!-- Botão Cadastrar -->
          <div class="d-grid">
            <button type="submit" class="btn-register" :disabled="loading">
              <span v-if="!loading">Cadastrar</span>
              <span v-else>⏳ Cadastrando...</span>
            </button>
          </div>
        </form>

        <!-- Já tenho conta -->
        <div class="text-center mt-4">
          <router-link to="/login" class="text-decoration-none fw-semibold">
            Já tem conta? <span>Entrar</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import api from "@/services/api";

const router = useRouter();
const store = useStore();

const name = ref("");
const email = ref("");
const password = ref("");
const loading = ref(false);

const handleRegister = async () => {
  loading.value = true;

  try {
    const response = await api.post("/register", {
      name: name.value,
      email: email.value,
      password: password.value,
    });

    // Verifica se o retorno contém token e user
    const { token, user, message } = response.data;

    if (token && user) {
      // Salva dados no store (se existir)
      if (store) {
        store.commit("setToken", token);
        store.commit("setUser", user);
      }

      alert(message || "Cadastro realizado com sucesso! 🎉");

      // Redireciona para o login após sucesso
      await router.replace("/login");
    } else {
      alert("Cadastro concluído! Faça login para continuar.");
      await router.replace("/login");
    }
  } catch (e) {
    if (e.response?.status === 422) {
      const errors = e.response.data.errors;
      if (errors?.email) {
        alert("❌ Esse e-mail já está cadastrado!");
      } else if (errors?.password) {
        alert("❌ Senha inválida (mínimo de 6 caracteres).");
      } else {
        alert(e.response.data.message || "Erro de validação ao cadastrar.");
      }
    } else if (e.response?.data?.message) {
      alert(`❌ Erro: ${e.response.data.message}`);
    } else {
      alert("❌ Erro inesperado ao cadastrar. Tente novamente.");
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.btn-register {
  background-color: #ff0084;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 6px;
  font-weight: 600;
  transition: 0.2s ease;
}

.btn-register:hover {
  background-color: #e60074;
}

input,
textarea,
select {
  outline: none !important;
  box-shadow: none !important;
  border-color: #ced4da !important;
  resize: none !important;
}
</style>
