<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(
  defineProps<{
    class?: HTMLAttributes['class'];
    variant?: 'default' | 'destructive' | 'success';
  }>(),
  {
    variant: 'default',
  }
);

const variantStyles = {
  default: 'bg-background text-foreground',
  destructive: 'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
  success: 'border-green-500/50 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 [&>svg]:text-green-600',
};
</script>

<template>
  <div
    :class="
      cn(
        'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
        variantStyles[variant],
        props.class
      )
    "
    role="alert"
  >
    <slot />
  </div>
</template>