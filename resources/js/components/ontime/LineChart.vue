<template>
  <div class="bg-background rounded-lg border shadow-sm p-4">
    <h3 class="text-base font-semibold mb-4">{{ title }}</h3>
    <div class="h-64">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  title: {
    type: String,
    default: 'Line chart'
  },
  chartData: {
    type: Object,
    default: () => ({
      labels: [],
      datasets: []
    })
  }
});

const chartCanvas = ref(null);
let chart = null;

// Function to initialize or update the chart
const initChart = () => {
  if (chart) {
    chart.destroy();
  }

  if (!chartCanvas.value) return;

  const ctx = chartCanvas.value.getContext('2d');
  chart = new Chart(ctx, {
    type: 'line',
    data: props.chartData,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
};

// Initialize chart when component is mounted
onMounted(() => {
  initChart();
});

// Watch for changes in chart data and update chart
watch(() => props.chartData, () => {
  initChart();
}, { deep: true });
</script>