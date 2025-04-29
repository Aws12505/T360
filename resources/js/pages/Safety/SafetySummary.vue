<template>
  <Card class="bg-background dark:bg-background">
    <CardContent class="p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Green Zone Score -->
        <div class="bg-muted/20 rounded-lg p-4">
          <h3 class="text-lg font-semibold text-foreground mb-2">Green Zone Score</h3>
          <div class="text-4xl font-bold text-primary">{{ Math.round(data.greenZoneScore) }}</div>
        </div>

        <!-- Top 5 Drivers -->
        <div class="bg-muted/20 rounded-lg p-4">
          <h3 class="text-lg font-semibold text-foreground mb-2">Top 5 Drivers</h3>
          <div class="space-y-2">
            <div v-if="data.topDrivers && data.topDrivers.length > 0">
              <div v-for="(driver, index) in data.topDrivers" :key="index" 
                   class="flex justify-between items-center">
                <span class="text-foreground">{{ driver.name }}</span>
                <span class="text-primary font-medium">{{ Math.round(driver.score) }}</span>
              </div>
            </div>
            <div v-else class="text-center text-muted-foreground italic">
              No driver data available
            </div>
          </div>
        </div>

        <!-- Bottom 5 Drivers -->
        <div class="bg-muted/20 rounded-lg p-4">
          <h3 class="text-lg font-semibold text-foreground mb-2">Bottom 5 Drivers</h3>
          <div class="space-y-2">
            <div v-if="data.bottomDrivers && data.bottomDrivers.length > 0">
              <div v-for="(driver, index) in data.bottomDrivers" :key="index" 
                   class="flex justify-between items-center">
                <span class="text-foreground">{{ driver.name }}</span>
                <span class="text-destructive font-medium">{{ Math.round(driver.score) }}</span>
              </div>
            </div>
            <div v-else class="text-center text-muted-foreground italic">
              No driver data available
            </div>
          </div>
        </div>
      </div>

      <!-- Total Severe Alerts -->
      <div class="mt-6">
        <h3 class="text-lg font-semibold text-foreground mb-4">Total Severe Alerts</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div v-for="(value, type) in data.alerts" :key="type" 
               class="bg-muted/20 rounded-lg p-4 text-center">
            <div class="text-sm text-muted-foreground mb-2">{{ formatAlertType(type) }}</div>
            <div class="text-2xl font-bold text-primary">{{ Math.floor(value) }}</div>
          </div>
        </div>
      </div>

      <!-- Other Severe Safety Infractions -->
      <div class="mt-6">
        <h3 class="text-lg font-semibold text-foreground mb-4">Other Severe Safety Infractions</h3>
        
        <!-- Grid for visible infractions -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div v-for="(value, type) in visibleInfractions" :key="type" 
               class="bg-muted/20 rounded-lg p-4 text-center">
            <div class="text-sm text-muted-foreground mb-2">{{ formatInfractionType(type) }}</div>
            <div class="text-2xl font-bold text-primary">{{ Math.round(value) }}</div>
          </div>
        </div>

        <!-- Show More Button -->
        <div v-if="hasMoreInfractions" class="mt-4 text-center">
          <Button 
            @click="toggleShowMore" 
            variant="outline"
            class="w-full md:w-auto"
          >
            <Icon 
              :name="showMore ? 'chevron-up' : 'chevron-down'" 
              class="mr-2 h-4 w-4"
            />
            {{ showMore ? 'Show Less' : `Show ${remainingInfractionCount} More` }}
          </Button>
        </div>

        <!-- Additional infractions when expanded -->
        <div v-if="showMore" class="mt-4">
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div v-for="(value, type) in hiddenInfractions" :key="type" 
                 class="bg-muted/20 rounded-lg p-4 text-center">
              <div class="text-sm text-muted-foreground mb-2">{{ formatInfractionType(type) }}</div>
              <div class="text-2xl font-bold text-primary">{{ Math.round(value) }}</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Show/Hide Graphs Button -->
      <div class="mt-6 text-center">
        <Button 
          @click="toggleShowGraphs" 
          variant="outline"
          class="w-full md:w-auto"
        >
          <Icon 
            :name="showGraphs ? 'eye-off' : 'eye'" 
            class="mr-2 h-4 w-4"
          />
          {{ showGraphs ? 'Hide Graphs' : 'Show Graphs' }}
        </Button>
      </div>
      
      <!-- Safety Charts Section -->
      <div v-if="showGraphs" class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Line Chart -->
          <div class="bg-muted/20 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-foreground mb-4">Safety Trends</h3>
            <div class="w-full h-64">
              <canvas ref="lineChartCanvas" width="400" height="200"></canvas>
            </div>
          </div>
          
          <!-- Doughnut Chart -->
          <div class="bg-muted/20 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-foreground mb-4">Alert Distribution</h3>
            <div class="flex justify-center items-center h-72">
              <div class="w-full h-full">
                <canvas ref="doughnutChartCanvas" width="200" height="200"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Card, CardContent, Button } from '@/components/ui';
import Icon from '@/components/Icon.vue';
import Chart from 'chart.js/auto';

// Props definition
const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  }
});

// Chart references
const lineChartCanvas = ref(null);
const doughnutChartCanvas = ref(null);
let lineChart = null;
let doughnutChart = null;

// Helper functions
const formatAlertType = (type) => {
  return type.replace(/([A-Z])/g, ' $1')
    .replace(/^./, (str) => str.toUpperCase())
    .trim();
};

const formatInfractionType = (type) => {
  return type.replace(/([A-Z])/g, ' $1')
    .replace(/^./, (str) => str.toUpperCase())
    .trim();
};

// New reactive state for show more functionality
const showMore = ref(false);
// New reactive state for show graphs functionality
const showGraphs = ref(false);

// Sort and split infractions into visible and hidden
const sortedInfractions = computed(() => {
  const entries = Object.entries(props.data.infractions || {});
  return entries.sort((a, b) => b[1] - a[1]); // Sort by value in descending order
});

const visibleInfractions = computed(() => {
  const visible = {};
  sortedInfractions.value.slice(0, 5).forEach(([key, value]) => {
    visible[key] = value;
  });
  return visible;
});

const hiddenInfractions = computed(() => {
  const hidden = {};
  sortedInfractions.value.slice(5).forEach(([key, value]) => {
    hidden[key] = value;
  });
  return hidden;
});

const hasMoreInfractions = computed(() => {
  return Object.keys(hiddenInfractions.value).length > 0;
});

const remainingInfractionCount = computed(() => {
  return Object.keys(hiddenInfractions.value).length;
});

const toggleShowMore = () => {
  showMore.value = !showMore.value;
};

const toggleShowGraphs = () => {
  showGraphs.value = !showGraphs.value;
  
  // Initialize charts after DOM update when showing graphs
  if (showGraphs.value) {
    setTimeout(() => {
      initializeCharts();
    }, 100);
  }
};

// Dummy data for line chart
const lineChartData = [
  {
    date: "Jan 1",
    greenZoneScore: 92,
    distractions: 5,
    speeding: 3
  },
  {
    date: "Jan 8",
    greenZoneScore: 88,
    distractions: 7,
    speeding: 4
  },
  {
    date: "Jan 15",
    greenZoneScore: 91,
    distractions: 4,
    speeding: 2
  },
  {
    date: "Jan 22",
    greenZoneScore: 93,
    distractions: 3,
    speeding: 2
  },
  {
    date: "Jan 29",
    greenZoneScore: 95,
    distractions: 2,
    speeding: 1
  },
  {
    date: "Feb 5",
    greenZoneScore: 94,
    distractions: 3,
    speeding: 2
  },
  {
    date: "Feb 12",
    greenZoneScore: 90,
    distractions: 6,
    speeding: 3
  }
];

// Dummy data for doughnut chart
const doughnutChartData = [45, 25, 15, 10, 5];
const doughnutChartLabels = [
  "Distracted Driving", 
  "Speeding", 
  "Sign Violations", 
  "Traffic Light Violations", 
  "Following Distance"
];

// Chart initialization function
const initializeCharts = () => {
  // Destroy existing charts if they exist
  if (lineChart) lineChart.destroy();
  if (doughnutChart) doughnutChart.destroy();
  
  // Initialize line chart
  if (lineChartCanvas.value) {
    const ctx = lineChartCanvas.value.getContext('2d');
    if (ctx) {
      lineChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: lineChartData.map(item => item.date),
          datasets: [
            {
              label: 'Green Zone Score',
              data: lineChartData.map(item => item.greenZoneScore),
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              tension: 0.3,
              fill: true
            },
            {
              label: 'Distractions',
              data: lineChartData.map(item => item.distractions),
              borderColor: '#f59e0b',
              backgroundColor: 'transparent',
              tension: 0.3
            },
            {
              label: 'Speeding',
              data: lineChartData.map(item => item.speeding),
              borderColor: '#ef4444',
              backgroundColor: 'transparent',
              tension: 0.3
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }
  }
  
  // Initialize doughnut chart
  if (doughnutChartCanvas.value) {
    const ctx = doughnutChartCanvas.value.getContext('2d');
    if (ctx) {
      doughnutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: doughnutChartLabels,
          datasets: [{
            data: doughnutChartData,
            backgroundColor: [
              '#10b981', // green
              '#f59e0b', // amber
              '#ef4444', // red
              '#6366f1', // indigo
              '#8b5cf6'  // violet
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '60%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 20,
                usePointStyle: true,
                pointStyle: 'circle',
                boxWidth: 10
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
    }
  }
};

// Watch for changes in showGraphs to initialize charts
watch(showGraphs, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      initializeCharts();
    }, 100);
  }
});

// Initialize charts when component is mounted if graphs are shown
onMounted(() => {
  if (showGraphs.value) {
    setTimeout(() => {
      initializeCharts();
    }, 100);
  }
});
</script>