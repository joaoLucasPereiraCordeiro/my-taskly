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
import api from "@/services/api";

const router = useRouter();

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

    // Se a API retornar token ou user, mostra mensagem de sucesso
    if (response.data?.token || response.data?.user) {
      alert("✅ Cadastro realizado com sucesso! Faça login para continuar.");
      router.replace("/login");
    } else {
      alert("Cadastro concluído, mas sem token. Faça login manualmente.");
      router.replace("/login");
    }

  } catch (e) {
    if (e.response?.status === 422 && e.response.data.errors) {
      // Formata os erros de validação para mostrar de forma clara
      const messages = Object.values(e.response.data.errors)
        .flat()
        .join("\n");
      alert(`⚠️ Erro de validação:\n${messages}`);
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
