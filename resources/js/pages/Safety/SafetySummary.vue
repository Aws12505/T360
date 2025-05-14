<template>
  <Card class="bg-background dark:bg-background border border-muted/30">
    <CardContent class="p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Green Zone Score with Driver Star -->
        <div class="bg-gradient-to-br from-muted/10 to-muted/30 rounded-lg p-4 shadow-md border border-muted/20 hover:shadow-lg transition-shadow">
          <h3 class="text-lg font-semibold text-foreground mb-2">Green Zone Score</h3>
          <div class="text-4xl font-bold text-primary">{{ Math.round(data.greenZoneScore) }}</div>
          
          <!-- Driver Star visualization -->
          <div class="mt-3 pt-3 border-t border-muted/30">
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted-foreground">Driver Star</span>
              <span class="text-2xl font-bold text-indigo-600">{{ Math.round(data.infractions?.driverStar || 0) }}</span>
            </div>
            <!-- Simple progress bar visualization -->
            <!-- <div class="mt-2 h-2 w-full bg-muted/30 rounded-full overflow-hidden">
              <div 
                class="h-full bg-indigo-600 rounded-full" 
                :style="{ width: `${Math.min(100, (data.infractions?.driverStar || 0) / 100 * 100)}%` }"
              ></div>
            </div> -->
          </div>
        </div>

        <!-- Top 5 Drivers -->
        <div class="bg-gradient-to-br from-muted/10 to-muted/30 rounded-lg p-4 shadow-md border border-muted/20 hover:shadow-lg transition-shadow">
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
        <div class="bg-gradient-to-br from-muted/10 to-muted/30 rounded-lg p-4 shadow-md border border-muted/20 hover:shadow-lg transition-shadow">
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
               class="bg-gradient-to-br from-muted/10 to-muted/30 rounded-lg p-4 text-center shadow-md border border-muted/20 hover:shadow-lg transition-shadow">
            <div class="text-sm text-muted-foreground mb-2">{{ formatAlertType(type) }}</div>
            <div class="text-2xl font-bold text-primary">{{ Math.floor(value) }}</div>
          </div>
        </div>
      </div>

      <!-- Other Severe Safety Infractions -->
      <div class="mt-6">
        <h3 class="text-lg font-semibold text-foreground mb-4">Other Severe Safety Infractions</h3>
        
        <!-- Grid for visible infractions (excluding Driver Star which is now in Green Zone Score) -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div v-for="(value, type) in filteredVisibleInfractions" :key="type" 
               class="bg-gradient-to-br from-muted/10 to-muted/30 rounded-lg p-4 text-center shadow-md border border-muted/20 hover:shadow-lg transition-shadow">
            <div class="text-sm text-muted-foreground mb-2">{{ formatInfractionType(type) }}</div>
            <div class="text-2xl font-bold text-primary">{{ Math.round(value) }}</div>
          </div>
        </div>

        <!-- Show More Button -->
        <div v-if="hasMoreInfractions" class="mt-4 text-center">
          <Button 
            @click="toggleShowMore" 
            variant="outline"
            class="w-full md:w-auto shadow-sm hover:shadow transition-shadow"
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
                 class="bg-gradient-to-br from-muted/10 to-muted/30 rounded-lg p-4 text-center shadow-md border border-muted/20 hover:shadow-lg transition-shadow">
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
          class="w-full md:w-auto shadow-sm hover:shadow transition-shadow"
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
          <div class="bg-gradient-to-br from-muted/10 to-muted/30 rounded-lg p-4 shadow-lg border border-muted/20">
            <h3 class="text-lg font-semibold text-foreground mb-4">Safety Trends</h3>
            <div class="w-full h-64">
              <canvas ref="lineChartCanvas" width="400" height="200"></canvas>
            </div>
          </div>
          
          <!-- Doughnut Chart -->
          <div class="bg-gradient-to-br from-muted/10 to-muted/30 rounded-lg p-4 shadow-lg border border-muted/20">
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

// Filter out driverStar from visible infractions
const filteredVisibleInfractions = computed(() => {
  const visible = {};
  sortedInfractions.value
    .filter(([key]) => key !== 'driverStar')
    .slice(0, 5)
    .forEach(([key, value]) => {
      visible[key] = value;
    });
  return visible;
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
  sortedInfractions.value
    .filter(([key]) => key !== 'driverStar')
    .slice(5)
    .forEach(([key, value]) => {
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



// Dummy data for doughnut chart
const doughnutChartData = [Math.round(props.data.alerts.distractedDriving), Math.round(props.data.alerts.speeding), Math.round(props.data.alerts.signViolation), Math.round(props.data.alerts.trafficLightViolation), Math.round(props.data.alerts.followingDistance)];
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
      // Get the data values for the line chart
      const dataValues = props.data.lineChartData?.map(item => item.greenZoneScore) || [];
      
      // Calculate min and max values for Y-axis
      const minValue = dataValues.length > 0 ? Math.min(...dataValues) : 0;
      const maxValue = dataValues.length > 0 ? Math.max(...dataValues) : 100;
      
      // Add some padding to the min/max values (10% of the range)
      const range = maxValue - minValue;
      const padding = range * 0.1;
      const yMin = Math.max(0, minValue - padding); // Don't go below 0
      const yMax = maxValue + padding;
      
      lineChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: props.data.lineChartData?.map(item => item.date) || [],
          datasets: [
            {
              label: 'Green Zone Score',
              data: dataValues,
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              tension: 0.3,
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          scales: {
            y: {
              beginAtZero: false,
              min: Math.max(Math.floor(yMin-75), 0),
              max: Math.min(Math.ceil(yMax+75), 1050),
              ticks: {
                stepSize: Math.ceil(range / 5) // Create approximately 5 steps
              }
            }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(context) {
                  const score = context.raw || 0;
                  const constantValue = props.data.greenZoneScore || 0;
                  
                  // Use the dateRange label from the data instead of determining by data length
                  const timeFilter = props.data.dateRangeLabel || 'selected period';
                  
                  // Convert to number and then format with 2 decimal places
                  const formattedConstantValue = Number(constantValue).toFixed(2);
                  
                  return [
                    `Green Zone Score: ${score}`,
                    `Average Score Over ${timeFilter}: ${formattedConstantValue}`
                  ];
                }
              }
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
              '#061455', 
              '#1911B2', 
              '#678AC6', 
              '#EA0E45',
              '#FF9898'  
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
                  return `${label}: ${value}`;
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

// Add a deep watcher for the data prop to reinitialize charts when data changes
// Add a debounced version of initializeCharts
let chartUpdateTimeout = null;
const debouncedInitializeCharts = () => {
  if (chartUpdateTimeout) clearTimeout(chartUpdateTimeout);
  chartUpdateTimeout = setTimeout(() => {
    initializeCharts();
  }, 100);
};

// Update the data watcher to use the debounced function
watch(() => props.data, () => {
  if (showGraphs.value) {
    debouncedInitializeCharts();
  }
}, { deep: true });

// Initialize charts when component is mounted if graphs are shown
onMounted(() => {
  if (showGraphs.value) {
    setTimeout(() => {
      initializeCharts();
    }, 100);
  }
});
</script>