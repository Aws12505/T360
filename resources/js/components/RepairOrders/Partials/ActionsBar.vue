<template>
  <div
    class="flex flex-col sm:flex-row justify-between items-center px-2 mb-2 md:mb-4 lg:mb-6 space-y-2 sm:space-y-0"
  >
    <div class="flex items-center gap-3">
      <Icon name="clipboard-list" class="h-6 w-6 text-primary hidden sm:block" />
      <h1
        class="text-lg md:text-xl lg:text-2xl font-bold text-gray-800 dark:text-gray-200"
      >
        Repair Orders
      </h1>
    </div>

    <div class="flex flex-wrap gap-3">
      <Button
        @click="$emit('openCreateModal')"
        variant="default"
        v-if="permissionNames.includes('repair-orders.create')"
        class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
      >
        <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" /> Create New
      </Button>

      <Button
        v-if="selectedIds.length && permissionNames.includes('repair-orders.delete')"
        @click="$emit('confirmBulkDelete')"
        variant="destructive"
        class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
      >
        <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" /> Delete Selected ({{
          selectedIds.length
        }})
      </Button>

      <Button
        v-if="isAdmin"
        @click="$emit('openAreasModal')"
        variant="outline"
        class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
      >
        <Icon name="settings" class="mr-1 h-4 w-4 md:mr-2" /> Areas
      </Button>

      <Button
        v-if="isAdmin"
        @click="$emit('openVendorsModal')"
        variant="outline"
        class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
      >
        <Icon name="settings" class="mr-1 h-4 w-4 md:mr-2" /> Vendors
      </Button>

      <Button
        v-if="isAdmin"
        @click="$emit('openStatusModal')"
        variant="outline"
        class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
      >
        <Icon name="settings" class="mr-1 h-4 w-4 md:mr-2" /> Statuses
      </Button>

      <Button
        @click="$emit('openImportModal')"
        v-if="permissionNames.includes('repair-orders.import')"
        variant="secondary"
        class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
      >
        <Icon name="upload" class="mr-1 h-4 w-4 md:mr-2" />
        Import CSV
      </Button>

      <Button
        v-if="permissionNames.includes('repair-orders.export')"
        @click.prevent="$emit('exportCsv')"
        variant="outline"
        class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
      >
        <Icon name="download" class="mr-1 h-4 w-4 md:mr-2" /> Download CSV
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon from "@/components/Icon.vue";
import { Button } from "@/components/ui";

defineProps<{
  permissionNames: string[];
  selectedIds: number[];
  isAdmin: boolean;
}>();

defineEmits<{
  (e: "openCreateModal"): void;
  (e: "confirmBulkDelete"): void;
  (e: "openAreasModal"): void;
  (e: "openVendorsModal"): void;
  (e: "openStatusModal"): void;
  (e: "openImportModal"): void;
  (e: "exportCsv"): void;
}>();
</script>
