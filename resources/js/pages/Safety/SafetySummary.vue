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
    </CardContent>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Card, CardContent, Button } from '@/components/ui';
import Icon from '@/components/Icon.vue';

// Props definition
const props = defineProps({
  data: {
    type: Object,
    
  }
});

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
</script>