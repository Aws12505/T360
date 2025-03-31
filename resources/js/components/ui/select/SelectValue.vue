<script setup lang="ts">
import { SelectValue as SelectValuePrimitive } from 'radix-vue';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
  placeholder?: string;
}>();
</script>

<template>
  <SelectValuePrimitive :placeholder="placeholder" :class="cn('text-sm', props.class)">
    <slot />
  </SelectValuePrimitive>
</template>