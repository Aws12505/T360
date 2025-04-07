<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  disabled?: boolean;
  onSelect?: (value: string) => void;
  class?: HTMLAttributes['class'];
  value?: string;
}>();

const emit = defineEmits<{
  select: [value: string];
}>();

function handleSelect() {
  if (props.disabled) return;
  
  if (props.onSelect) {
    props.onSelect(props.value || '');
  }
  
  emit('select', props.value || '');
}
</script>

<template>
  <div
    cmdk-item=""
    :class="cn(
      'relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none aria-selected:bg-accent aria-selected:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      props.class
    )"
    :data-disabled="disabled ? '' : undefined"
    @click="handleSelect"
    role="option"
  >
    <slot />
  </div>
</template>