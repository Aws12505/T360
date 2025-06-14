<template>
  <Transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-background rounded-lg shadow-xl max-w-md w-full p-6 animate-pop-in border">
        <div class="space-y-4">
          <!-- Title -->
          <div class="text-center">
            <h3 class="text-lg font-medium text-foreground">
              <slot name="title">Delete Confirmation</slot>
            </h3>
          </div>

          <!-- Message Preview Box -->
          <div class="mt-2">
            <p class="text-sm text-muted-foreground mb-4">
              Are you sure you want to delete this template? This action cannot be undone.
            </p>

            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 overflow-hidden">
              <div
                class="text-gray-700 dark:text-gray-300 font-medium text-sm leading-relaxed max-h-[4.5rem] overflow-hidden line-clamp-2"
                v-html="formattedPreview"
              />
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-3 pt-4">
            <Button @click="$emit('close')" variant="outline" class="px-4 py-2 text-sm">
              Cancel
            </Button>
            <Button @click="$emit('confirm')" variant="destructive" class="px-4 py-2 text-sm">
              Delete
            </Button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps({
  show: Boolean,
  template: {
    type: Object,
    default: () => ({})
  }
});

defineEmits(['close', 'confirm']);

// Map placeholders to user-friendly labels
const placeholderMap = {
  '{driver_first_name}': 'First Name',
  '{driver_last_name}': 'Last Name',
  '{driver_ontime_score}': 'On-Time %',
  '{driver_acceptance_score}': 'Acceptance %',
  '{driver_greenzone_score}': 'Green Zone',
  '{driver_severe_alerts}': 'Severe Alerts',
  '{company_avg_ontime}': 'Avg On-Time %',
  '{company_avg_acceptance}': 'Avg Acceptance %',
  '{company_avg_greenzone}': 'Avg Green Zone'
};

// Format message preview
const formattedPreview = computed(() => {
  const message = props.template?.coaching_message || '';

  // Replace placeholders with styled HTML tags
  const safe = message.replace(/{[^}]+}/g, match => {
    const label = placeholderMap[match] || match;
    return `<span class="inline-block bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded px-1.5 py-0.5 text-xs font-mono mx-0.5">&lt;${label}&gt;</span>`;
  });

  return safe;
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
@keyframes pop-in {
  0% {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
.animate-pop-in {
  animation: pop-in 0.2s ease-out;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
