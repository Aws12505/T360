<template>
  <div
    v-if="filteredOutstandingInvoices.length > 0"
    class="mx-auto mb-6 max-w-[95vw] overflow-x-auto rounded-lg border bg-card shadow-sm md:max-w-[64vw] lg:max-w-full"
  >
    <div class="flex items-center justify-between border-b p-4">
      <h3 class="text-lg font-semibold">Outstanding Invoices</h3>
      <div class="flex items-start justify-between">
        <div class="flex flex-col items-center space-y-1">
          <Badge variant="outline" class="text-sm">
            {{ filteredOutstandingInvoices.length }} invoices
          </Badge>
          <Badge variant="outline" class="text-sm">
            total: ${{ totalFilteredOutstanding.toFixed(2) }}
          </Badge>
        </div>

        <Button variant="ghost" size="sm" @click="$emit('toggleShow')" class="ml-4 mt-1">
          {{ showOutstandingInvoicesSection ? "Hide Invoices" : "Show Invoices" }}
          <Icon
            :name="showOutstandingInvoicesSection ? 'chevron-up' : 'chevron-down'"
            class="ml-2 h-4 w-4"
          />
        </Button>
      </div>
    </div>

    <div
      v-if="showOutstandingInvoicesSection"
      class="mb-6 rounded-lg border bg-card shadow-sm"
    >
      <div class="overflow-x-auto">
        <div class="max-h-60 overflow-y-auto sm:max-h-80 md:max-h-96">
          <Table class="min-w-full">
            <TableHeader>
              <TableRow>
                <TableHead>RO Number</TableHead>
                <TableHead>Vendor</TableHead>
                <TableHead>Week</TableHead>
                <TableHead class="text-right">Amount</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="invoice in filteredOutstandingInvoices"
                :key="invoice.ro_number"
              >
                <TableCell>{{ invoice.ro_number }}</TableCell>
                <TableCell>{{ invoice.vendor_name }}</TableCell>
                <TableCell>W{{ invoice.week_number }}/{{ invoice.year }}</TableCell>
                <TableCell class="text-right">{{
                  formatCurrency(invoice.invoice_amount)
                }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="mb-6 rounded-lg border bg-card shadow-sm">
    <div class="border-b p-4">
      <h3 class="text-lg font-semibold">Outstanding Invoices</h3>
    </div>
    <div class="p-4 text-center text-muted-foreground">
      No outstanding invoices match the current criteria
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon from "@/components/Icon.vue";
import {
  Badge,
  Button,
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from "@/components/ui";

defineProps<{
  filteredOutstandingInvoices: any[];
  totalFilteredOutstanding: number;
  showOutstandingInvoicesSection: boolean;
  formatCurrency: (a: any) => string;
}>();

defineEmits<{
  (e: "toggleShow"): void;
}>();
</script>
