<template>
  <BaseModal :title="task.title" @close="emitClose">
    <template #header>
      <div class="d-flex justify-content-between align-items-center w-100">
        <h5 class="modal-title ps-3 mb-0">{{ task.title }}</h5>

        <div class="d-flex align-items-center gap-2">
          <div class="dropdown">
            <button class="btn btn-secondary bg-transparent btn-sm border border-0" type="button" data-bs-toggle="dropdown">
              <i class="fas fa-ellipsis-v" style="color: #000000;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button class="dropdown-item text-danger" @click="$emit('delete', task)">Excluir</button></li>
              <li><button class="dropdown-item" @click="$emit('edit', task)">Editar</button></li>
            </ul>
          </div>
        </div>
      </div>
    </template>

    <!-- Conteúdo principal do modal -->
    <div class="modal-content-wrapper" @click.self="emitClose">
      <div class="modal-content-inner" @click.stop>
        <p><strong>Descrição:</strong> {{ task.description || 'Sem descrição' }}</p>
        <p><strong>Data de criação:</strong> {{ formatDate(task.created_at) }}</p>
        <p><strong>Prazo:</strong> {{ formatDate(task.due_date) }}</p>
        <p><strong>Status:</strong> {{ task.completed ? 'Concluído' : 'Pendente' }}</p>

        <!-- Exibe subtarefas sempre -->
        <div v-if="task.subtasks?.length" class="mt-3">
          <strong>Subtarefas ({{ task.subtasks.length }}):</strong>
          <ul class="list-unstyled mt-2 ms-3">
            <li
              v-for="subtask in task.subtasks"
              :key="subtask.id"
              class="d-flex align-items-center gap-2 mb-1"
            >
              <input
                type="checkbox"
                :checked="subtask.completed"
                disabled
              />
              <span :class="{ 'text-decoration-line-through': subtask.completed }">
                {{ subtask.title }}
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script>
import BaseModal from '@/components/BaseModal.vue';

export default {
  name: 'TaskDetails',
  components: { BaseModal },
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'edit', 'delete'],
  methods: {
    emitClose() {
      this.$emit('close');
    },
    formatDate(dateString) {
      if (!dateString) return 'Sem data';

      const parts = dateString.split('/');
      if (parts.length === 3) {
        const [day, month, year] = parts;
        const date = new Date(`${year}-${month}-${day}T00:00:00`);
        if (isNaN(date.getTime())) return 'Data inválida';

        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();

        return `${d}/${m}/${y}`;
      }

      const date = new Date(dateString);
      if (isNaN(date.getTime())) return 'Data inválida';

      const d = String(date.getDate()).padStart(2, '0');
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const y = date.getFullYear();

      return `${d}/${m}/${y}`;
    },
  }
};
</script>

<style scoped>
.dropdown-menu {
  z-index: 1055 !important;
}

.dropdown {
  padding-top:4px;
}

.fas.fa-ellipsis-v {
  font-size: 1.2rem;
}
.btn-close-custom {
  color: red; 
  font-size: 1.7rem;
  background: transparent;
  border: none;
  font-weight: 600;
  cursor: pointer;
  padding: 0 8px;
}
</style>
