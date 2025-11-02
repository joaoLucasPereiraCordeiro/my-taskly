<template>
  <div
    class="task-card p-3 bg-white rounded shadow-sm"
    @click="handleClick"
    style="cursor: pointer; width: 100%; position: relative;"
  >
    <!-- Título e Ações -->
    <div class="task-title fs-6 d-flex align-items-center justify-content-between mb-2">
      <div class="d-flex gap-2 align-items-center">
        <!-- Ícone da tarefa -->
        <span v-if="!readonly" @click.stop="toggleComplete" style="cursor:pointer">
          <i
            :class="task.completed ? 'fa-regular fa-circle-check' : 'fa-regular fa-circle'"
            style="color:#000000; font-size:1.2rem;"
          ></i>
        </span>
        <strong>{{ task.title }}</strong>
      </div>
      <!-- Botões de ação (editar e excluir) só se não for readonly -->
      <div class="task-actions d-flex gap-2" v-if="!readonly">
        <button @click.stop="$emit('edit', task)">
          <i class="fa-solid fa-pen-to-square" style="color: #000000;"></i>
        </button>
        <button @click.stop="$emit('delete', task)">
          <i class="fa-solid fa-trash" style="color: #000000;"></i>
        </button>
      </div>
    </div>

    <!-- Descrição -->
    <p v-if="task.description" class="text-muted mb-2">
      {{ task.description }}
    </p>

    <!-- Rodapé com data e subtarefas -->
    <div class="d-flex align-items-center justify-content-between mt-2">
      <span :class="[getTaskClass(task), 'd-flex align-items-center gap-1']">
        <i class="fa-solid fa-calendar-days"></i>
        {{ task.due_date }}
      </span>

      <!-- Indicador circular de subtarefas -->
      <div v-if="task.subtasks?.length" class="d-flex align-items-center gap-2">
        <div class="progress-circle">
          <svg viewBox="0 0 36 36" class="circular-chart">
            <path
              class="circle-bg"
              d="M18 2.0845
                 a 15.9155 15.9155 0 0 1 0 31.831
                 a 15.9155 15.9155 0 0 1 0 -31.831"
            />
            <path
              class="circle"
              :stroke-dasharray="percentSubtasks + ', 100'"
              d="M18 2.0845
                 a 15.9155 15.9155 0 0 1 0 31.831
                 a 15.9155 15.9155 0 0 1 0 -31.831"
            />
            <!-- AGORA mostrando porcentagem -->
            <text x="18" y="21.35" class="percentage">
              {{ percentSubtasks }}%
            </text>
          </svg>
        </div>
        <button
          @click.stop="toggleSubtasks"
          class="btn btn-link btn-sm p-0 ms-1"
          aria-label="Expandir subtarefas"
        >
          <i id="showsubtasks" :class="showSubtasks ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-up'"></i>
        </button>
      </div>
    </div>

    <!-- Lista de subtarefas -->
    <ul v-if="showSubtasks && task.subtasks?.length" class="list-unstyled mt-2 ms-4">
      <li
        v-for="subtask in task.subtasks"
        :key="subtask.id"
        class="d-flex align-items-center gap-2"
      >
        <!-- Ícone da subtask -->
        <span v-if="!readonly" @click.stop="$emit('toggle-subtask', subtask)" style="cursor:pointer">
          <i
            :class="subtask.completed ? 'fa-regular fa-circle-check' : 'fa-regular fa-circle'"
            style="color:#000000;"
          ></i>
        </span>
        <span :class="{ 'text-decoration-line-through': subtask.completed }">
          {{ subtask.title }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'TaskCard',
  props: {
    task: {
      type: Object,
      required: true
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  emits: ['edit', 'delete', 'click', 'toggle-complete', 'toggle-subtask'],
  data() {
    return {
      showSubtasks: false,
    };
  },
  computed: {
    percentSubtasks() {
      if (!this.task.subtasks || this.task.subtasks.length === 0) return 0;
      const done = this.task.subtasks.filter(s => s.completed).length;
      return Math.round((done / this.task.subtasks.length) * 100);
    }
  },
  methods: {
    handleClick() {
      this.$emit('click', this.task);
    },
    toggleComplete() {
      this.$emit('toggle-complete', this.task);
    },
    toggleSubtasks() {
      this.showSubtasks = !this.showSubtasks;
    },
    getTaskClass(task) {
      if (!task || !task.due_date) return '';

      const dateParts = task.due_date.split('/');
      if (dateParts.length !== 3) return '';

      const [day, month, year] = dateParts.map(Number);
      const dueDate = new Date(year, month - 1, day);
      if (isNaN(dueDate)) return '';

      const today = new Date();
      today.setHours(0, 0, 0, 0);
      dueDate.setHours(0, 0, 0, 0);

      if (task.completed) return 'text-success';
      if (dueDate < today) return 'text-danger';
      return 'text-warning';
    },
  },
};
</script>

<style scoped>
.task-card {
  position: relative;
}

.task-actions {
  font-size: 20px;
}
.task-actions button {
  border: none;
  background: none;
}
.task-card:hover .task-actions {
  opacity: 1;
  pointer-events: auto;
}

/* círculo de progresso */
.progress-circle {
  width: 40px;
  height: 40px;
}
.circular-chart {
  display: block;
  margin: 0 auto;
  max-width: 100%;
  max-height: 250px;
}
.circle-bg {
  fill: none;
  stroke: #eee;
  stroke-width: 3.8;
}
.circle {
  fill: none;
  stroke: #009465;
  stroke-width: 3.8;
  stroke-linecap: round;
  transition: stroke-dasharray 0.3s ease;
}
.percentage {
  fill: #000;
  font-size: 0.6rem;
  font-weight: 600;
  text-anchor: middle;
}


.btn-link {
  color: #000 !important;          /* sempre preto */
  text-decoration: none !important;/* sem sublinhado */
  background: transparent !important;
  border: none !important;
}

.btn-link:hover,
.btn-link:focus,
.btn-link:active {
  color: #000 !important;          /* continua preto */
  text-decoration: none !important;/* sem sublinhado no hover */
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;     /* remove sombra ao clicar */
}


.text-success {
  background: #0094881A;
  border-radius: 4px;
  padding: 3px 6px;
  color: #009488;
  font-weight: 600;
}

.text-danger {
  background: #D314081A;
  border-radius: 4px;
  padding: 3px 6px;
  color: #D31408;
  font-weight: 600;
}
.text-warning {
  background: #fff8c4;
  border-radius: 4px;
  padding: 3px 6px;
  color: #9f8700;
  font-weight: 600;
}
</style>
