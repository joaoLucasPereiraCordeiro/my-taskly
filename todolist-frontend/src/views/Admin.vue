<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fw-bold">Painel Administrativo</h1>
        <p class="text-muted">Gerencie usuários e visualize estatísticas do sistema</p>
      </div>
      <!-- Botão de sair -->
      <li class="custom-btn list-unstyled">
        <a href="#" @click.prevent="handleLogout" class="text-decoration-none fw-bold">
          <i class="fas fa-right-from-bracket"></i> Sair
        </a>
      </li>
    </div>

    <!-- Estatísticas -->
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title text-muted">Total de Usuários</h5>
            <p class="display-6 text-primary fw-bold mb-0">{{ stats.total_users }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title text-muted">Usuários Online</h5>
            <p class="display-6 text-success fw-bold mb-0">{{ stats.online_users }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title text-muted">Administradores</h5>
            <p class="display-6 text-warning fw-bold mb-0">{{ stats.admins }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Usuários -->
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h4 class="card-title mb-4">Lista de Usuários</h4>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Função</th>
                <th>Último login</th>
                <th>Criado em</th>
                <th>Funções</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td>{{ user.id }}</td>
                <td><strong>{{ user.name }}</strong></td>
                <td>{{ user.email }}</td>
                <td>
                  <span
                    class="badge"
                    :class="{
                      'bg-primary': user.role === 'admin',
                      'bg-secondary': user.role !== 'admin'
                    }"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td>{{ formatDate(user.last_login_at) }}</td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>
                  <button
                    class="btn btn-sm btn-danger"
                    @click="deleteUser(user.id)"
                  >
                    Excluir
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue"
import { useStore } from "vuex"
import { useRouter } from "vue-router"
import axios from "axios"

const users = ref([])
const stats = ref({ total_users: 0, online_users: 0, admins: 0 })

const store = useStore()
const router = useRouter()

// Formata para PT-BR apenas com dia, mês e ano
function formatDate(date) {
  if (!date) return "-"
  return new Date(date).toLocaleDateString("pt-BR", {
    timeZone: "America/Sao_Paulo",
    day: "2-digit",
    month: "2-digit",
    year: "numeric"
  })
}

async function fetchAdminData() {
  try {
    const usersResponse = await axios.get("/api/admin/users")
    users.value = usersResponse.data.users

    const statsResponse = await axios.get("/api/admin/stats")
    stats.value = statsResponse.data
  } catch (error) {
    alert("Erro ao carregar dados do admin")
  }
}

async function deleteUser(id) {
  if (!confirm("Tem certeza que deseja excluir este usuário?")) return
  try {
    await axios.delete(`/api/admin/users/${id}`)
    users.value = users.value.filter((user) => user.id !== id)
  } catch (error) {
    alert("Erro ao excluir usuário")
  }
}

// Logout
async function handleLogout() {
  await store.dispatch("logout")
  router.push("/login")
}

onMounted(() => {
  fetchAdminData()
})
</script>
