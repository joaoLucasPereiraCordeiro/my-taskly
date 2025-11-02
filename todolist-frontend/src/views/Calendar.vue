<template>
  <div class="bg-light my-3 px-4">
    <div class="container-calendar w-100">
      <div class="fw-semibold fs-4 my-4 text-center text-md-start mb-2">
        <div class="title-base d-flex gap-1">
          <button class="btn btn-sm border-0 ps-0" @click="prevPeriod">
            <i class="fas fa-angle-left"></i>
          </button>
          <h4 class="title-calendar m-0">
            {{ isMobile ? monthName(currentMonth) + ' - ' + currentYear : 'Calendário - ' + currentYear }}
          </h4>
          <button class="btn btn-sm border-0" @click="nextPeriod">
            <i class="fas fa-angle-right"></i>
          </button>
        </div>
      </div>

      <!-- Versão Desktop (12 meses) -->
      <div v-if="!isMobile" class="year-grid">
        <div v-for="monthIndex in 12" :key="monthIndex" class="month-container rounded shadow-sm">
          <div class="month-name text-center font-weight-bold mb-2">
            {{ monthName(monthIndex - 1) }}
          </div>
          <div class="calendar-grid">
            <div class="calendar-day header" v-for="day in daysOfWeek" :key="day">
              {{ day }}
            </div>
            <div
              v-for="(day, index) in getPaddedDays(currentYear, monthIndex - 1)"
              :key="index"
              class="calendar-day"
              @click="handleDayClick(day)"
            >
              <div class="day-wrapper">
                <div :class="getDayClass(day)">
                  {{ day?.getDate?.() || '' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Versão Mobile (1 mês por vez) -->
      <div v-else class="month-container bg-white rounded shadow-sm">
        <div class="month-name text-center font-weight-bold mb-2">
          {{ monthName(currentMonth) }}
        </div>
        <div class="calendar-grid">
          <div class="calendar-day header" v-for="day in daysOfWeek" :key="day">
            {{ day }}
          </div>
          <div
            v-for="(day, index) in getPaddedDays(currentYear, currentMonth)"
            :key="index"
            class="calendar-day"
            @click="handleDayClick(day)"
          >
            <div class="day-wrapper">
              <div :class="getDayClass(day)">
                {{ day?.getDate?.() || '' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <BaseModal
        v-if="showModal"
        :title="'Tarefas de ' + selectedDateFormatted"
        @close="showModal = false"
      >
        <div v-if="tasksForSelectedDate.length > 0">
          <TaskCard
            v-for="task in tasksForSelectedDate"
            :key="task.id"
            :task="task"
            :readonly="true"
          />
        </div>
        <div v-else class="text-center text-muted">
          Nenhuma tarefa para este dia.
        </div>
      </BaseModal>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BaseModal from '@/components/BaseModal.vue';
import TaskCard from '@/components/TaskCard.vue';

export default {
  name: 'Calendar',
  components: { BaseModal, TaskCard },
  data() {
    const now = new Date();
    return {
      currentYear: now.getFullYear(),
      currentMonth: now.getMonth(),
      isMobile: window.innerWidth < 768,
      daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
      tasks: [],
      showModal: false,
      selectedDate: null,
    };
  },
  computed: {
    selectedDateFormatted() {
      return this.selectedDate ? this.formatDate(this.selectedDate) : '';
    },
    tasksForSelectedDate() {
      if (!this.selectedDate) return [];
      const formatted = this.formatDate(this.selectedDate);
      return this.tasks.filter(task => task.due_date === formatted);
    },
  },
  methods: {
    fetchTasks() {
      axios.get('http://127.0.0.1:8000/api/tasks')
        .then(res => { this.tasks = res.data; })
        .catch(err => { console.error('Erro ao buscar tarefas:', err); });
    },
    monthName(monthIndex) {
      return new Date(this.currentYear, monthIndex).toLocaleString('pt-BR', { month: 'long' });
    },
    getDaysInMonth(year, month) {
      const days = [];
      const date = new Date(year, month, 1);
      while (date.getMonth() === month) {
        days.push(new Date(date));
        date.setDate(date.getDate() + 1);
      }
      return days;
    },
    getPaddedDays(year, month) {
      const daysInMonth = this.getDaysInMonth(year, month);
      const startDay = new Date(year, month, 1).getDay();
      const blanks = Array(startDay).fill(null);
      return [...blanks, ...daysInMonth];
    },
    formatDate(date) {
      const d = String(date.getDate()).padStart(2, '0');
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const y = date.getFullYear();
      return `${d}/${m}/${y}`;
    },
    getDayClass(day) {
      if (!day) return 'empty-day';
      const formattedDay = this.formatDate(day);
      const today = new Date(); today.setHours(0,0,0,0);

      const tasksForDay = this.tasks?.filter(task => task.due_date === formattedDay) || [];
      if (tasksForDay.length === 0) return '';

      const hasOverdue = tasksForDay.some(task => {
        const [d,m,y] = task.due_date.split('/');
        const taskDate = new Date(+y, +m - 1, +d);
        return !task.completed && taskDate < today;
      });
      const hasPendingOnTime = tasksForDay.some(task => {
        const [d,m,y] = task.due_date.split('/');
        const taskDate = new Date(+y, +m - 1, +d);
        return !task.completed && taskDate >= today;
      });

      if (hasOverdue) return 'has-task bg-danger text-white rounded-circle';
      if (hasPendingOnTime) return 'has-task bg-yellow text-white rounded-circle';
      return 'has-task bg-success text-white rounded-circle';
    },
    handleDayClick(day) {
      if (!day) return;
      const formatted = this.formatDate(day);
      const tasks = this.tasks.filter(task => task.due_date === formatted);
      if (tasks.length > 0) {
        this.selectedDate = day;
        this.showModal = true;
      }
    },
    prevPeriod() {
      if (this.isMobile) {
        this.currentMonth--;
        if (this.currentMonth < 0) { this.currentMonth = 11; this.currentYear--; }
      } else {
        this.currentYear--;
      }
    },
    nextPeriod() {
      if (this.isMobile) {
        this.currentMonth++;
        if (this.currentMonth > 11) { this.currentMonth = 0; this.currentYear++; }
      } else {
        this.currentYear++;
      }
    }
  },
  mounted() {
    this.fetchTasks();
    window.addEventListener("resize", () => {
      this.isMobile = window.innerWidth < 768;
    });
  },
};
</script>

<style scoped>
.container-calendar { max-width: 100%; }
.year-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 10px;
}
.month-container {
  border: 1px solid #ddd;
  padding: 1rem;
  background-color: #fff;
}
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}
.day-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 30px;
}
.calendar-day {
  min-height: 30px;
  text-align: center;
  user-select: none;
  font-weight: 500;
}
.has-task {
  width: 30px; height: 30px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 50%;
}
.header { font-weight: bold; }
.empty-day { visibility: hidden; }
.bg-yellow { background: #ffc107 !important; }
.bg-danger { background-color: #dc3545; }
.bg-success { background-color: #28a745; }
.text-white { color: white; }
.rounded-circle { border-radius: 50%; }

@media (max-width: 768px) {
  .title-base {
    padding: 0 0 0 2rem;
    align-items:center;
    height: 20px;
  }

  .calendar-grid {
    height: 80%;
  }

  .month-container {
    margin-top: 2rem;
    height: 400px;
  }
}
</style>
