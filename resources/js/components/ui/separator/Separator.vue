<script setup lang="ts">
import { Separator as SeparatorPrimitive } from 'radix-vue';
import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'vue';

const props = withDefaults(
  defineProps<{
    orientation?: 'horizontal' | 'vertical';
    decorative?: boolean;
    class?: HTMLAttributes['class'];
  }>(),
  {
    orientation: 'horizontal',
    decorative: true,
  }
);
</script>

<template>
  <SeparatorPrimitive
    :orientation="props.orientation"
    :decorative="props.decorative"
    :class="
      cn(
        'shrink-0 bg-border',
        props.orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]',
        props.class
      )
    "
  />
</template>
