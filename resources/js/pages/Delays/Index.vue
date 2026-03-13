<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="props.permissions">

    <Head title="On-Time" />

    <div
      class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6">
      <!-- Alerts -->
      <Alert v-if="successMessage" variant="success">
        <AlertTitle>Success</AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>
      <Alert v-if="errorMessage" variant="destructive">
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>{{ errorMessage }}</AlertDescription>
      </Alert>

      <!-- Actions -->
      <div class="mb-2 flex flex-col items-center justify-between px-2 sm:flex-row md:mb-4 lg:mb-6">
        <h1 class="text-lg font-bold text-gray-800 dark:text-gray-200 md:text-xl lg:text-2xl">
          On-Time Management
        </h1>
        <div class="flex flex-wrap gap-3 ml-3">
          <Button v-if="permissionNames.includes('delays.create')" class="px-2 py-0 md:px-4 md:py-2" @click="openForm()"
            variant="default">
            <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" />
            Add Delay
          </Button>

          <Button class="px-2 py-0 md:px-4 md:py-2"
            v-if="selectedDelays.length > 0 && permissionNames.includes('delays.delete')"
            @click="confirmDeleteSelected()" variant="destructive">
            <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" />
            Delete Selected ({{ selectedDelays.length }})
          </Button>

          <Button v-if="permissionNames.includes('delays.import')" variant="secondary"
            class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all" @click="showImportModal = true">
            <Icon name="upload" class="mr-1 h-4 w-4 md:mr-2" />
            Import CSV
          </Button>

          <Button class="px-2 py-0 md:px-4 md:py-2" @click.prevent="exportCSV" variant="outline"
            v-if="permissionNames.includes('delays.export')">
            <Icon name="download" class="mr-1 h-4 w-4 md:mr-2" />
            Download CSV
          </Button>
        </div>
      </div>

      <!-- Hidden Export Form -->
      <form ref="exportForm" :action="exportUrl" method="GET" class="hidden"></form>

      <!-- Date Filter Tabs -->
      <Card>
        <CardContent class="p-2 md:p-4 lg:p-6">
          <div class="flex flex-col items-center gap-2 md:items-start">
            <div class="flex flex-wrap gap-1 md:gap-2">
              <Button @click="selectDateFilter('yesterday')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary': activeTab === 'yesterday',
              }">
                Yesterday
              </Button>
              <Button @click="selectDateFilter('current-week')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary':
                  activeTab === 'current-week',
              }">
                WTD
              </Button>
              <Button @click="selectDateFilter('6w')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary': activeTab === '6w',
              }">
                T6W
              </Button>
              <Button @click="selectDateFilter('quarterly')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary': activeTab === 'quarterly',
              }">
                Quarterly
              </Button>
            </div>
            <div v-if="props.dateRange" class="text-sm text-muted-foreground">
              <span v-if="activeTab === 'yesterday' && props.dateRange.start">
                Showing data from {{ formatDate(props.dateRange.start) }}
              </span>
              <span v-else-if="props.dateRange.start && props.dateRange.end">
                Showing data from {{ formatDate(props.dateRange.start) }} to
                {{ formatDate(props.dateRange.end) }}
              </span>
              <span v-else>{{ props.dateRange.label }}</span>
              <span v-if="weekNumberText" class="ml-1">({{ weekNumberText }})</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Filters -->
      <Card class="mb-6">
        <CardHeader class="p-2 md:p-4 lg:p-6">
          <div class="flex items-center justify-between">
            <!-- Left: title + reason toggle + pills -->
            <div class="flex items-center gap-3 flex-wrap">
              <CardTitle class="text-lg md:text-xl lg:text-2xl">Filters</CardTitle>

              <!-- Delayed / On Time / All toggle -->
              <div class="flex items-center rounded-lg border border-input bg-muted p-0.5 gap-0.5">
                <button @click="setReasonFilter('with_reason')" :class="[
                  'rounded-md px-3 py-1 text-xs font-medium transition-all',
                  localFilters.delayReasonFilter === 'with_reason'
                    ? 'bg-background text-foreground shadow-sm'
                    : 'text-muted-foreground hover:text-foreground',
                ]">
                  Delayed
                </button>
                <button @click="setReasonFilter('without_reason')" :class="[
                  'rounded-md px-3 py-1 text-xs font-medium transition-all',
                  localFilters.delayReasonFilter === 'without_reason'
                    ? 'bg-background text-foreground shadow-sm'
                    : 'text-muted-foreground hover:text-foreground',
                ]">
                  On Time
                </button>
                <button @click="setReasonFilter('all')" :class="[
                  'rounded-md px-3 py-1 text-xs font-medium transition-all',
                  localFilters.delayReasonFilter === 'all'
                    ? 'bg-background text-foreground shadow-sm'
                    : 'text-muted-foreground hover:text-foreground',
                ]">
                  All
                </button>
              </div>

              <!-- Active filter pills (collapsed state) -->
              <div v-if="!showFilters && hasActiveFilters" class="flex flex-wrap gap-2">
                <span v-if="localFilters.search"
                  class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Search: {{ localFilters.search }}
                </span>
                <span v-if="localFilters.delayType"
                  class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Type:
                  {{ localFilters.delayType === "origin" ? "Origin" : "Destination" }}
                </span>
                <span v-if="localFilters.delayCategory"
                  class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Category: {{ formatDelayCategory(localFilters.delayCategory) }}
                </span>
                <span v-if="localFilters.disputed"
                  class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold capitalize">
                  Disputed: {{ localFilters.disputed }}
                </span>
                <span v-if="localFilters.driverControllable"
                  class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Driver:
                  {{
                    localFilters.driverControllable === "true"
                      ? "Yes"
                      : localFilters.driverControllable === "false"
                        ? "No"
                        : "N/A"
                  }}
                </span>
                <span v-if="localFilters.carrierControllable"
                  class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Carrier:
                  {{
                    localFilters.carrierControllable === "true"
                      ? "Yes"
                      : localFilters.carrierControllable === "false"
                        ? "No"
                        : "N/A"
                  }}
                </span>
              </div>
            </div>

            <!-- Right: show/hide toggle -->
            <Button variant="ghost" size="sm" @click="showFilters = !showFilters">
              {{ showFilters ? "Hide Filters" : "Show Filters" }}
              <Icon :name="showFilters ? 'chevron-up' : 'chevron-down'" class="ml-2 h-4 w-4" />
            </Button>
          </div>
        </CardHeader>

        <CardContent v-if="showFilters" class="p-2 md:p-4 lg:p-6">
          <div class="flex flex-col gap-3 md:gap-4">
            <!-- Row 1: Search + Delay Type + Category -->
            <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3 md:gap-4">
              <div>
                <Label for="search">Search</Label>
                <Input class="h-9 w-full lg:h-10" id="search" v-model="localFilters.search" type="text"
                  placeholder="Driver name, Load ID..." />
              </div>
              <div>
                <Label for="delayType">Delay Type</Label>
                <select id="delayType" v-model="localFilters.delayType" class="select-base">
                  <option value="">All Types</option>
                  <option value="origin">Origin</option>
                  <option value="destination">Destination</option>
                </select>
              </div>
              <div>
                <Label for="delayCategory">Delay Category</Label>
                <select id="delayCategory" v-model="localFilters.delayCategory" class="select-base">
                  <option value="">All Categories</option>
                  <option value="1_60">1–60 minutes late</option>
                  <option value="61_240">61–240 minutes late</option>
                  <option value="241_600">241–600 minutes late</option>
                  <option value="601_plus">601+ minutes late</option>
                </select>
              </div>
            </div>

            <!-- Row 2: Disputed + Driver Controllable + Carrier Controllable -->
            <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3 md:gap-4">
              <div>
                <Label for="disputed">Disputed</Label>
                <select id="disputed" v-model="localFilters.disputed" class="select-base">
                  <option value="">All</option>
                  <option value="none">None</option>
                  <option value="pending">Pending</option>
                  <option value="won">Won</option>
                  <option value="lost">Lost</option>
                </select>
              </div>
              <div>
                <Label for="driverControllable">Driver Controllable</Label>
                <select id="driverControllable" v-model="localFilters.driverControllable" class="select-base">
                  <option value="">All</option>
                  <option value="true">Yes</option>
                  <option value="false">No</option>
                  <option value="NA">N/A</option>
                </select>
              </div>
              <div>
                <Label for="carrierControllable">Carrier Controllable</Label>
                <select id="carrierControllable" v-model="localFilters.carrierControllable" class="select-base">
                  <option value="">All</option>
                  <option value="true">Yes</option>
                  <option value="false">No</option>
                  <option value="NA">N/A</option>
                </select>
              </div>
            </div>

            <div class="flex justify-end space-x-2">
              <Button @click="resetFilters" variant="ghost" size="sm">
                <Icon name="rotate_ccw" class="mr-2 h-4 w-4" />
                Reset Filters
              </Button>
              <Button @click="applyFilters" variant="default" size="sm">
                <Icon name="filter" class="mr-2 h-4 w-4" />
                Apply Filters
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- On-Time Dashboard -->
      <OnTimeDashboard v-if="!props.isSuperAdmin" :metricsData="props.delay_breakdown ?? {}"
        :driversData="props.delay_breakdown?.bottom_five_drivers?.total ?? []" :chartData="props.line_chart_data ?? []"
        :averageOntime="props.average_ontime ?? null" :currentDateFilter="props.dateRange?.label || ''"
        :currentFilters="localFilters" />

      <!-- Delays Table -->
      <Card class="mx-auto max-w-[95vw] overflow-x-auto md:max-w-[64vw] lg:max-w-full">
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table class="relative h-[500px] overflow-auto">
              <TableHeader>
                <TableRow class="sticky top-0 z-10 border-b bg-background hover:bg-background">
                  <TableHead class="w-[50px]" v-if="permissionNames.includes('delays.delete')">
                    <div class="flex items-center justify-center">
                      <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                    </div>
                  </TableHead>

                  <TableHead v-if="props.isSuperAdmin" class="whitespace-nowrap">Company</TableHead>

                  <TableHead v-for="col in ALL_COLUMNS" :key="col.key" class="cursor-pointer whitespace-nowrap"
                    @click="sortBy(col.key)">
                    <div class="flex items-center gap-1">
                      {{ col.label }}
                      <span v-if="sortColumn === col.key">
                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2">
                          <path d="M8 15l4-4 4 4" />
                        </svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M16 9l-4 4-4-4" />
                        </svg>
                      </span>
                      <span v-else class="opacity-40">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M8 10l4-4 4 4" />
                          <path d="M16 14l-4 4-4-4" />
                        </svg>
                      </span>
                    </div>
                  </TableHead>

                  <TableHead v-if="
                    permissionNames.includes('delays.update') ||
                    permissionNames.includes('delays.delete')
                  ">
                    Actions
                  </TableHead>
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-if="sortedDelays.length === 0">
                  <TableCell :colspan="props.isSuperAdmin ? ALL_COLUMNS.length + 3 : ALL_COLUMNS.length + 2
                    " class="py-8 text-center text-primary font-medium">
                    No delays found matching your criteria
                  </TableCell>
                </TableRow>

                <TableRow v-for="delay in sortedDelays" :key="delay.id" class="hover:bg-muted/50">
                  <TableCell class="text-center" v-if="permissionNames.includes('delays.delete')">
                    <input type="checkbox" :value="delay.id" v-model="selectedDelays"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                  </TableCell>

                  <TableCell v-if="props.isSuperAdmin" class="whitespace-nowrap">
                    {{ delay.tenant?.name || "—" }}
                  </TableCell>

                  <TableCell v-for="col in ALL_COLUMNS" :key="col.key" class="whitespace-nowrap text-sm">
                    <component :is="renderCell(col, delay)" />
                  </TableCell>

                  <TableCell v-if="
                    permissionNames.includes('delays.update') ||
                    permissionNames.includes('delays.delete')
                  ">
                    <div class="flex space-x-2">
                      <Button size="sm" @click="openForm(delay)" variant="warning"
                        v-if="permissionNames.includes('delays.update')">
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>
                      <Button size="sm" variant="destructive" @click="confirmDelete(delay.id)"
                        v-if="permissionNames.includes('delays.delete')">
                        <Icon name="trash" class="mr-1 h-4 w-4" />
                        Delete
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Pagination -->
          <div class="border-t bg-muted/20 px-4 py-3" v-if="props.delays.links">
            <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
              <div class="flex items-center gap-4 text-sm text-muted-foreground">
                <span>Showing {{ props.delays.data.length }} of
                  {{ props.delays.total }} entries</span>
                <div class="flex items-center gap-2">
                  <span class="text-sm">Show:</span>
                  <select v-model="localPerPage" @change="changePerPage"
                    class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                    <option v-for="size in [10, 25, 50, 100]" :key="size" :value="size">
                      {{ size }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="flex flex-wrap">
                <Button v-for="link in props.delays.links" :key="link.label" @click="visitPage(link.url)"
                  :disabled="!link.url" variant="ghost" size="sm" class="mx-1"
                  :class="{ 'border-primary bg-primary/10 text-primary': link.active }">
                  <span v-html="link.label"></span>
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- ── Delay Form Modal ── -->
      <Dialog v-model:open="formModal">
        <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-4xl">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">
              {{ selectedDelay ? "Edit" : "Add" }} Delay
            </DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Fill in the details to {{ selectedDelay ? "update" : "create" }} a delay
              record.
            </DialogDescription>
          </DialogHeader>
          <DelayForm :delay="selectedDelay" :tenants="props.tenants" :is-super-admin="props.isSuperAdmin"
            :tenant-slug="props.tenantSlug" @close="formModal = false" @success="onFormSuccess"
            class="max-h-[75vh] overflow-y-auto p-4 sm:p-6" />
        </DialogContent>
      </Dialog>

      <!-- ── Single Delete Confirmation ── -->
      <Dialog v-model:open="showDeleteModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">Confirm Deletion</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Are you sure you want to delete this delay record? This action cannot be
              undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="px-4 sm:px-6">
            <Button type="button" @click="showDeleteModal = false" variant="outline">Cancel</Button>
            <Button type="button" @click="deleteDelay(delayToDelete)" variant="destructive">Delete</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- ── Bulk Delete Confirmation ── -->
      <Dialog v-model:open="showDeleteSelectedModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">Confirm Bulk Deletion</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Are you sure you want to delete {{ selectedDelays.length }} delay records?
              This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="px-4 sm:px-6">
            <Button type="button" @click="showDeleteSelectedModal = false" variant="outline">Cancel</Button>
            <Button type="button" @click="deleteSelectedDelays()" variant="destructive">Delete Selected</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- ── Import Modal (extracted component) ── -->
      <ImportDelayModal v-model:open="showImportModal" :is-super-admin="props.isSuperAdmin"
        :tenant-slug="props.tenantSlug" :tenants="props.tenants" @success="onImportSuccess" @error="onImportError" />
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, defineComponent, h, onMounted, onUnmounted, ref, watch } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import DelayForm from "@/components/DelayForm.vue";
import ImportDelayModal from "@/components/onTime/ImportDelayModal.vue";
import OnTimeDashboard from "@/components/onTime/OnTimeDashboard.vue";
import Icon from "@/components/Icon.vue";

import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps({
  delays: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
  tenantSlug: { type: String, default: null },
  isSuperAdmin: { type: Boolean, default: false },
  tenants: { type: Array, default: () => [] },
  dateRange: { type: Object, default: null },
  dateFilter: { type: String, default: "full" },
  weekNumber: { type: Number, default: null },
  startWeekNumber: { type: Number, default: null },
  endWeekNumber: { type: Number, default: null },
  year: { type: Number, default: null },
  delay_breakdown: { type: Object, default: null },
  line_chart_data: { type: Array, default: () => [] },
  average_ontime: { type: Number, default: null },
  filters: { type: Object, default: () => ({}) },
  permissions: { type: Array, default: () => [] },
});

// ─── Permissions ──────────────────────────────────────────────────────────────

const permissionNames = computed(() =>
  (props.permissions ?? []).map((p) => (typeof p === "string" ? p : p.name))
);

// ─── Flash messages ───────────────────────────────────────────────────────────

const page = usePage();
const successMessage = ref("");
const errorMessage = ref("");

watch(
  () => page.props?.flash,
  (flash) => {
    if (!flash) return;
    if (flash.success) {
      successMessage.value = flash.success;
      setTimeout(() => (successMessage.value = ""), 5000);
    }
    if (flash.error) {
      errorMessage.value = flash.error;
      setTimeout(() => (errorMessage.value = ""), 5000);
    }
  },
  { immediate: true, deep: true }
);

function onFormSuccess() {
  successMessage.value = "Delay saved successfully.";
}
function onImportSuccess(msg) {
  successMessage.value = msg || "Import completed.";
  showImportModal.value = false;
}
function onImportError(msg) {
  errorMessage.value = msg || "Import failed.";
}

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs = [
  {
    title: props.tenantSlug ? "Dashboard" : "Admin Dashboard",
    href: props.tenantSlug
      ? route("dashboard", { tenantSlug: props.tenantSlug })
      : route("admin.dashboard"),
  },
  {
    title: "On Time",
    href: props.tenantSlug
      ? route("ontime.index", { tenantSlug: props.tenantSlug })
      : route("ontime.index.admin"),
  },
];

// ─── Column definitions with getValue + render (mirrors rejections pattern) ───

const ALL_COLUMNS = [
  {
    key: "date",
    label: "Date",
    getValue: (d) => d.date ?? "",
    render: (d) => h("span", {}, formatDate(d.date)),
  },
  {
    key: "delay_type",
    label: "Type",
    getValue: (d) => d.delay_type ?? "",
    render: (d) => {
      const hasReason = !!d.delay_reason;
      const label =
        d.delay_type === "origin"
          ? hasReason
            ? "Origin"
            : "Origin"
          : d.delay_type === "destination"
            ? hasReason
              ? "Destination"
              : "Destination"
            : d.delay_type ?? "—";
      return h(
        "span",
        {
          class: hasReason ? "text-destructive font-medium" : "text-muted-foreground",
        },
        label
      );
    },
  },
  {
    key: "load_id",
    label: "Load ID",
    getValue: (d) => d.load_id ?? "",
    render: (d) => h("span", {}, d.load_id || "—"),
  },
  {
    key: "driver_name",
    label: "Driver",
    getValue: (d) => d.driver_name ?? "",
    render: (d) => h("span", {}, formatDriverName(d.driver_name) || "—"),
  },
  {
    key: "delay_duration",
    label: "Duration",
    getValue: (d) => d.delay_duration ?? 0,
    render: (d) => h("span", {}, formatDuration(d.delay_duration)),
  },
  {
    key: "delay_category",
    label: "Category",
    getValue: (d) => d.delay_category ?? "",
    render: (d) => h("span", {}, formatDelayCategory(d.delay_category) || "—"),
  },
  {
    key: "penalty",
    label: "Penalty",
    getValue: (d) => {
      const isWon = d.disputed === "won";
      const isCarrierCtrl = isControllable(d.carrier_controllable);
      if (isWon) return 0;
      if (!isCarrierCtrl) return 0;
      return d.penalty != null ? Number(d.penalty) : null;
    },
    render: (d) => {
      const originalPenalty = d.penalty != null ? Number(d.penalty).toFixed(2) : null;
      // const isWon = d.disputed === "won";
      // const isDriverCtrl = isControllable(d.driver_controllable);
      // const isCarrierCtrl = isControllable(d.carrier_controllable);

      // No penalty and not won → dash
      if (!originalPenalty) {
        return h("span", { class: "text-muted-foreground" }, "—");
      }

      // // WON + driver controllable → show 0.00 with original as driver note
      // if (isWon && isDriverCtrl) {
      //   return h("div", { class: "flex flex-col leading-tight" }, [
      //     h(
      //       "span",
      //       {
      //         class: "font-mono text-xs font-semibold text-green-600 dark:text-green-400",
      //       },
      //       "0.00"
      //     ),
      //     h(
      //       "span",
      //       { class: "self-end text-[10px] opacity-60 font-mono whitespace-nowrap" },
      //       `Driver: ${originalPenalty}`
      //     ),
      //   ]);
      // }

      // // WON (not driver controllable) → just 0.00
      // if (isWon) {
      //   return h(
      //     "span",
      //     { class: "font-mono text-xs font-semibold text-green-600 dark:text-green-400" },
      //     "0.00"
      //   );
      // }

      // // Carrier NOT controllable + driver IS controllable → 0.00 with driver note
      // if (!isCarrierCtrl && isDriverCtrl && originalPenalty) {
      //   return h("div", { class: "flex flex-col leading-tight" }, [
      //     h(
      //       "span",
      //       {
      //         class: "font-mono text-xs font-semibold text-green-600 dark:text-green-400",
      //       },
      //       "0.00"
      //     ),
      //     h(
      //       "span",
      //       { class: "self-end text-[10px] opacity-60 font-mono whitespace-nowrap" },
      //       `Driver: ${originalPenalty}`
      //     ),
      //   ]);
      // }

      // // Carrier NOT controllable (both false) → just 0.00
      // if (!isCarrierCtrl) {
      //   return h(
      //     "span",
      //     { class: "font-mono text-xs font-semibold text-green-600 dark:text-green-400" },
      //     "0.00"
      //   );
      // }

      // Normal case
      return h("span", { class: "font-mono text-xs" }, originalPenalty);
    },
  },
  {
    key: "delay_reason",
    label: "Reason",
    getValue: (d) => d.delay_reason ?? "",
    render: (d) =>
      h("span", { class: "max-w-[200px] truncate block" }, formatDelayReason(d.delay_reason) || "—"),
  },
  {
    key: "disputed",
    label: "Disputed",
    getValue: (d) => d.disputed ?? "none",
    render: (d) =>
      h(
        "span",
        {
          class: `inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold capitalize ${disputedBadgeClass(
            d.disputed
          )}`,
        },
        formatDisputed(d.disputed)
      ),
  },
  {
    key: "driver_controllable",
    label: "Driver Controllable",
    getValue: (d) => d.driver_controllable,
    render: (d) =>
      h(
        "span",
        {},
        d.driver_controllable === null
          ? "N/A"
          : isControllable(d.driver_controllable)
            ? "Yes"
            : "No"
      ),
  },
  {
    key: "carrier_controllable",
    label: "Carrier Controllable",
    getValue: (d) => d.carrier_controllable,
    render: (d) =>
      h(
        "span",
        {},
        d.carrier_controllable === null
          ? "N/A"
          : isControllable(d.carrier_controllable)
            ? "Yes"
            : "No"
      ),
  },
];

// ─── Cell renderer ────────────────────────────────────────────────────────────

function renderCell(col, delay) {
  return defineComponent({
    render: () => col.render(delay),
  });
}

// ─── Sorting ──────────────────────────────────────────────────────────────────

const sortColumn = ref("date");
const sortDirection = ref("desc");

const sortedDelays = computed(() => {
  const rows = [...(props.delays.data ?? [])];
  return rows.sort((a, b) => {
    const colDef = ALL_COLUMNS.find((c) => c.key === sortColumn.value);
    const valA = colDef ? colDef.getValue(a) ?? "" : "";
    const valB = colDef ? colDef.getValue(b) ?? "" : "";
    if (valA === "—" || valA === "") return 1;
    if (valB === "—" || valB === "") return -1;
    const cmp =
      String(valA).toLowerCase() < String(valB).toLowerCase()
        ? -1
        : String(valA).toLowerCase() > String(valB).toLowerCase()
          ? 1
          : 0;
    return sortDirection.value === "asc" ? cmp : -cmp;
  });
});

function sortBy(key) {
  if (sortColumn.value === key) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortColumn.value = key;
    sortDirection.value = "asc";
  }
}

// ─── Selection ────────────────────────────────────────────────────────────────

const selectedDelays = ref([]);

const isAllSelected = computed(
  () =>
    props.delays.data.length > 0 &&
    props.delays.data.every((d) => selectedDelays.value.includes(d.id))
);

function toggleSelectAll(e) {
  selectedDelays.value = e.target.checked ? props.delays.data.map((d) => d.id) : [];
}

// ─── Modals ───────────────────────────────────────────────────────────────────

const formModal = ref(false);
const selectedDelay = ref(null);
const showDeleteModal = ref(false);
const delayToDelete = ref(null);
const showDeleteSelectedModal = ref(false);
const showImportModal = ref(false);

function openForm(delay = null) {
  selectedDelay.value = delay ?? null;
  formModal.value = true;
}

function confirmDelete(id) {
  delayToDelete.value = id;
  showDeleteModal.value = true;
}

function confirmDeleteSelected() {
  showDeleteSelectedModal.value = true;
}

// ─── Delete ───────────────────────────────────────────────────────────────────

function deleteDelay(id) {
  const routeName = props.isSuperAdmin ? "ontime.destroy.admin" : "ontime.destroy";
  const routeParams = props.isSuperAdmin ? { delay: id } : { tenantSlug: props.tenantSlug, delay: id };
  router.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
    },
  });
}

function deleteSelectedDelays() {
  const routeName = props.isSuperAdmin
    ? "ontime.destroyBulk.admin"
    : "ontime.destroyBulk";
  const routeParams = props.isSuperAdmin ? {} : { tenantSlug: props.tenantSlug };
  router.delete(route(routeName, routeParams), {
    data: { ids: selectedDelays.value },
    preserveScroll: true,
    onSuccess: () => {
      showDeleteSelectedModal.value = false;
      selectedDelays.value = [];
    },
  });
}

// ─── Filters ──────────────────────────────────────────────────────────────────

const showFilters = ref(false);
const localFilters = ref({
  search: props.filters?.search ?? "",
  delayType: props.filters?.delayType ?? "",
  delayCategory: props.filters?.delayCategory ?? "",
  disputed: props.filters?.disputed ?? "",
  driverControllable: props.filters?.driverControllable ?? "",
  carrierControllable: props.filters?.carrierControllable ?? "",
  delayReasonFilter: props.filters?.delayReasonFilter ?? "with_reason",
});

const hasActiveFilters = computed(
  () =>
    !!localFilters.value.search ||
    !!localFilters.value.delayType ||
    !!localFilters.value.delayCategory ||
    !!localFilters.value.disputed ||
    !!localFilters.value.driverControllable ||
    !!localFilters.value.carrierControllable ||
    localFilters.value.delayReasonFilter !== "with_reason"
);

function setReasonFilter(value) {
  localFilters.value.delayReasonFilter = value;
  getIndexRoute();
}

function applyFilters() {
  getIndexRoute();
}

function resetFilters() {
  localFilters.value = {
    search: "",
    delayType: "",
    delayCategory: "",
    disputed: "",
    driverControllable: "",
    carrierControllable: "",
    delayReasonFilter: "with_reason",
  };
  getIndexRoute();
}

function getIndexRoute() {
  const routeName = props.isSuperAdmin ? "ontime.index.admin" : "ontime.index";
  const routeParams = props.isSuperAdmin ? {} : { tenantSlug: props.tenantSlug };
  router.get(
    route(routeName, routeParams),
    { ...localFilters.value, dateFilter: activeTab.value, perPage: localPerPage.value },
    { preserveState: true, preserveScroll: true }
  );
}

// ─── Pagination ───────────────────────────────────────────────────────────────

const localPerPage = ref(Number(props.filters?.perPage ?? 10));

function changePerPage() {
  getIndexRoute();
}

function visitPage(url) {
  if (!url) return;
  router.get(url, {}, { preserveState: true, preserveScroll: true });
}

// ─── Date filter ──────────────────────────────────────────────────────────────

const activeTab = ref(props.dateFilter ?? "full");

function selectDateFilter(filter) {
  activeTab.value = filter;
  getIndexRoute();
}

const weekNumberText = computed(() => {
  const { year, weekNumber, startWeekNumber, endWeekNumber } = props;

  // Yesterday / current-week
  if (["yesterday", "current-week"].includes(activeTab.value) && weekNumber && year) {
    return `Week ${weekNumber}, ${year}`;
  }

  // 6w / quarterly
  if (
    ["6w", "quarterly"].includes(activeTab.value) &&
    startWeekNumber &&
    endWeekNumber &&
    year
  ) {
    // ✅ Cross-year case
    if (startWeekNumber > endWeekNumber) {
      return `Weeks ${startWeekNumber}–${endWeekNumber} (${year}–${year + 1})`;
    }

    // Normal same-year case
    return `Weeks ${startWeekNumber}–${endWeekNumber}, ${year}`;
  }

  return "";
});

// ─── Export ───────────────────────────────────────────────────────────────────

const exportForm = ref(null);

const exportUrl = computed(() =>
  props.isSuperAdmin
    ? route("ontime.export.admin")
    : route("ontime.export", { tenantSlug: props.tenantSlug })
);

function exportCSV() {
  exportForm.value?.submit();
}

// ─── Display helpers ──────────────────────────────────────────────────────────

function isControllable(val) {
  return val === true || val === 1 || val === "1" || val === "true";
}

function formatDate(val) {
  if (!val) return "—";
  const d = new Date(val);
  return isNaN(d.getTime())
    ? val
    : d.toLocaleDateString("en-US", { month: "numeric", day: "numeric", year: "numeric" });
}
function formatDelayReason(val) {
  if (!val) return "—";

  return val
    .replace(/_/g, " ")      // replace underscores with spaces
    .toLowerCase()
    .trim()
    .replace(/(^\p{L})|(\s+\p{L})/gu, (m) => m.toUpperCase()); // capitalize words
}
function formatDriverName(val) {
  if (!val) return "—";

  return val
    .toLowerCase()
    .trim()
    // capitalize first letter of each word (unicode letters)
    .replace(/(^\p{L})|(\s+\p{L})/gu, (m) => m.toUpperCase());
}

function formatDelayCategory(cat) {
  const map = {
    "1_60": "1–60",
    "61_240": "61–240",
    "241_600": "241–600",
    "601_plus": "601+",
  };
  return map[cat] ?? "";
}

function formatDuration(totalMinutes) {
  if (!totalMinutes) return "—";
  const h = Math.floor(totalMinutes / 60);
  const m = totalMinutes % 60;
  if (h === 0) return `${m}m`;
  if (m === 0) return `${h}h`;
  return `${h}h ${m}m`;
}

function formatDisputed(val) {
  const map = { none: "None", pending: "Pending", won: "Won", lost: "Lost" };
  return map[val] ?? "None";
}

function disputedBadgeClass(val) {
  return (
    {
      none: "bg-muted text-muted-foreground",
      pending: "bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400",
      won: "bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400",
      lost: "bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400",
    }[val] ?? "bg-muted text-muted-foreground"
  );
}

// ─── Global drag prevention ───────────────────────────────────────────────────

function preventDefault(e) {
  e.preventDefault();
}
onMounted(() => {
  window.addEventListener("dragover", preventDefault);
  window.addEventListener("drop", preventDefault);
});
onUnmounted(() => {
  window.removeEventListener("dragover", preventDefault);
  window.removeEventListener("drop", preventDefault);
});
</script>

<style scoped>
.select-base {
  @apply flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background appearance-none focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50;
}
</style>
