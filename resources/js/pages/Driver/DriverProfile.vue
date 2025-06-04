<template>
  <Head title="Driver Profile" />
  <div class="relative min-h-screen bg-background p-4 md:p-6">
    <!-- Logout and Appearance Switcher -->
    <div class="absolute top-4 right-4 flex items-center gap-4">
      <!-- Sun/Moon Toggle (Appearance Switcher) -->
      <div
        :class="['inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800']"
      >
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

        <!-- Logout button -->
      <button 
        @click="logout"
        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-background text-destructive hover:bg-destructive/10 h-9 px-3"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        Logout
      </button>
      </div>
    </div>

    <div class="container max-w-6xl mx-auto mt-6">
      <!-- Profile Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold">Driver Profile</h1>
          <p class="text-muted-foreground mt-1">Detailed performance metrics</p>
        </div>
        <div class="flex items-center border rounded-full px-3 py-1 text-sm mt-2 md:mt-0">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1 h-4 w-4">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          Last updated: {{ formattedLastUpdated }}
        </div>
      </div>

      <!-- Profile Card -->
      <div class="bg-card rounded-lg border border-muted/30 shadow-sm">
        <div class="flex flex-col md:flex-row gap-6 p-6">
          <!-- Profile Overview -->
          <div class="md:w-1/3 flex flex-col items-center space-y-4">
            <!-- Profile Image -->
            <div class="relative">
              <div class="w-32 h-32 rounded-full bg-muted/20 flex items-center justify-center overflow-hidden border-2 border-primary">
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

            <!-- Driver Info -->
            <div class="text-center mt-6 space-y-2">
              <h2 class="text-xl font-bold">{{ driver.name }}</h2>
              <div class="flex items-center justify-center text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                +1 {{ driver.phone }}
              </div>
              <div class="flex items-center justify-center text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                {{ driver.email }}
              </div>
              <div class="pt-4">
                <div class="text-sm text-muted-foreground">Hire Date</div>
                <div class="font-medium">{{ driver.hireDate }}</div>
              </div>
              <div class="pt-1">
                <div class="text-sm text-muted-foreground">Company</div>
                <div class="font-medium">{{ driver.tenant }}</div>
              </div>
            </div>
          </div>

          <!-- Performance Metrics -->
          <div class="md:w-2/3 space-y-6">
            <!-- Score Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Overall Score -->
              <div class="bg-primary/10 border border-primary/20 rounded-lg p-4">
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-primary">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                  </svg>
                  <h3 class="font-semibold text-primary">Overall Score</h3>
                </div>
                <div class="text-2xl font-bold mt-2 text-primary">{{ driver.overall_score }}</div>
              </div>
              
              <!-- On-Time Score -->
              <div class="bg-success/10 border border-success/20 rounded-lg p-4">
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-success">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                  </svg>
                  <h3 class="font-semibold text-success">On-Time Score</h3>
                </div>
                <div class="text-2xl font-bold mt-2 text-success">{{ driver.on_time_score }}</div>
              </div>
              
              <!-- Acceptance Score -->
              <div class="bg-warning/10 border border-warning/20 rounded-lg p-4">
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-warning">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                  </svg>
                  <h3 class="font-semibold text-warning">Acceptance Score</h3>
                </div>
                <div class="text-2xl font-bold mt-2 text-warning">{{ driver.acceptance_score }}</div>
              </div>
            </div>

            <!-- Green Zone Card -->
            <div class="bg-success/10 border border-success/20 rounded-lg p-4">
              <div class="flex justify-between items-center">
                <h3 class="font-semibold text-success flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                  </svg>
                  Green Zone Score
                </h3>
                <div class="text-2xl font-bold text-success">
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
              <!-- Minutes Analyzed -->
              <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-blue-500">
                      <circle cx="12" cy="12" r="10"></circle>
                      <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <div>
                      <h3 class="font-semibold">Minutes Analyzed</h3>
                      <p class="text-sm text-muted-foreground">Total driving time analyzed</p>
                    </div>
                  </div>
                  <div class="text-2xl font-bold">{{ driver.minutesAnalyzed }}</div>
                </div>
              </div>
              
              <!-- # of Trips -->
              <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-indigo-500">
                      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                      <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <div>
                      <h3 class="font-semibold"># of Trips</h3>
                      <p class="text-sm text-muted-foreground">Completed trips</p>
                    </div>
                  </div>
                  <div class="text-2xl font-bold">{{ driver.trips }}</div>
                </div>
              </div>
            </div>

            <!-- Infractions Section -->
            <div class="border rounded-lg">
              <div class="border-b p-4">
                <h3 class="font-semibold flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-destructive">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12" y2="17"></line>
                  </svg>
                  Infractions
                </h3>
              </div>
              <div class="p-4 space-y-3">
                <div 
                  v-for="(value, key) in driver.infractions" 
                  :key="key"
                  class="flex justify-between items-center py-2 border-b border-muted/20 last:border-0"
                >
                  <div class="flex items-center">
                    <div class="bg-destructive/10 text-destructive rounded-full w-8 h-8 flex items-center justify-center mr-3">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                      </svg>
                    </div>
                    <span>{{ formatInfractionLabel(key) }}</span>
                  </div>
                  <span class="font-bold">{{ value }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useInitials } from '@/composables/useInitials';
import { useAppearance } from '@/composables/useAppearance';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
  driverData: Object,
  class: String,
});

const { appearance, updateAppearance } = useAppearance();

const tabs = [
  { value: 'light', name: 'sun' },
  { value: 'dark',  name: 'moon' },
];



const driver = computed(() => props.driverData);
const { getInitials } = useInitials();

const formatInfractionLabel = (key) => {
  return key
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, str => str.toUpperCase());
};

const formatDate = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
};

const formattedLastUpdated = computed(() => {
  if (!driver.value.last_updated) return '-';
  return formatDate(new Date(driver.value.last_updated));
});

const logout = () => {
  router.post(route('logout'));
};
</script>
