<template>
  <div
    class="mx-auto mb-6 max-w-[90vw] overflow-x-auto rounded-lg border bg-card p-4 shadow-sm md:max-w-[64vw] lg:max-w-full"
  >
    <h3 class="mb-4 text-lg font-semibold">Outstanding Invoices Filter</h3>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <div>
        <Label for="min-invoice-amount">Minimum Invoice Amount ($)</Label>
        <Input
          id="min-invoice-amount"
          v-model.number="localMin"
          type="number"
          min="0"
          step="0.01"
          placeholder="Enter minimum amount"
          class="mt-1"
        />
      </div>

      <div>
        <Label for="outstanding-since">Outstanding Since</Label>
        <Input id="outstanding-since" v-model="localDate" type="date" class="mt-1" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Input, Label } from "@/components/ui";

const props = defineProps<{
  minInvoiceAmount: number | null;
  outstandingDate: string | null;
}>();

const emit = defineEmits<{
  (e: "update:minInvoiceAmount", v: number | null): void;
  (e: "update:outstandingDate", v: string | null): void;
}>();

const localMin = computed({
  get: () => props.minInvoiceAmount,
  set: (v) => emit("update:minInvoiceAmount", v),
});

const localDate = computed({
  get: () => props.outstandingDate,
  set: (v) => emit("update:outstandingDate", v),
});
</script>
