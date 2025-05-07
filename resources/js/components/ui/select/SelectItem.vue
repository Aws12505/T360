<script setup lang="ts">
import { SelectItem as SelectItemPrimitive } from 'radix-vue';
import { cn } from '@/lib/utils';
import { Check } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  value: string | number;
  class?: HTMLAttributes['class'];
  disabled?: boolean;
}>();
</script>

<template>
  <SelectItemPrimitive
    :value="value"
    :disabled="disabled"
    :class="
      cn(
        'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 text-foreground dark:text-white',
        props.class
      )
    "
  >
    <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
      <Check class="h-4 w-4" />
    </span>
    <slot />
  </SelectItemPrimitive>
</template>