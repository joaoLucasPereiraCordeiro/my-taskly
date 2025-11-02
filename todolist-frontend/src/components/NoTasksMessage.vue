<template>
    <div class="text-center text-muted py-5">
        <img
      :src="imageSrc"
      alt="Nenhuma tarefa"
      class="mb-4"
      style="max-width: 200px; opacity: 0.8;"
    />
      <h5 class="mb-2">
        {{ message }}
      </h5>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      filter: String,
      search: String,
    },
    computed: {
      message() {
        if (this.search) return `Nenhuma tarefa encontrada com o nome "${this.search}".`;
        switch (this.filter) {
          case 'today': return 'Você não tem tarefas para hoje.';
          case 'week': return 'Você não tem tarefas para esta semana.';
          case 'month': return 'Você não tem tarefas para este mês.';
          case 'completed': return 'Você ainda não concluiu nenhuma tarefa.';
          case 'pending': return 'Você não possui tarefas pendentes.';
          case 'all': return 'Você ainda não criou nenhuma tarefa.';
          default: return 'Nenhuma tarefa encontrada.';
        }
      },
      imageSrc() {
      // Pode trocar pelos seus próprios caminhos ou URLs
      if (this.search) return '/img/alert-pink.svg';
      return '/img/add-task-pink.svg';
    },
    }
  }
  </script>
  
  <style scoped>
  .text-muted {
    color: #6c757d;
  }

  .custom-btn {
    border-radius: 5px;
  }
  </style>
  