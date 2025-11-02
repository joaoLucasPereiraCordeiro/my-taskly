<template>
  <div class="w-100 d-flex flex-column overflow-hidden p-4 bg-light">
    <div class="title-base">
      <h4 class="sub-text bg-light fw-medium">A rotina ideal começa com um plano</h4>
      <h1 class="mb-3">Planejamento de Tarefas</h1>
    </div>

    <!-- Barra superior -->
    <div class="container-button gap-3 bg-white rounded shadow-sm px-3 py-4 mb-4">
      <!-- Busca -->
      <div class="d-flex align-items-center gap-2 flex-grow-1 search-box order-1">
        <i class="fas fa-search text-muted"></i>
        <input
          v-model="searchTerm"
          type="text"
          class="form-control form-control-sm"
          placeholder="Buscar tarefa..."
        />
      </div>

     <!-- Filtros Desktop (botões visíveis somente em telas grandes) -->
<div class="d-none d-lg-block order-2">
  <TaskFilterButtons
    :activeFilter="activeFilter"
    @update:filter="setFilter"
  />
</div>

<!-- Filtros Mobile e Tablet (dropdown visível em telas menores que 992px) -->
<div class="d-block d-lg-none w-100 order-3 mt-2">
  <div class="dropdown w-100">
    <button
      class="btn-dropdown bg-white w-100 text-start d-flex justify-content-between align-items-center"
      type="button"
      data-bs-toggle="dropdown"
    >
      <span>
        <i class="fas fa-filter text-muted me-2"></i>
        {{ filterTitle }}
      </span>
      <i class="fas fa-chevron-down text-muted"></i>
    </button>

    <ul class="dropdown-menu w-100 rounded shadow-sm">
      <li><button class="dropdown-item" @click="setFilter('all')">Todas as Tarefas</button></li>
      <li><button class="dropdown-item" @click="setFilter('today')">Tarefas de Hoje</button></li>
      <li><button class="dropdown-item" @click="setFilter('week')">Tarefas da Semana</button></li>
      <li><button class="dropdown-item" @click="setFilter('month')">Tarefas do Mês</button></li>
      <li><button class="dropdown-item" @click="setFilter('completed')">Tarefas Concluídas</button></li>
      <li><button class="dropdown-item" @click="setFilter('pending')">Tarefas Pendentes</button></li>
    </ul>
  </div>
</div>

      <!-- Botão Criar Tarefa -->
      <div class="d-flex align-items-center gap-2 order-4 mt-2 mt-lg-0 w-100">
        <button class="custom-btn" @click="openCreateModal">
          <i class="fas fa-plus"></i> Criar Tarefa
        </button>
      </div>
    </div>

    <!-- Modal de criação -->
    <TaskCreate
      v-if="showCreateModal"
      :modelValue="showCreateModal"
      :new-task="newTask"
      @submit="createTask"
      :isSubmitting="isSubmitting"
      @voice-input="handleVoiceInput"
      @close="closeCreateModal"
    />

    <!-- Modal de detalhes -->
    <TaskDetails
      v-if="selectedTask"
      :task="selectedTask"
      @close="selectedTask = null"
      @edit="onEditTask"
      @delete="onDeleteTask"
    />

    <!-- Mensagem de status -->
    <Message 
      :message="message" 
      @update:message="message = $event" 
    />

    <!-- Conteúdo principal -->
    <div class="task-container">
      <div class="task-scroll-wrapper hide-scrollbar" ref="taskListContainer" @scroll="handleScroll">
        <TaskCard
          v-for="task in searchedTasks"
          :key="task.id"
          :task="task"
          @click="showTaskDetails"
          @edit="onEditTask"
          @delete="onDeleteTask"
          @toggle-complete="toggleComplete"
          @toggle-subtask="toggleSubtask"
        />

        <!-- Mensagem caso não haja tarefas -->
        <NoTasksMessage
          v-if="searchedTasks.length === 0"
          :filter="activeFilter"
          :search="searchTerm"
          @create-task="openCreateModal"
        />
      </div>

      <!-- NotesPad -->
      <div class="notes-wrapper">
        <NotesPad />
      </div>
    </div>

    <!-- Modal de edição -->
    <BaseModal v-if="showEditModal" @close="showEditModal = false">
      <TaskEdit 
        :modelValue="editingTask" 
        @submit="updateTask" 
        @close="showEditModal = false" 
      />
    </BaseModal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import BaseModal from '../components/BaseModal.vue';
import TaskDetails from "../components/TaskDetails.vue";
import NotesPad from '../components/NotesPad.vue';
import TaskCreate from '../components/TaskCreate.vue';
import TaskEdit from '../components/TaskEdit.vue';
import Message from '../components/Message.vue';
import TaskFilterButtons from '../components/TaskFilterButtons.vue';
import TaskCard from '../components/TaskCard.vue';
import NoTasksMessage from '../components/NoTasksMessage.vue';
import { filterTasks } from '../utils/useTaskFilter';
import { formatDateToISO } from '../utils/formatDate';
import api from '../services/api';

export default {
  components: {
    BaseModal,
    TaskDetails,
    NotesPad,
    TaskCreate,
    TaskEdit,
    Message,
    TaskFilterButtons,
    TaskCard,
    NoTasksMessage,
  },
  data() {
    return {
      newTask: this.emptyTask(),
      editingTask: null,
      selectedTask: null,
      showCreateModal: false,
      showEditModal: false,
      message: "",
      activeFilter: 'all', // exibe todas as tarefas ao iniciar
      searchTerm: "",
      isSubmitting: false,
    };
  },
  async created() {
    await this.$store.dispatch('fetchTasks');
  },
  computed: {
    ...mapState(['tasks']),
    filteredTasks() {
      return filterTasks(this.tasks, this.activeFilter);
    },
    searchedTasks() {
      if (!this.searchTerm.trim()) return this.filteredTasks;
      const term = this.searchTerm.toLowerCase();
      return this.filteredTasks.filter(task =>
        task.title.toLowerCase().includes(term)
      );
    },
    filterTitle() {
      switch (this.activeFilter) {
        case 'all': return 'Todas as Tarefas';
        case 'completed': return 'Tarefas Concluídas';
        case 'pending': return 'Tarefas Pendentes';
        case 'today': return 'Tarefas de Hoje';
        case 'week': return 'Tarefas da Semana';
        case 'month': return 'Tarefas do Mês';
        default: return 'Tarefas';
      }
    },
  },
  methods: {
    emptyTask() { return { title: "", description: "", due_date: "", subtasks: [] }; },
    openCreateModal() { this.newTask = this.emptyTask(); this.showCreateModal = true; },
    closeCreateModal() { this.newTask = this.emptyTask(); this.showCreateModal = false; },
    setFilter(filter) { this.activeFilter = filter; },
    showTaskDetails(task) { this.selectedTask = task; },

    async createTask(newTask) {
      if (!newTask.title) { this.message = "O título da tarefa é obrigatório."; return; }
      this.isSubmitting = true;
      try {
        const response = await api.post("/tasks", newTask);
        this.tasks.push(response.data);
        this.closeCreateModal();
        this.message = "Tarefa criada com sucesso!";
        setTimeout(() => this.message = "", 2000);
      } catch (error) {
        console.error(error);
        this.message = "Erro ao criar tarefa.";
      } finally {
        this.isSubmitting = false;
      }
    },

    async updateTask(updatedTask) {
      if (!updatedTask.title) { this.message = "O título é obrigatório."; return; }
      if (updatedTask.subtasks && Array.isArray(updatedTask.subtasks)) {
        updatedTask.subtasks = updatedTask.subtasks.filter(sub => sub.title && sub.title.trim() !== '');
      }
      try {
        const response = await api.put(`/tasks/${updatedTask.id}`, updatedTask);
        const index = this.tasks.findIndex(t => t.id === updatedTask.id);
        if (index !== -1) this.tasks.splice(index, 1, response.data);
        this.showEditModal = false;
        this.message = "Tarefa atualizada com sucesso!";
      } catch (error) {
        console.error(error);
        this.message = "Erro ao atualizar tarefa.";
      } finally {
        setTimeout(() => this.message = "", 2000);
      }
    },

    async toggleSubtask(subtask) {
      subtask.completed = !subtask.completed;
      try {
        const response = await api.put(`/subtasks/${subtask.id}`, { title: subtask.title, completed: subtask.completed });
        const updatedSubtask = response.data.subtask;
        const updatedTask = response.data.task;

        const taskIndex = this.tasks.findIndex(t => t.id === updatedTask.id);
        if (taskIndex !== -1) {
          const task = this.tasks[taskIndex];
          const subtaskIndex = task.subtasks.findIndex(s => s.id === updatedSubtask.id);
          if (subtaskIndex !== -1) task.subtasks[subtaskIndex] = updatedSubtask;
          task.completed = updatedTask.completed;
        }
      } catch (error) {
        console.error(error);
        subtask.completed = !subtask.completed;
        this.message = 'Erro ao atualizar subtarefa.';
      }
    },

    async toggleComplete(task) {
      task.completed = !task.completed;
      const formatDateYMD = (date) => {
        if (!date) return null;
        if (typeof date === "string" && date.includes("/")) {
          const [day, month, year] = date.split("/");
          return `${year}-${month}-${day}`;
        }
        const d = new Date(date);
        return d.toISOString().split("T")[0];
      };

      try {
        const response = await api.put(`/tasks/${task.id}`, {
          title: task.title,
          description: task.description,
          due_date: formatDateYMD(task.due_date),
          completed: task.completed,
        });
        const updatedTask = response.data;
        const index = this.tasks.findIndex(t => t.id === task.id);
        if (index !== -1) this.tasks.splice(index, 1, updatedTask);
      } catch (error) {
        console.error(error);
        task.completed = !task.completed;
        this.message = "Erro ao atualizar tarefa.";
      }
    },

    onEditTask(task) {
      this.editingTask = { ...task, due_date: formatDateToISO(task.due_date) };
      this.showEditModal = true;
    },

    async onDeleteTask(task) {
      
        try {
          await api.delete(`/tasks/${task.id}`);
          const index = this.tasks.findIndex(t => t.id === task.id);
          if (index !== -1) this.tasks.splice(index, 1);
          this.selectedTask = null;
          this.message = "Tarefa excluída com sucesso!";
        } catch (error) {
          console.error(error);
          this.message = "Erro ao excluir tarefa.";
        }
        setTimeout(() => (this.message = ""), 2000);
      
    },

    handleVoiceInput({ title, due_date }) {
      if (title) {
        const delimitadores = [' no ', ' em ', ' dia ', ' de '];
        let tituloLower = title.toLowerCase();
        let ultimoIndex = -1;
        delimitadores.forEach(delim => {
          const idx = tituloLower.lastIndexOf(delim);
          if (idx > ultimoIndex) ultimoIndex = idx;
        });
        let tituloLimpo = ultimoIndex > -1 ? title.substring(0, ultimoIndex) : title;
        tituloLimpo = tituloLimpo.trim();
        tituloLimpo = tituloLimpo.charAt(0).toUpperCase() + tituloLimpo.slice(1);
        this.newTask.title = tituloLimpo;
      }
      if (due_date) this.newTask.due_date = due_date;
    },
  },
};
</script>

<style scoped>
.task-card {
  position: relative;
}


.dropdown-menu {
  border: none;
  border-radius: .5rem;
}

.btn-dropdown {
  border: 1px solid;
  color: #dee2e6;
  border-radius: 0.25rem;
}

.btn-dropdown span {
  color: #000;
}


/* Ícones ações */
.task-actions {
  font-size: 20px;
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}
.task-actions button {
  border:none;
  background: none;
}
.task-card:hover .task-actions {
  opacity: 1;
  pointer-events: auto;
}

.custom-btn {
  padding: 6px 16px;
  border-radius: 6px;
  background-color: #ff0084;
  color: #ffff;
  border: none;
  font-weight: 500;
  transition: background-color 0.2s;
}


input,
input:focus,
textarea,
textarea:focus,
select,
select:focus {
  outline: none !important;       /* tira contorno */
  box-shadow: none !important;    /* tira a sombra azul do Bootstrap */
  border-color: #ced4da !important; /* opcional: volta borda neutra */
  resize: none !important;
}

.dropdown-item:focus,
.dropdown-item:active {
  background-color: transparent !important;
  color: inherit !important;
  outline: none !important;
  box-shadow: none !important;
}


.sub-text {
  width: 350px;
  text-align: center;
  color:#c3c0c0;
  font-size: 1rem; 
  font-weight: 500;
  letter-spacing: 2px
}

/* Esconde scroll */
.hide-scrollbar {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE 10+ */
}
.hide-scrollbar::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}

.badge {
  background: #D314081A;
  border-radius: 4px;
  color: #D31408;
  font-weight: 600;
  padding: 3px 15px;
}

#prazo {
  font-weight: 600;
}

thead th {
  border: none !important;
}

.column-table {
  margin-bottom: 3rem;
}

.task-container {
  display: flex;
  height: 100%;
  justify-content: center;
  gap: 5%;
}

.task-scroll-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 60%;
  max-height: 450px;
  overflow-y: auto;
}

.notes-wrapper {
  width: 35%;
}

.container-button {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

/* Responsividade */
@media (max-width: 991px) { /* tablet para baixo */
  .task-container {
    flex-direction: column;
    align-items: center;
  }
  .task-scroll-wrapper {
    width: 100%;
    max-height: 400px;
  }
  .notes-wrapper {
    width: 100%;
    margin-top: 1.5rem;
  }
  .container-button {
    display: flex;
    flex-direction: column;
    align-items: stretch;
  }
  .search-box {
    width: 100%;
  }
  .custom-btn {
    width: 100%;
    text-align: center;
  }
  h4.sub-text  {
    padding-left: 2rem;
    text-align: left;
    width: 100%;
  }
}

@media (max-width: 768px) { /* celulares */
  .task-container {
    flex-direction: column;
    gap: 1rem;
  }
  .task-scroll-wrapper {
    width: 100%;
    max-height: 350px;
  }
  .notes-wrapper {
    width: 100%;
    margin-top: 1rem;
  }
}
</style>
