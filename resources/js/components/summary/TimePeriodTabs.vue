<template>
  <div class="mb-6">
    <div class="border-b">
      <div class="flex -mb-px space-x-8">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="handleTabChange(tab.id)"
          class="py-2 px-1 border-b-2 text-sm font-medium transition-colors"
          :class="activeTab === tab.id ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground'"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>
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
  { id: 'quarterly', label: 'Quarterly Scores' }
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

// Emit the default tab on component mount
onMounted(() => {
  emit('tab-change', activeTab.value);
});
</script>