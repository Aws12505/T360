<template>
  <div
    v-if="
      hasCanceledQSInvoices &&
      !SuperAdmin &&
      permissionNames.includes('repair-orders.update')
    "
    class="mb-6"
  >
    <div
      class="rounded-md border-l-4 border-red-500 bg-red-50 p-4 shadow-sm dark:border-red-400 dark:bg-red-900/30"
    >
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div
            class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-800"
          >
            <Icon name="triangleAlert" class="h-6 w-6 text-red-600 dark:text-red-300" />
          </div>
        </div>
        <div class="ml-3">
          <h3 class="text-lg font-medium text-red-800 dark:text-red-300">
            Attention Required
          </h3>
          <div class="mt-1 text-sm text-red-700 dark:text-red-200">
            {{ canceledQSInvoices?.length || 0 }} invoices found with canceled WO status
            but still on QS. These need immediate attention.
          </div>
          <div class="mt-2">
            <Button @click="$emit('openDialog')" variant="destructive" size="sm">
              View Details
            </Button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon from "@/components/Icon.vue";
import { Button } from "@/components/ui";

defineProps<{
  hasCanceledQSInvoices: boolean;
  SuperAdmin: boolean;
  permissionNames: string[];
  canceledQSInvoices: any[];
}>();

defineEmits<{
  (e: "openDialog"): void;
}>();
</script>
