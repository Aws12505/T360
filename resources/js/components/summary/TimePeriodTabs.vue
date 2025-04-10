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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

const tabs = [
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'current-week', label: 'Current Week' },
  { id: 't6w', label: 'T6W Scores' },
  { id: 'quarterly', label: 'Quarterly Scores' }
];

const activeTab = ref('yesterday');
const emit = defineEmits(['tab-change']);

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