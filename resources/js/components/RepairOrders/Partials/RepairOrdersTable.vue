<template>
  <Card
    class="mx-auto max-w-[95vw] md:max-w-[64vw] lg:max-w-full overflow-x-auto shadow-sm border"
  >
    <CardContent class="p-0">
      <div class="overflow-x-auto">
        <Table class="relative h-[500px] overflow-auto">
          <TableHeader>
            <TableRow
              class="sticky top-0 z-10 border-b bg-background hover:bg-background"
            >
              <TableHead
                class="w-12"
                v-if="permissionNames.includes('repair-orders.delete')"
              >
                <div class="flex items-center justify-center">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="$emit('toggleAll', $event)"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                  />
                </div>
              </TableHead>

              <TableHead
                @click="$emit('sort', 'ro_number')"
                class="cursor-pointer font-semibold"
              >
                <div class="flex items-center space-x-1">
                  RO#
                  <SortIndicator column="ro_number" :sortState="sortState" />
                </div>
              </TableHead>

              <TableHead v-if="isAdmin" class="font-semibold">Company</TableHead>

              <TableHead
                @click="$emit('sort', 'ro_open_date')"
                class="cursor-pointer font-semibold"
              >
                <div class="flex items-center space-x-1">
                  Open Date
                  <SortIndicator column="ro_open_date" :sortState="sortState" />
                </div>
              </TableHead>

              <TableHead
                @click="$emit('sort', 'ro_close_date')"
                class="cursor-pointer font-semibold"
              >
                <div class="flex items-center space-x-1">
                  Close Date
                  <SortIndicator column="ro_close_date" :sortState="sortState" />
                </div>
              </TableHead>

              <TableHead class="font-semibold">Truck</TableHead>
              <TableHead class="font-semibold">Vendor</TableHead>
              <TableHead class="font-semibold">Areas of Concern</TableHead>
              <TableHead class="font-semibold">WO#</TableHead>
              <TableHead class="font-semibold">WO Status</TableHead>
              <TableHead class="font-semibold">Invoice</TableHead>
              <TableHead class="font-semibold">Amount</TableHead>
              <TableHead class="font-semibold">Invoice Received</TableHead>
              <TableHead class="font-semibold">On QS</TableHead>
              <TableHead class="font-semibold">QS Invoice Date</TableHead>
              <TableHead class="font-semibold">Disputed</TableHead>

              <TableHead
                class="font-semibold"
                v-if="
                  permissionNames.includes('repair-orders.delete') ||
                  permissionNames.includes('repair-orders.update')
                "
              >
                Actions
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-if="!repairOrders.data.length">
              <TableCell :colspan="isAdmin ? 16 : 15" class="py-8 text-center">
                <div
                  class="flex flex-col items-center justify-center rounded-lg border bg-muted/20 py-16"
                >
                  <Icon name="database-x" class="h-16 w-16 mx-auto mb-4 opacity-70" />
                  <h2 class="text-lg font-medium">No repair orders found.</h2>
                  <p class="text-muted-foreground mt-2">
                    There is no data to display at this time.
                  </p>
                </div>
              </TableCell>
            </TableRow>

            <TableRow
              v-for="o in repairOrders.data"
              :key="o.id"
              class="hover:bg-muted/50 transition-colors"
            >
              <TableCell v-if="permissionNames.includes('repair-orders.delete')">
                <input
                  type="checkbox"
                  :checked="selectedIds.includes(o.id)"
                  @change="toggleOne(o.id, $event)"
                  class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-1 focus:ring-primary"
                />
              </TableCell>

              <TableCell class="whitespace-nowrap font-medium">
                {{ o.ro_number }}
              </TableCell>

              <TableCell v-if="isAdmin" class="whitespace-nowrap">
                {{ o.tenant?.name || "—" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ formatDate(o.ro_open_date) }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.ro_close_date ? formatDate(o.ro_close_date) : "N/A" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.truck?.truckid || "—" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.vendor?.vendor_name || "—" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                <span v-if="o.areas_of_concern?.length">
                  <span v-for="(area, idx) in o.areas_of_concern" :key="area.id">
                    {{ area.concern
                    }}<span v-if="idx < o.areas_of_concern.length - 1">, </span>
                  </span>
                </span>
                <span v-else>—</span>
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.wo_number || "—" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.wo_status?.name || "—" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.invoice || "—" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ formatCurrency(o.invoice_amount) }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.invoice_received ? "Yes" : "No" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.on_qs ? o.on_qs.charAt(0).toUpperCase() + o.on_qs.slice(1) : "No" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.qs_invoice_date ? formatDate(o.qs_invoice_date) : "N/A" }}
              </TableCell>

              <TableCell class="whitespace-nowrap">
                {{ o.disputed ? "Yes" : "No" }}
              </TableCell>

              <TableCell
                v-if="
                  permissionNames.includes('repair-orders.update') ||
                  permissionNames.includes('repair-orders.delete')
                "
              >
                <div class="flex space-x-2">
                  <Button
                    size="sm"
                    variant="warning"
                    @click="$emit('openEdit', o)"
                    class="h-8 px-2"
                    v-if="permissionNames.includes('repair-orders.update')"
                  >
                    <Icon name="pencil" class="h-4 w-4" />
                  </Button>

                  <Button
                    size="sm"
                    variant="destructive"
                    @click="$emit('deleteOne', o.id)"
                    class="h-8 px-2"
                    v-if="permissionNames.includes('repair-orders.delete')"
                  >
                    <Icon name="trash" class="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </CardContent>

    <!-- Pagination -->
    <div
      class="border-t bg-muted/20 px-4 py-3 flex flex-col sm:flex-row justify-between items-center gap-2"
    >
      <div class="flex items-center gap-4 text-sm text-muted-foreground">
        <span class="flex items-center gap-1">
          <Icon name="list" class="h-4 w-4" />
          Showing {{ repairOrders.data.length }} of {{ repairOrders.total }} entries
        </span>

        <span class="flex items-center gap-1">
          <Icon name="layout-grid" class="h-4 w-4" />
          Per page:
        </span>

        <div class="relative">
          <select
            v-model.number="localPerPageProxy"
            @change="$emit('changePerPage')"
            class="h-8 appearance-none rounded-md border bg-background px-2 py-1 text-sm focus:ring-2 focus:ring-ring"
          >
            <option v-for="n in [10, 25, 50, 100]" :key="n" :value="n">
              {{ n }}
            </option>
          </select>

          <div
            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
          >
            <svg
              class="h-4 w-4 opacity-50"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
        </div>
      </div>

      <div class="flex space-x-1">
        <Button
          size="sm"
          variant="ghost"
          @click="$emit('go', repairOrders.prev_page_url)"
          :disabled="!repairOrders.prev_page_url"
          class="flex items-center gap-1"
        >
          <Icon name="chevron-left" class="h-4 w-4" /> Prev
        </Button>

        <Button
          v-for="link in repairOrders.links.slice(1, -1)"
          :key="link.label"
          size="sm"
          variant="ghost"
          @click="$emit('go', link.url)"
          :disabled="!link.url"
          :class="{
            'border-primary bg-primary/10 text-primary font-medium': link.active,
          }"
        >
          <span v-html="link.label"></span>
        </Button>

        <Button
          size="sm"
          variant="ghost"
          @click="$emit('go', repairOrders.next_page_url)"
          :disabled="!repairOrders.next_page_url"
          class="flex items-center gap-1"
        >
          Next <Icon name="chevron-right" class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </Card>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Icon from "@/components/Icon.vue";
import SortIndicator from "@/components/SortIndicator.vue";
import {
  Button,
  Card,
  CardContent,
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from "@/components/ui";

const props = defineProps<{
  repairOrders: any;
  permissionNames: string[];
  isAdmin: boolean;
  selectedIds: number[];
  allSelected: boolean;
  sortState: any;
  localPerPage: number;
  tenantSlug: string | null;
  filter: any;
  formatDate: (d: string) => string;
  formatCurrency: (a: any) => string;
}>();

const emit = defineEmits<{
  (e: "toggleAll", ev: Event): void;
  (e: "sort", col: string): void;
  (e: "openEdit", row: any): void;
  (e: "deleteOne", id: number): void;
  (e: "changePerPage"): void;
  (e: "go", url?: string): void;

  // ✅ v-model support
  (e: "update:selectedIds", v: number[]): void;
  (e: "update:localPerPage", v: number): void;
}>();

/**
 * ✅ Row checkbox handler (no prop mutation)
 */
function toggleOne(id: number, ev: Event) {
  const checked = (ev.target as HTMLInputElement).checked;

  const next = checked
    ? Array.from(new Set([...props.selectedIds, id]))
    : props.selectedIds.filter((x) => x !== id);

  emit("update:selectedIds", next);
}

/**
 * ✅ Per page v-model proxy (no prop mutation)
 */
const localPerPageProxy = computed({
  get: () => props.localPerPage,
  set: (v: number) => emit("update:localPerPage", v),
});
</script>
