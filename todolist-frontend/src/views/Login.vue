<template>
  <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card shadow-lg border-0 m-3" style="max-width: 420px; width: 100%;">
      <div class="card-body p-5">
        <!-- Logo ou Título -->
        <h2 class="text-center fw-bold mb-4">Login</h2>
        <p class="text-center text-muted mb-4">Entre com suas credenciais para acessar</p>

        <!-- Formulário -->
        <form @submit.prevent="handleLogin">
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
              placeholder="Digite sua senha"
              required
            />
          </div>

          <!-- Botão Entrar -->
          <div class="d-grid">
            <button type="submit" class="btn-enter" :disabled="isLoading">
              <span>Entrar</span>
            </button>
          </div>
        </form>

        <!-- Criar Conta -->
        <div class="text-center mt-4">
          <router-link to="/register" class="text-decoration-none fw-semibold">
            Não tem uma conta? <span class="text-primary">Crie agora</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Loader global -->
    <BaseLoader :loading="isLoading" />
  </div>
</template>

<script setup>
import BaseLoader from '@/components/BaseLoader.vue'
import { ref } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

const store = useStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const isLoading = ref(false)
const handleLogin = async () => {
  store.commit('setLoadingTrue');

  try {
    await store.dispatch('login', {
      email: email.value,
      password: password.value,
    });

    await store.dispatch('fetchUser');

    await store.dispatch('fetchTasks');
store.commit('setLoadingFalse');
router.push('/home');


  } catch (e) {
    console.error("Erro ao fazer login:");
  
    store.commit('setLoadingFalse');
  }
};


</script>

<style scoped>
.btn-enter {
  background-color: #ff0084;
  color: white;
  border: none;
  padding: 6px 16px;
  border-radius: 6px;
  font-weight: 500;
}


input,
input:focus,
textarea,
textarea:focus,
select,
select:focus {
  outline: none !important;
  box-shadow: none !important;
  border-color: #ced4da !important;
  resize: none !important;
}
</style>
