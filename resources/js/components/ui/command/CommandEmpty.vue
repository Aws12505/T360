<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <div
    cmdk-empty=""
    :class="cn('py-6 text-center text-sm', props.class)"
    role="presentation"
  >
    <slot />
  </div>
</template>