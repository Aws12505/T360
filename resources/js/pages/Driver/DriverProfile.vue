<template>
  <Head title="Driver Profile" />
  <div class="relative min-h-screen bg-background p-4 md:p-6">
    <!-- Logout and Appearance Switcher -->
    <div class="absolute top-4 right-4 flex items-center gap-4">
      <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
        <button
          v-for="{ value, name } in tabs"
          :key="value"
          @click="updateAppearance(value)"
          :class="[
            'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
            appearance === value
              ? 'bg-white shadow-sm dark:bg-neutral-700 dark:text-neutral-100'
              : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
          ]"
        >
          <Icon :name="name" class="-ml-1 h-4 w-4" />
        </button>

        <!-- Logout -->
        <button 
          @click="logout"
          class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-background text-destructive hover:bg-destructive/10 h-9 px-3"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          Logout
        </button>
      </div>
    </div>

    <div class="container max-w-6xl mx-auto mt-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold">Driver Profile</h1>
          <p class="text-muted-foreground mt-1">Detailed performance metrics</p>
        </div>
        <div class="flex items-center border rounded-full px-3 py-1 text-sm mt-2 md:mt-0">
          <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
          </svg>
          Last updated: {{ formattedLastUpdated }}
        </div>
        <DateFilter
          v-if="driverData"
          :driverData="driverData"
        />
      </div>

      <!-- Profile Card -->
      <div class="bg-card rounded-lg border border-muted/30 shadow-sm">
        <div class="flex flex-col md:flex-row gap-6 p-6">
          <!-- LEFT: Overview -->
          <div class="md:w-1/3 flex flex-col items-center space-y-4">
            <div class="relative">
              <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-primary bg-muted/20 flex items-center justify-center">
                <template v-if="driver.image">
                  <img :src="driver.image" alt="Driver" class="w-full h-full object-cover" />
                </template>
                <template v-else>
                  <div class="text-3xl font-bold text-primary">
                    {{ getInitials(driver.name) }}
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
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"><path d="M22 16.92v3a2..." /></svg>
                +1 {{ driver.phone }}
              </div>
              <div class="flex items-center justify-center text-muted-foreground">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"><path d="M4 4h16c1.1..." /></svg>
                {{ driver.email }}
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
              <!-- Score Cards -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-primary/10 border border-primary/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <svg class="mr-2 h-5 w-5 text-primary" fill="none" stroke="currentColor"><polygon points="12 2..." /></svg>
                    <h3 class="font-semibold text-primary">Overall Score</h3>
                  </div>
                  <div class="text-2xl font-bold mt-2 text-primary">{{ driver.overall_score }}</div>
                </div>

                <div class="bg-success/10 border border-success/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <svg class="mr-2 h-5 w-5 text-success" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <h3 class="font-semibold text-success">On-Time Score</h3>
                  </div>
                  <div class="text-2xl font-bold mt-2 text-success" :class="getPerformanceScoreColorClass(driver.on_time_score)">
                    {{ driver.on_time_score }}%
                  </div>
                </div>

                <div class="bg-warning/10 border border-warning/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <svg class="mr-2 h-5 w-5 text-warning" fill="none" stroke="currentColor"><path d="..." /></svg>
                    <h3 class="font-semibold text-warning">Acceptance Score</h3>
                  </div>
                  <div class="text-2xl font-bold mt-2 text-warning" :class="getPerformanceScoreColorClass(driver.acceptance_score)">
                    {{ driver.acceptance_score }}%
                  </div>
                </div>
              </div>

              <!-- Green Zone -->
              <div class="bg-success/10 border border-success/20 rounded-lg p-4">
                <div class="flex justify-between items-center">
                  <h3 class="font-semibold text-success flex items-center">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor"><path d="..." /></svg>
                    Green Zone Score
                  </h3>
                  <div class="text-2xl font-bold text-success" :class="getSafetyScoreColorClass(driver.greenZoneScore)">
                    {{ driver.greenZoneScore }}
                  </div>
                </div>
                <div class="mt-2 h-2 w-full bg-success/20 rounded-full overflow-hidden">
                  <div class="h-full bg-success rounded-full" :style="{ width: `${driver.greenZoneScore}%` }"></div>
                </div>
              </div>

              <!-- Stats Row -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <svg class="mr-2 h-5 w-5 text-blue-500" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
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
                      <svg class="mr-2 h-5 w-5 text-indigo-500" fill="none" stroke="currentColor"><path d="..." /></svg>
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
                    <svg class="mr-2 h-5 w-5 text-destructive" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Safety Alerts
                  </h3>
                </div>
                <div class="p-4 space-y-3">
                  <div v-for="(value, key) in driver.infractions" :key="key" class="flex justify-between items-center py-2 border-b border-muted/20 last:border-0">
                    <div class="flex items-center">
                      <div class="bg-destructive/10 text-destructive rounded-full w-8 h-8 flex items-center justify-center mr-3">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor"><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                      </div>
                      <span>{{ formatInfractionLabel(key) }}</span>
                    </div>
                    <span class="font-bold">{{ value }}</span>
                  </div>
                </div>
              </div>
            </template>

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
import { Head, router } from '@inertiajs/vue3'
import { useInitials } from '@/composables/useInitials'
import { useAppearance } from '@/composables/useAppearance'
import Icon from '@/Components/Icon.vue'
import DateFilter from '@/components/DateFilterSwitcherPage.vue'

const props = defineProps({
  driverData: Object,
})

const driver = computed(() => props.driverData)
const { getInitials } = useInitials()
const { appearance, updateAppearance } = useAppearance()

const tabs = [
  { value: 'light', name: 'sun' },
  { value: 'dark', name: 'moon' },
]

function formatHireDate(dateString) {
  const [year, month, day] = dateString.split("-")
  return `${month}/${day}/${year}`
}

function formatInfractionLabel(key) {
  const labels = {
    speeding: 'Speeding',
    distraction: 'Distracted Driving',
    sign: 'Sign Violation',
    light: 'Traffic Light Violation',
    following: 'Following Distance'
  }
  return labels[key] ?? key
}

const formatDate = (date) =>
  new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)

const formattedLastUpdated = computed(() => {
  return driver.value.last_updated ? formatDate(new Date(driver.value.last_updated)) : '-'
})

const logout = () => {
  router.post(route('logout'))
}

const getPerformanceScoreColorClass = (score) => {
  if (score >= 75) return 'text-green-600'
  if (score >= 50) return 'text-amber-600'
  return 'text-red-600'
}

const getSafetyScoreColorClass = (score) => {
  if (score >= 900) return 'text-green-600'
  if (score >= 750) return 'text-emerald-600'
  if (score >= 600) return 'text-blue-600'
  return 'text-red-600'
}
</script>
