<template>
  <Dialog v-model:open="openProxy">
    <DialogContent
      class="max-h-[90vh] max-w-[95vw] overflow-y-auto md:max-w-[80vw] lg:max-w-4xl"
    >
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2 text-lg md:text-xl">
          <Icon name="alert-triangle" class="h-5 w-5 text-red-600" />
          Canceled Invoices on QS
        </DialogTitle>
        <DialogDescription>
          These invoices have a canceled WO status but are still marked as on QS. Please
          review and take appropriate action.
        </DialogDescription>
      </DialogHeader>

      <div class="max-h-[60vh] overflow-x-auto overflow-y-auto">
        <Table v-if="canceledQSInvoices?.length">
          <TableHeader>
            <TableRow>
              <TableHead class="whitespace-nowrap">RO Number</TableHead>
              <TableHead class="whitespace-nowrap">Vendor</TableHead>
              <TableHead class="whitespace-nowrap text-right">Amount</TableHead>
              <TableHead class="whitespace-nowrap">Week</TableHead>

              <!-- ✅ NEW -->
              <TableHead class="whitespace-nowrap text-right">Action</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="invoice in canceledQSInvoices"
              :key="invoice.ro_number"
              class="hover:bg-red-50 dark:hover:bg-red-900/30"
            >
              <TableCell class="font-medium">
                <div class="flex items-center gap-2">
                  <Icon
                    name="alert-triangle"
                    class="h-4 w-4 text-red-600 dark:text-red-400"
                  />
                  {{ invoice.ro_number }}
                </div>
              </TableCell>

              <TableCell>{{ invoice.vendor_name }}</TableCell>

              <TableCell class="text-right">
                {{ formatCurrency(invoice.invoice_amount) }}
              </TableCell>

              <TableCell>W{{ invoice.week_number }}/{{ invoice.year }}</TableCell>

              <!-- ✅ NEW -->
              <TableCell class="text-right">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  class="h-8 px-2"
                  @click="$emit('editInvoice', invoice)"
                >
                  <Icon name="pencil" class="h-4 w-4 mr-1" />
                  Edit
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div v-else class="py-8 text-center text-muted-foreground">
          No canceled QS invoices found.
        </div>
      </div>

      <DialogFooter class="mt-4 flex flex-col gap-2 sm:flex-row">
        <Button
          type="button"
          @click="openProxy = false"
          variant="outline"
          class="w-full sm:w-auto"
        >
          Close
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Icon from "@/components/Icon.vue";
import {
  Button,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from "@/components/ui";

const props = defineProps<{
  open: boolean;
  canceledQSInvoices: any[];
  formatCurrency: (a: any) => string;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;

  // ✅ NEW: tell parent to open edit modal
  (e: "editInvoice", invoice: any): void;
}>();

const openProxy = computed({
  get: () => props.open,
  set: (v) => emit("update:open", v),
});
</script>
