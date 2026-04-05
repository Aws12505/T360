<template>
  <div
    class="mx-auto mb-6 max-w-[90vw] overflow-x-auto rounded-lg border bg-card p-4 shadow-sm md:max-w-[64vw] lg:max-w-full">
    <h3 class="mb-4 text-lg font-semibold">Outstanding Invoices Filter</h3>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <div>
        <Label for="min-invoice-amount">Minimum Invoice Amount ($)</Label>
        <Input id="min-invoice-amount" v-model.number="localMin" type="number" min="0" step="1"
          placeholder="Enter minimum amount" class="mt-1" />
      </div>

      <div>
        <Label for="outstanding-since">Outstanding Since</Label>

        <Popover v-model:open="outstandingDateOpen">
          <PopoverTrigger as-child>
            <Button id="outstanding-since" variant="outline" class="mt-1 w-full justify-start text-left font-normal">
              <CalendarIcon class="mr-2 h-4 w-4" />
              {{
                outstandingDatePicker
                  ? df.format(outstandingDatePicker.toDate(getLocalTimeZone()))
                  : "Pick a date"
              }}
            </Button>
          </PopoverTrigger>

          <PopoverContent class="w-auto p-0">
            <Calendar :model-value="outstandingDatePicker" layout="month-and-year"
              @update:model-value="handleOutstandingDateSelect" />
          </PopoverContent>
        </Popover>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { Button, Input, Label } from "@/components/ui";
import { CalendarIcon } from "lucide-vue-next";
import { DateFormatter, getLocalTimeZone, parseDate } from "@internationalized/date";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
const props = defineProps<{
  minInvoiceAmount: number | null;
  outstandingDate: string | null;
}>();
const outstandingDatePicker = ref(
  props.outstandingDate ? parseDate(props.outstandingDate) : null
);
const outstandingDateOpen = ref(false);

const df = new DateFormatter("en-US", { dateStyle: "medium" });
const emit = defineEmits<{
  (e: "update:minInvoiceAmount", v: number | null): void;
  (e: "update:outstandingDate", v: string | null): void;
}>();
const handleOutstandingDateSelect = (val) => {
  outstandingDatePicker.value = val ?? null;
  localDate.value = val
    ? val.toDate(getLocalTimeZone()).toISOString().split("T")[0]
    : null;
  outstandingDateOpen.value = false;
};
const localMin = computed({
  get: () => props.minInvoiceAmount,
  set: (v) => emit("update:minInvoiceAmount", v),
});

const localDate = computed({
  get: () => props.outstandingDate,
  set: (v) => emit("update:outstandingDate", v),
});

watch(
  () => props.outstandingDate,
  (v) => {
    outstandingDatePicker.value = v ? parseDate(v) : null;
  }
);
</script>
