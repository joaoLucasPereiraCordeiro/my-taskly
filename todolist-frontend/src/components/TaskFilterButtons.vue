<template>
  <div class="d-flex gap-2 ms-3 flex-wrap">
    <button
      v-for="option in filterOptions"
      :key="option.value"
      :class="['custom-btn', { active: activeFilter === option.value }]"
      @click="$emit('update:filter', option.value)"
    >
      {{ option.label }}
    </button>
  </div>
</template>

  <script>
  export default {
    name: 'TaskFilteredButtons',
    props: {
      activeFilter: {
        type: String,
        required: true,
      },
    },
    data() {
      return {
        filterOptions: [
          { label: 'Hoje', value: 'today' },
          { label: 'Todas', value: 'all' },
          { label: 'Concluídas', value: 'completed' },
          { label: 'Pendentes', value: 'pending' },
          { label: 'Semana', value: 'week' },
          { label: 'Mês', value: 'month' },
        ],
      };
    },
    computed: {
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
      filterBtnClass(type) {
        const base = 'px-3 py-1 rounded font-semibold text-sm transition';
        const active = 'bg-[#01675e1a] text-[#01675e] border border-[#01675e]';
        const inactive = 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300';
        return `${base} ${this.activeFilter === type ? active : inactive}`;
      },
    },
  };
  </script>
  <style scoped>
  .custom-btn {
    padding: 6px 16px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background-color: white;
    color: #000;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
  }
  
  .custom-btn:hover {
    background-color: #f0f0f0;
  }
  
  .custom-btn.active {
    background-color: #ff0084;
    color: #fff;
    border-color: #ff0084;
  }
  </style>