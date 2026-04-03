<template>
  <div class="mb-4">

    <!-- Tabs -->
    <div class="overflow-x-auto">
      <div class="flex gap-2 border-b pb-2 min-w-max">
        <button v-for="tab in tabs" :key="tab.id" @click="handleTabChange(tab.id)"
          class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-all" :class="activeTab === tab.id
            ? 'bg-primary text-white shadow-sm'
            : 'text-muted-foreground hover:bg-muted'">
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Date -->
    <div v-if="dateRangeText" class="mt-2 text-sm text-muted-foreground">
      <span>{{ dateRangeText }}</span>
      <span v-if="weekNumberText" class="ml-1">({{ weekNumberText }})</span>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
  dateRangeText: {
    type: String,
    default: ''
  },
  weekNumber: {
    type: Number,
    default: null
  },
  startWeekNumber: {
    type: Number,
    default: null
  },
  endWeekNumber: {
    type: Number,
    default: null
  },
  year: {
    type: Number,
    default: null
  },
  activeTabId: {
    type: String,
    default: 'yesterday'
  }
});

const tabs = [
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'current-week', label: 'WTD' },
  { id: 't6w', label: 'T6W Scores' },
  { id: 'quarterly', label: 'Quarterly Scores' },
  { id: 'custom', label: 'Custom' }
];

const activeTab = ref(props.activeTabId);
const emit = defineEmits(['tab-change']);

const weekNumberText = computed(() => {
  // For yesterday and current-week, show single week
  if ((activeTab.value === 'yesterday' || activeTab.value === 'current-week') && props.weekNumber && props.year) {
    return `Week ${props.weekNumber}, ${props.year}`;
  }

  // For t6w and quarterly, show start-end week range if available
  if ((activeTab.value === 't6w' || activeTab.value === 'quarterly') &&
    props.startWeekNumber && props.endWeekNumber && props.year) {
    return `Weeks ${props.startWeekNumber}-${props.endWeekNumber}, ${props.year}`;
  }

  return '';
});

// Handle tab change and emit the event to parent
const handleTabChange = (tabId: string) => {
  activeTab.value = tabId;
  emit('tab-change', tabId);
};


</script>