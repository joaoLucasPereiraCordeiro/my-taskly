<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Painel de Administração</h2>

    <div v-if="loading">Carregando usuários...</div>

    <div v-else>
      <p>Total de usuários: {{ users.length }}</p>

      <table class="table-auto w-full mt-4 border">
        <thead>
          <tr class="bg-gray-200">
            <th class="p-2">Nome</th>
            <th class="p-2">Email</th>
            <th class="p-2">Função</th>
            <th class="p-2">Último Login</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id" class="border-t">
            <td class="p-2">{{ u.name }}</td>
            <td class="p-2">{{ u.email }}</td>
            <td class="p-2">{{ u.role }}</td>
            <td class="p-2">
              {{ u.last_login_at ? formatDate(u.last_login_at) : 'Nunca' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import api from '@/services/api';

export default {
  data() {
    return {
      users: [],
      loading: true,
    };
  },
  methods: {
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleString("pt-BR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    },
  },
  async mounted() {
    try {
      const res = await api.get("/admin/users"); // chama a rota da API
      this.users = res.data.users;
    } catch (err) {
      console.error("Erro ao buscar usuários:", err);
    } finally {
      this.loading = false;
    }
  },
};
</script>

<style scoped>
.table-auto th,
.table-auto td {
  text-align: left;
}
</style>
