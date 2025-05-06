<template>
  <div class="bg-background rounded-lg border shadow-sm p-4 flex flex-col items-center justify-center">
    <h3 class="text-sm font-semibold mb-2 text-foreground">{{ title }}</h3>
    <p class="text-2xl font-bold text-primary">{{ value }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  }
});
</script>