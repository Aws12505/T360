<template>
    <div class="relative min-h-screen bg-background flex items-center justify-center p-4">
      
      <!-- Logout button -->
      <div class="absolute top-4 right-4">
        <Button @click="logout" variant="destructive" size="sm">
          Logout
        </Button>
      </div>
  
      <!-- Profile Card -->
      <Card class="p-4 md:p-6 flex flex-col md:flex-row items-center md:items-start gap-6 border-muted/30 shadow-sm">
        
        <!-- Profile image -->
        <div class="flex flex-col items-center space-y-3 md:w-1/3">
          <img
            :src="driver.image || placeholderImage"
            alt="Driver Image"
            class="w-32 h-32 rounded-full object-cover border border-muted"
          />
          <div class="text-center space-y-1">
            <div class="font-medium text-lg">{{ driver.phone }}</div>
            <div class="text-muted-foreground text-sm">{{ driver.email }}</div>
            <div class="text-sm">Hire Date: {{ driver.hireDate }}</div>
            <div class="text-sm text-muted-foreground">Company: {{ driver.tenant }}</div>
          </div>
          <div class="bg-primary text-white px-4 py-2 rounded-lg font-bold text-xl mt-2">
            Rank #{{ driver.rank }}
          </div>
        </div>
  
        <!-- Performance Metrics -->
        <div class="flex-1 space-y-6 w-full">
  
          <!-- Overall Rating -->
          <div class="text-center text-2xl font-bold text-green-600 border rounded py-2">
            Overall Score: {{ driver.overall_score }}
          </div>
  
          <!-- Two buttons row -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-muted/20 border border-muted/30 rounded py-3 text-center font-semibold">
              On-Time Score: {{ driver.on_time_score }}
            </div>
            <div class="bg-muted/20 border border-muted/30 rounded py-3 text-center font-semibold">
              Acceptance Score: {{ driver.acceptance_score }}
            </div>
          </div>
  
          <!-- Green Zone Score -->
          <div class="bg-muted/20 border border-muted/30 rounded py-3 text-center font-semibold text-lg">
            Green Zone Score: {{ driver.greenZoneScore }}
          </div>
  
          <!-- Infractions List -->
          <div class="bg-muted/20 border border-muted/30 rounded p-4 space-y-2">
            <div class="font-semibold mb-2">Infractions</div>
            <div v-for="(value, key) in driver.infractions" :key="key" class="flex justify-between text-sm">
              <span>{{ formatInfractionLabel(key) }}</span>
              <span class="font-medium">{{ value }}</span>
            </div>
          </div>
  
          <!-- Footer Metrics Row -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-muted/20 border border-muted/30 rounded py-3 text-center font-semibold">
              Minutes Analyzed: {{ driver.minutesAnalyzed }}
            </div>
            <div class="bg-muted/20 border border-muted/30 rounded py-3 text-center font-semibold">
              # of Trips: {{ driver.trips }}
            </div>
          </div>
  
        </div>
      </Card>
  
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  import { Button, Card } from '@/components/ui';
  import { router } from '@inertiajs/vue3';
  
  // Props from backend (from Controller)
  const props = defineProps({
    driverData: Object
  });
  
  // Use computed so it's reactive
  const driver = computed(() => props.driverData);
  
  // Placeholder image
  const placeholderImage = '/images/avatar-placeholder.png';
  
  // Helper to format infraction labels
  const formatInfractionLabel = (key) => {
    return key
      .replace(/([A-Z])/g, ' $1')
      .replace(/^./, (str) => str.toUpperCase());
  };
  
  // Logout method
  const logout = () => {
    router.post(route('logout'), {}, {
      onSuccess: () => {
        // Optionally you can redirect or show message
        console.log('Logged out');
      }
    });
  };
  </script>
  
  <style scoped>
  /* Optional: add any additional styling */
  </style>
  