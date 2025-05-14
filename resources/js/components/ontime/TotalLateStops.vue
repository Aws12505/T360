<template>
  <div class="bg-background rounded-lg border shadow-sm p-2 md:p-4 lg:p-6 flex flex-col items-center justify-center">
    <h3 class="text-base font-semibold mb-2 text-foreground">{{ title }}</h3>
    <p class="text-2xl font-bold text-primary">{{ displayValue }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  }
});

const displayValue = computed(() => {
  if (props.value === null || props.value === undefined || props.value === '') {
    return 'No Data';
  }
  return props.value;
});
</script>