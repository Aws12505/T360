<script setup lang="ts">
import { PopoverTrigger as PopoverTriggerPrimitive, type PopoverTriggerProps } from 'radix-vue';

const props = defineProps<PopoverTriggerProps>();
</script>

<template>
  <PopoverTriggerPrimitive v-bind="props">
    <slot />
  </PopoverTriggerPrimitive>
</template>