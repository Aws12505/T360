<template>
  <div class="w-full h-64">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  labels: {
    type: Array,
    required: true
  },
  colors: {
    type: Array,
    default: () => ['#10b981', '#f59e0b', '#ef4444', '#6366f1', '#8b5cf6']
  }
});

const chartCanvas = ref(null);
let chart = null;

const createChart = () => {
  if (!chartCanvas.value) return;
  
  const ctx = chartCanvas.value.getContext('2d');
  
  // Create chart
  chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: props.labels,
      datasets: [{
        data: props.data,
        backgroundColor: props.colors,
        borderColor: 'transparent',
        borderWidth: 0,
        hoverOffset: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            boxWidth: 6,
            padding: 15
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.raw || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = Math.round((value / total) * 100);
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
};

const updateChart = () => {
  if (!chart) return;
  
  chart.data.labels = props.labels;
  chart.data.datasets[0].data = props.data;
  chart.data.datasets[0].backgroundColor = props.colors;
  
  chart.update();
};

onMounted(() => {
  createChart();
});

watch(() => props.data, () => {
  if (chart) {
    updateChart();
  } else {
    createChart();
  }
}, { deep: true });

watch(() => props.labels, () => {
  if (chart) {
    updateChart();
  } else {
    createChart();
  }
}, { deep: true });
</script>