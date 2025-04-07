<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <div
    cmdk-list=""
    :class="cn('max-h-[300px] overflow-y-auto overflow-x-hidden', props.class)"
    role="listbox"
  >
    <slot />
  </div>
</template>