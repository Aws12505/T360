<script setup lang="ts">
import { DialogRoot as Dialog, DialogContent } from 'radix-vue';
import { cn } from '@/lib/utils';
import { computed, type HTMLAttributes } from 'vue';

const props = withDefaults(
  defineProps<{
    open?: boolean;
    defaultOpen?: boolean;
    modal?: boolean;
    class?: HTMLAttributes['class'];
    contentClass?: HTMLAttributes['class'];
    shouldFilter?: boolean;
    loop?: boolean;
  }>(),
  {
    shouldFilter: true,
    loop: true,
    modal: true,
  }
);

const emit = defineEmits(['update:open']);

const delegatedProps = computed(() => {
  const { class: _, contentClass: __, shouldFilter: ___, loop: ____, ...delegated } = props;
  return delegated;
});
</script>

<template>
  <Dialog 
    :open="props.open" 
    :defaultOpen="props.defaultOpen"
    :modal="props.modal"
    @update:open="emit('update:open', $event)"
  >
    <DialogContent
      :class="cn('overflow-hidden p-0 shadow-lg', props.contentClass)"
      role="dialog"
      aria-label="Command menu"
    >
      <div
        :class="cn(
          'flex h-full w-full flex-col overflow-hidden rounded-md bg-popover text-popover-foreground',
          props.class
        )"
        cmdk-root=""
      >
        <slot />
      </div>
    </DialogContent>
  </Dialog>
</template>