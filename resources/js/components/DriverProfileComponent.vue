<template>
  <div class="min-h-screen bg-background p-4 md:p-6">
    <!-- BACK BUTTON -->
    <div class="mb-4">
      <button
        @click="goBack"
        class="inline-flex items-center text-sm text-primary hover:underline"
      >
        <ArrowLeft class="h-4 w-4 mr-1" />
        Back
      </button>
    </div>

    <div class="container max-w-6xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold">Driver Profile</h1>
          <p class="text-muted-foreground mt-1">Detailed performance metrics</p>
        </div>
        <div class="flex items-center border rounded-full px-3 py-1 text-sm mt-2 md:mt-0">
          <Calendar class="mr-1 h-4 w-4" />
          <span>Last updated: {{ formattedLastUpdated }}</span>
        </div>
        <DateFilter
          v-if="driverID"
          :driver="driver"
          :tenantSlug="tenantSlug"
          :driverID="driverID"
        />
      </div>

      <!-- Profile Card -->
      <div class="bg-card rounded-lg border border-muted/30 shadow-sm">
        <div class="flex flex-col md:flex-row gap-6 p-6">
          <!-- LEFT: Overview -->
          <div class="md:w-1/3 flex flex-col items-center space-y-4">
            <div class="relative">
              <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-primary">
                <template v-if="driver.image">
                  <img
                    :src="driver.image"
                    alt="Driver"
                    class="w-full h-full object-cover object-center"
                  />
                </template>
                <template v-else>
                  <div class="w-full h-full bg-muted/20 flex items-center justify-center">
                    <div class="text-3xl font-bold text-primary">
                      {{ getInitials(driver.name) }}
                    </div>
                  </div>
                </template>
              </div>
              <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                <div class="px-2 py-1 text-sm font-bold bg-primary text-white rounded-full text-center">
                  Rank #{{ driver.rank }}
                </div>
              </div>
            </div>

            <div class="text-center mt-6 space-y-2">
              <h2 class="text-xl font-bold">{{ driver.name }}</h2>
              <div class="flex items-center justify-center text-muted-foreground">
                <Phone class="mr-2 h-4 w-4" />
                <span>+1 {{ driver.phone }}</span>
              </div>
              <div class="flex items-center justify-center text-muted-foreground">
                <Mail class="mr-2 h-4 w-4" />
                <span>{{ driver.email }}</span>
              </div>
              <div class="pt-4">
                <div class="text-sm text-muted-foreground">Hire Date</div>
                <div class="font-medium">{{ formatHireDate(driver.hireDate) }}</div>
              </div>
            </div>
          </div>

          <!-- RIGHT: Metrics -->
          <div class="md:w-2/3 space-y-6">
            <template v-if="driver.rank > 0">
              <!-- Scores -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-primary/10 border border-primary/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <Star class="mr-2 h-5 w-5 text-primary" />
                    <h3 class="font-semibold text-primary">Overall Score</h3>
                  </div>
                  <div class="text-2xl font-bold mt-2 text-primary">{{ driver.overall_score }}</div>
                </div>
                <div class="bg-success/10 border border-success/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <Clock class="mr-2 h-5 w-5 text-success" />
                    <h3 class="font-semibold text-success">On-Time Score</h3>
                  </div>
                  <div
                    class="text-2xl font-bold mt-2 text-success"
                    :class="getPerformanceScoreColorClass(driver.on_time_score)"
                  >
                    {{ driver.on_time_score }}%
                  </div>
                </div>
                <div class="bg-warning/10 border border-warning/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <CheckCircle class="mr-2 h-5 w-5 text-warning" />
                    <h3 class="font-semibold text-warning">Acceptance Score</h3>
                  </div>
                  <div
                    class="text-2xl font-bold mt-2 text-warning"
                    :class="getPerformanceScoreColorClass(driver.acceptance_score)"
                  >
                    {{ driver.acceptance_score }}%
                  </div>
                </div>
              </div>

              <!-- Green Zone -->
              <div class="bg-success/10 border border-success/20 rounded-lg p-4">
                <div class="flex justify-between items-center">
                  <h3 class="font-semibold text-success flex items-center">
                    <Shield class="mr-2 h-5 w-5" />
                    Green Zone Score
                  </h3>
                  <div
                    class="text-2xl font-bold text-success"
                    :class="getSafetyScoreColorClass(driver.greenZoneScore)"
                  >
                    {{ driver.greenZoneScore }}
                  </div>
                </div>
                <div class="mt-2 h-2 w-full bg-success/20 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-success rounded-full"
                    :style="{ width: `${driver.greenZoneScore}%` }"
                  ></div>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <Clock class="mr-2 h-5 w-5 text-blue-500" />
                      <div>
                        <h3 class="font-semibold">Minutes Analyzed</h3>
                        <p class="text-sm text-muted-foreground">Total driving time analyzed</p>
                      </div>
                    </div>
                    <div class="text-2xl font-bold">{{ driver.minutesAnalyzed }}</div>
                  </div>
                </div>
                <div class="border rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <Truck class="mr-2 h-5 w-5 text-indigo-500" />
                      <div>
                        <h3 class="font-semibold"># of Trips</h3>
                        <p class="text-sm text-muted-foreground">Completed trips</p>
                      </div>
                    </div>
                    <div class="text-2xl font-bold">{{ driver.trips }}</div>
                  </div>
                </div>
              </div>

              <!-- Infractions -->
              <div class="border rounded-lg">
                <div class="border-b p-4">
                  <h3 class="font-semibold flex items-center">
                    <XCircle class="mr-2 h-5 w-5 text-destructive" />
                    Safety Alerts
                  </h3>
                </div>
                <div class="p-4 space-y-3">
                  <div
                    v-for="(value, key) in driver.infractions"
                    :key="key"
                    class="flex justify-between items-center py-2 border-b border-muted/20 last:border-0"
                  >
                    <div class="flex items-center">
                      <div
                        class="bg-destructive/10 text-destructive rounded-full w-8 h-8 flex items-center justify-center mr-3"
                      >
                        <XCircle class="h-4 w-4" />
                      </div>
                      <span>{{ formatInfractionLabel(key) }}</span>
                    </div>
                    <span class="font-bold">{{ value }}</span>
                  </div>
                </div>
              </div>
            </template>

            <!-- Empty Data Block -->
            <template v-else>
              <div class="border border-dashed border-muted/40 bg-muted/10 text-center text-muted-foreground rounded-lg p-8">
                <h2 class="text-xl font-semibold mb-2">No data available</h2>
                <p>This driver has no performance data for the selected date filter.</p>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useInitials } from '@/composables/useInitials'
import {
  ArrowLeft,
  Calendar,
  Phone,
  Mail,
  Star,
  Clock,
  CheckCircle,
  Shield,
  Truck,
  XCircle,
} from 'lucide-vue-next'
import DateFilter from '@/components/DateFilterSwitcher.vue'

const props = defineProps({
  driver: { type: Object, required: true },
  tenantSlug: String,
  initialFilter: { type: String, default: 'full' },
  driverID: Number,
})

const { getInitials } = useInitials()

const driversHref = computed(() => {
  return props.tenantSlug
    ? route('driver.index', { tenantSlug: props.tenantSlug })
    : route('driver.index.admin')
})

function goBack() {
  if (driversHref.value) {
    router.get(driversHref.value)
  }
}

function formatInfractionLabel(key) {
  switch (key) {
    case 'speeding':
      return 'Speeding'
    case 'distraction':
      return 'Distracted Driving'
    case 'sign':
      return 'Sign Violation'
    case 'light':
      return 'Traffic Light Violation'
    case 'following':
      return 'Following Distance'
    default:
      return key
  }
}

const formatDate = (date) =>
  new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)

const formattedLastUpdated = computed(() => {
  return props.driver.last_updated
    ? formatDate(new Date(props.driver.last_updated))
    : '-'
})

function formatHireDate(dateString) {
  const [year, month, day] = dateString.split('-')
  return `${month}/${day}/${year}`
}

const getPerformanceScoreColorClass = (score) => {
  if (score >= 75) return 'text-green-600'
  if (score >= 50) return 'text-amber-600'
  return 'text-red-600'
}

const getSafetyScoreColorClass = (rating) => {
  if (rating >= 900) return 'text-green-600'
  if (rating >= 750) return 'text-emerald-600'
  if (rating >= 600) return 'text-blue-600'
  return 'text-red-600'
}
</script>
