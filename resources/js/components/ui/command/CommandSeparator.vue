<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <div cmdk-separator="" :class="cn('-mx-1 h-px bg-border', props.class)" role="separator" />
</template>