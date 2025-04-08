<template>
  <div
    :class="[
      'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
      variantClasses[variant],
      sizeClasses[size],
      className
    ]"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value: string) => {
      return ['default', 'secondary', 'destructive', 'outline'].includes(value);
    }
  },
  size: {
    type: String,
    default: 'default',
    validator: (value: string) => {
      return ['default', 'sm', 'lg'].includes(value);
    }
  },
  className: {
    type: String,
    default: ''
  }
});

const variantClasses = {
  default: 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
  secondary: 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
  destructive: 'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80',
  outline: 'text-foreground'
};

const sizeClasses = {
  default: 'text-xs',
  sm: 'text-xs',
  lg: 'text-sm'
};
</script>