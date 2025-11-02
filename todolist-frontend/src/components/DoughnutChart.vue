<!-- src/components/DoughnutChart.vue -->
<template>
    <Doughnut :data="chartData" :options="chartOptions" />
  </template>
  
  <script>
  import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
  } from 'chart.js';
  import { Doughnut } from 'vue-chartjs';
  
  ChartJS.register(Title, Tooltip, Legend, ArcElement);
  
  export default {
    name: 'DoughnutChart',
    components: { Doughnut },
    props: {
      chartData: {
        type: Object,
        required: true,
      },
      chartOptions: {
        type: Object,
        required: true,
      },
    },
  };
  </script>
  