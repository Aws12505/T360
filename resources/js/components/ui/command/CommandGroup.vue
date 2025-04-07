<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  heading?: string;
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <div
    cmdk-group=""
    :class="cn('overflow-hidden p-1 text-foreground', props.class)"
    role="group"
  >
    <div v-if="heading" cmdk-group-heading="" class="px-2 py-1.5 text-xs font-medium text-muted-foreground">
      {{ heading }}
    </div>
    <slot />
  </div>
</template>