<template>
  <div class="dashboard-wrapper container py-4">
    <h2 class="title-base fw-semibold fs-4 mb-2 pb-2 ">Resumo das suas tarefas</h2>

    <!-- Estatísticas principais -->
    <div class="row text-center g-3">
      <div
        class="col-12 col-sm-6 col-md-4 col-lg-3"
        v-for="(stat, index) in stats"
        :key="index"
      >
        <div class="card stat-card bg-white text-black shadow-sm border-light rounded">
          <div class="card-body py-3">
            <h5 class="card-title fs-6 mb-2">{{ stat.label }}</h5>
            <p class="display-6 mb-0">{{ stat.value }}</p>
          </div>
        </div>
      </div>
    </div>

    <h2 class="fw-semibold fs-4 my-4 text-start">Distribuição e progresso visual</h2>

    <!-- Gráficos com espaçamento lateral -->
    <div class="mb-4">
      <div class="white-card2 shadow-sm rounded">
        <div class="chart-item">
          <h5 class="text-center fw-semibold mb-3">Tarefas por Status</h5>
          <div class="chart-box">
            <DoughnutChart :chartData="chartData" :chartOptions="chartOptions" />
          </div>
        </div>

        <div class="chart-item">
          <h5 class="text-center fw-semibold mb-3">Tarefas por Mês</h5>
          <div class="chart-box">
            <LineChart :chartData="barChartData" :chartOptions="barChartOptions" />
          </div>
        </div>

        <div class="chart-item">
          <h5 class="text-center fw-semibold mb-3">Concluídas por Dia</h5>
          <div class="chart-box">
            <LineChart :chartData="concluidasPorDiaChartData" :chartOptions="barChartOptions" />
          </div>
        </div>
      </div>
    </div>

    <div class="row white-card shadow-sm rounded">
      <div class="col-12">
        <h5 class="text-center fw-semibold mb-3">Tarefas na Semana</h5>
        <div class="chart-box">
          <LineChart :chartData="semanalChartData" :chartOptions="barChartOptions" />
        </div>
      </div>
    </div>

    <!-- Progresso Geral -->
    <div class="mb-3 white-card shadow-sm rounded">
      <h5 class="fw-semibold mb-3">Progresso Geral</h5>
      <div class="progress" style="height: 30px;">
        <div
          class="progress-bar bg-success progress-bar-striped progress-bar-animated"
          role="progressbar"
          :style="{ width: percentualConcluido + '%' }"
          :aria-valuenow="percentualConcluido"
          aria-valuemin="0"
          aria-valuemax="100"
        >
          {{ percentualConcluido }}%
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import DoughnutChart from '../components/DoughnutChart.vue'
import LineChart from '../components/LineChart.vue'

export default {
  components: {
    DoughnutChart,
    LineChart
  },
  computed: {
    ...mapGetters(['tasks']),
    totalTarefas() {
      return this.tasks.length
    },
    tarefasConcluidas() {
      return this.tasks.filter(t => t.completed).length
    },
    tarefasAFazer() {
      const hoje = new Date()
      hoje.setHours(0, 0, 0, 0)
      return this.tasks.filter(t => {
        if (t.completed || !t.due_date) return false
        const [d, m, y] = t.due_date.split('/')
        const taskDate = new Date(+y, +m - 1, +d)
        taskDate.setHours(0, 0, 0, 0)
        return taskDate >= hoje
      }).length
    },
    tarefasAtrasadas() {
      const hoje = new Date()
      hoje.setHours(0, 0, 0, 0)
      return this.tasks.filter(t => {
        if (t.completed || !t.due_date) return false
        const [d, m, y] = t.due_date.split('/')
        const taskDate = new Date(+y, +m - 1, +d)
        taskDate.setHours(0, 0, 0, 0)
        return taskDate < hoje
      }).length
    },
    porcentagemAntesDoPrazo() {
      const tarefasConcluidas = this.tasks.filter(t => t.completed && t.due_date && t.updated_at)
      if (tarefasConcluidas.length === 0) return 0
      const countAntes = tarefasConcluidas.filter(t => {
        const [d, m, y] = t.due_date.split('/')
        const prazo = new Date(+y, +m - 1, +d)
        prazo.setHours(23, 59, 59, 999)
        const concluidaEm = new Date(t.updated_at)
        return concluidaEm <= prazo
      }).length
      return ((countAntes / tarefasConcluidas.length) * 100).toFixed(1)
    },
    tempoMedioConclusao() {
      const tempos = this.tasks
        .filter(t => t.completed && t.created_at && t.updated_at)
        .map(t => {
          const criacao = new Date(t.created_at)
          const conclusao = new Date(t.updated_at)
          const diffMs = conclusao - criacao
          const diffDias = diffMs / (1000 * 60 * 60 * 24)
          return diffDias
        })
      if (tempos.length === 0) return 0
      const soma = tempos.reduce((acc, val) => acc + val, 0)
      return (soma / tempos.length).toFixed(1)
    },
    percentualConcluido() {
      if (this.totalTarefas === 0) return 0
      return ((this.tarefasConcluidas / this.totalTarefas) * 100).toFixed(1)
    },
    chartData() {
      return {
        labels: ['Concluídas', 'À Fazer', 'Atrasadas'],
        datasets: [
          {
            data: [this.tarefasConcluidas, this.tarefasAFazer, this.tarefasAtrasadas],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
          }
        ]
      }
    },
    chartOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        }
      }
    },
    barChartData() {
      const tasksByMonth = Array(12).fill(0)
      this.tasks.forEach(t => {
        if (!t.due_date) return
        const [d, m, y] = t.due_date.split('/')
        const taskDate = new Date(+y, +m - 1, +d)
        const month = taskDate.getMonth()
        tasksByMonth[month]++
      })
      return {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        datasets: [
          {
            label: 'Tarefas por mês',
            data: tasksByMonth,
            backgroundColor: '#007bff'
          }
        ]
      }
    },
    concluidasPorDiaChartData() {
      const dias = {}
      this.tasks.forEach(t => {
        if (t.completed && t.updated_at) {
          const dia = t.updated_at.split('T')[0]
          dias[dia] = (dias[dia] || 0) + 1
        }
      })
      const labels = Object.keys(dias).sort()
      const valores = labels.map(d => dias[d])
      return {
        labels,
        datasets: [
          {
            label: 'Concluídas por dia',
            data: valores,
            backgroundColor: '#28a745'
          }
        ]
      }
    },
    semanalChartData() {
      const nomesDias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']
      const dias = []
      const hoje = new Date()
      hoje.setHours(0, 0, 0, 0)
      for (let i = 6; i >= 0; i--) {
        const d = new Date(hoje)
        d.setDate(d.getDate() - i)
        const chave = d.toISOString().split('T')[0]
        const nomeDia = nomesDias[d.getDay()]
        dias.push({ data: chave, nomeDia, concluidas: 0, pendentes: 0, atrasadas: 0 })
      }
      this.tasks.forEach(t => {
        if (!t.due_date) return
        const [dd, mm, yyyy] = t.due_date.split('/')
        const dueDate = new Date(+yyyy, +mm - 1, +dd)
        dueDate.setHours(0, 0, 0, 0)
        dias.forEach(dia => {
          if (dueDate.toISOString().split('T')[0] === dia.data) {
            if (t.completed) dia.concluidas++
            else if (dueDate < hoje) dia.atrasadas++
            else dia.pendentes++
          }
        })
      })
      return {
        labels: dias.map(d => d.nomeDia),
        datasets: [
          { label: 'Concluídas', data: dias.map(d => d.concluidas), backgroundColor: '#28a745', stack: 'Stack 0' },
          { label: 'Pendentes', data: dias.map(d => d.pendentes), backgroundColor: '#ffc107', stack: 'Stack 0' },
          { label: 'Atrasadas', data: dias.map(d => d.atrasadas), backgroundColor: '#dc3545', stack: 'Stack 0' }
        ]
      }
    },
    barChartOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: { grid: { display: false }, ticks: { display: true } },
          y: { grid: { display: false }, ticks: { display: true } }
        }
      }
    },
    stats() {
      return [
        { label: 'Total', value: this.totalTarefas },
        { label: 'Concluídas', value: this.tarefasConcluidas },
        { label: 'À Fazer', value: this.tarefasAFazer },
        { label: 'Atrasadas', value: this.tarefasAtrasadas },
        { label: 'Tempo Médio', value: this.tempoMedioConclusao },
        { label: 'Antecipadas', value: this.porcentagemAntesDoPrazo + '%' }
      ]
    }
  },
  methods: {
    ...mapActions(['fetchTasks'])
  },
  created() {
    this.fetchTasks()
  }
}
</script>

<style lang="scss" scoped>
body,
.dashboard-wrapper {
  min-height: 100vh;
}

.white-card,
.white-card2 {
  margin: auto;
  background-color: white;
  border-radius: 1rem;
  box-shadow: 0 0 16px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  margin-bottom: 20px;
}

.white-card2 {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 2rem;
  text-align: center;
}

/* Chart box flexível */
.chart-box {
  width: 100%;
  max-width: 100%;
  height: 220px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.chart-box canvas {
  max-width: 100% !important;
  max-height: 220px !important;
}

/* Cada gráfico é uma coluna flex */
.chart-item {
  flex: 1 1 280px;
  min-width: 280px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

/* Cards estatísticas adaptáveis */
.stat-card {
  height: 100%;
}

@media (max-width: 768px) {
  .chart-box {
    height: 200px;
  }
  h2.title-base {
    line-height: 1.2rem;
  }
}

@media (max-width: 655px) {
  h2.title-base {
    padding: 0 0 0 2.5rem;
  }
}
</style>
