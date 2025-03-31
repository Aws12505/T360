<script setup lang="ts">
import { SelectRoot } from 'radix-vue';

const props = defineProps<{
  modelValue: any;
}>();

const emit = defineEmits(['update:modelValue']);

function updateValue(value: any) {
  emit('update:modelValue', value);
}
</script>

<template>
  <SelectRoot :model-value="modelValue" @update:model-value="updateValue">
    <slot />
  </SelectRoot>
</template>