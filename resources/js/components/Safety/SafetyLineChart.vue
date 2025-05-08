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
  categories: {
    type: Array,
    default: () => []
  },
  colors: {
    type: Array,
    default: () => ['#10b981', '#f59e0b', '#ef4444']
  },
  minYValue: {
    type: Number,
    default: null
  }
});

const chartCanvas = ref(null);
let chart = null;

const createChart = () => {
  if (!chartCanvas.value) return;
  
  const ctx = chartCanvas.value.getContext('2d');
  
  // Extract labels (dates) from data
  const labels = props.data.map(item => item.date);
  
  // Create datasets from categories
  const datasets = props.categories.map((category, index) => {
    return {
      label: category.replace(/([A-Z])/g, ' $1')
        .replace(/^./, (str) => str.toUpperCase())
        .trim(),
      data: props.data.map(item => item[category]),
      borderColor: props.colors[index % props.colors.length],
      backgroundColor: props.colors[index % props.colors.length] + '20',
      tension: 0.3,
      fill: false,
      pointRadius: 3,
      pointHoverRadius: 5
    };
  });
  
  // Get all data values for calculating min and max
  const allDataValues = [];
  datasets.forEach(dataset => {
    if (dataset.data && dataset.data.length) {
      allDataValues.push(...dataset.data.filter(val => val !== null && val !== undefined));
    }
  });
  
  // Calculate min and max values for Y-axis
  const minValue = allDataValues.length > 0 ? Math.min(...allDataValues) : 0;
  const maxValue = allDataValues.length > 0 ? Math.max(...allDataValues) : 100;
  
  // Add some padding to the min/max values (10% of the range)
  const range = maxValue - minValue;
  const padding = range * 0.1;
  const yMin = Math.max(0, minValue - padding); // Don't go below 0
  const yMax = maxValue + padding;
  
  // Create chart
  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            usePointStyle: true,
            boxWidth: 6
          }
        },
        tooltip: {
          mode: 'index',
          intersect: false
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          beginAtZero: false,
          min: props.minYValue !== null ? props.minYValue : Math.min(Math.floor(yMin-75), 0),
          max: Math.ceil(yMax+75),
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          },
          ticks: {
            stepSize: Math.ceil(range / 5) // Create approximately 5 steps
          }
        }
      }
    }
  });
};

const updateChart = () => {
  if (!chart) return;
  
  chart.data.labels = props.data.map(item => item.date);
  
  chart.data.datasets = props.categories.map((category, index) => {
    return {
      label: category.replace(/([A-Z])/g, ' $1')
        .replace(/^./, (str) => str.toUpperCase())
        .trim(),
      data: props.data.map(item => item[category]),
      borderColor: props.colors[index % props.colors.length],
      backgroundColor: props.colors[index % props.colors.length] + '20',
      tension: 0.3,
      fill: false,
      pointRadius: 3,
      pointHoverRadius: 5
    };
  });
  
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

watch(() => props.categories, () => {
  if (chart) {
    updateChart();
  } else {
    createChart();
  }
}, { deep: true });
</script>