<template>
  <div class="mb-6">
    <div class="border-b">
      <div class="flex -mb-px space-x-8">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
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
import { ref } from 'vue';

const tabs = [
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'current-week', label: 'Current Week' },
  { id: 't8w', label: 'T8W Scores' },
  { id: 'quarterly', label: 'Quarterly Scores' }
];

const activeTab = ref('yesterday');

defineEmits(['tab-change']);
</script>