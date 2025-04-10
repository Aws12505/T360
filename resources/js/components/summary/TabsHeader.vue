<template>
  <div class="flex border-b mb-6">
    <button
      v-for="tab in tabs"
      :key="tab.id"
      @click="() => selectTab(tab.id)"
      class="px-4 py-2 font-medium text-sm transition-colors"
      :class="[
        activeTab === tab.id 
          ? 'text-primary border-b-2 border-primary' 
          : 'text-muted-foreground hover:text-foreground'
      ]"
    >
      {{ tab.label }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const tabs = [
  { id: 'on-time', label: 'On-Time' },
  { id: 'acceptance', label: 'Acceptance' },
  { id: 'safety', label: 'Safety' }
];

const activeTab = ref('on-time');
const emit = defineEmits(['tab-change']);

const selectTab = (tabId) => {
  activeTab.value = tabId;
  emit('tab-change', tabId);
};

// Expose activeTab to parent components
defineExpose({ activeTab });
</script>