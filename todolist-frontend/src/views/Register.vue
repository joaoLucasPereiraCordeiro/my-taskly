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
              <span>Cadastrar</span>
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import store from '@/store'

const router = useRouter()
const name = ref('')
const email = ref('')
const password = ref('')
const loading = ref(false)

const handleRegister = async () => {
  loading.value = true

  try {
    const response = await api.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value
    })

    const token = response.data.token
    const user = response.data.user

    if (token && user) {
      // Salva no store
      store.commit('setToken', token)
      store.commit('setUser', user)

      // Aguarda o fetchUser para garantir que os dados estejam prontos
      await store.dispatch('fetchUser')

      // Redireciona com replace para evitar voltar pro /register
      await router.replace('/home')
    } else {
      alert('Cadastro realizado, mas sem token. Faça login manualmente.')
      router.replace('/login')
    }
  } catch (e) {
    if (e.response?.data?.message) {
      alert(`Erro: ${e.response.data.message}`)
    } else {
      alert('Erro ao cadastrar')
    }
  } finally {
    loading.value = false
  }
}

</script>

<style scoped>
.btn-register {
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