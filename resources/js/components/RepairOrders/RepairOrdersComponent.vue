<template>
  <div
    class="w-full pt-6 md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto p-1 space-y-6"
  >
    <!-- Alerts -->
    <AlertsSection :successMessage="successMessage" :errorMessage="errorMessage" />

    <!-- Actions -->
    <ActionsBar
      :permissionNames="permissionNames"
      :selectedIds="selectedIds"
      :isAdmin="isAdmin"
      @openCreateModal="openCreateModal"
      @confirmBulkDelete="confirmBulkDelete"
      @openAreasModal="openAreasModal"
      @openVendorsModal="openVendorsModal"
      @openStatusModal="openStatusModal"
      @openImportModal="openImportModal"
      @exportCsv="exportCsv"
    />

    <!-- Canceled QS Invoices Alert -->
    <CanceledQSInvoicesAlert
      :hasCanceledQSInvoices="hasCanceledQSInvoices"
      :SuperAdmin="props.SuperAdmin"
      :permissionNames="permissionNames"
      :canceledQSInvoices="props.canceledQSInvoices"
      @openDialog="showCanceledQSInvoicesDialog = true"
    />

    <!-- Date Filter Tabs -->
    <DateFilterTabs
      :dateOptions="dateOptions"
      :filter="filter"
      :dateRange="props.dateRange"
      :dateFilter="props.dateFilter"
      :weekNumberText="weekNumberText"
      :formatDate="formatDate"
      @selectDate="selectDate"
    />

    <!-- Top Panels (Quarterly / 6w) -->
    <TopPanels
      v-if="(dateFilter === 'quarterly' || dateFilter === '6w') && !props.SuperAdmin"
      :topAreasOfConcern="topAreasOfConcern"
      :topWorkOrdersByTruck="topWorkOrdersByTruck"
    />

    <!-- Outstanding Invoices Filter -->
    <OutstandingInvoicesFilter
      v-if="!props.SuperAdmin"
      v-model:minInvoiceAmount="minInvoiceAmount"
      v-model:outstandingDate="outstandingDate"
    />

    <!-- Outstanding Invoices Section -->
    <OutstandingInvoicesSection
      v-if="!props.SuperAdmin"
      :filteredOutstandingInvoices="filteredOutstandingInvoices"
      :totalFilteredOutstanding="totalFilteredOutstanding"
      :showOutstandingInvoicesSection="showOutstandingInvoicesSection"
      :formatCurrency="formatCurrency"
      @toggleShow="showOutstandingInvoicesSection = !showOutstandingInvoicesSection"
    />

    <!-- Filters Card -->
    <FiltersCard
      :showFilters="showFilters"
      :activeFilterBadges="activeFilterBadges"
      :filter="filter"
      :vendors="props.vendors"
      :woStatuses="props.woStatuses"
      @toggleShowFilters="showFilters = !showFilters"
      @resetFilters="resetFilters"
      @applyFilters="applyFilters"
    />

    <!-- Table -->
    <template v-if="hasData">
      <RepairOrdersTable
        :repairOrders="props.repairOrders"
        :permissionNames="permissionNames"
        :isAdmin="isAdmin"
        v-model:selectedIds="selectedIds"
        :allSelected="allSelected"
        :sortState="sortState"
        v-model:localPerPage="localPerPage"
        :tenantSlug="props.tenantSlug"
        :filter="filter"
        :formatDate="formatDate"
        :formatCurrency="formatCurrency"
        @toggleAll="toggleAll"
        @sort="sort"
        @openEdit="openEdit"
        @deleteOne="deleteOne"
        @changePerPage="changePerPage"
        @go="go"
      />
    </template>

    <div
      v-else
      class="flex flex-col items-center justify-center rounded-lg border bg-muted/20 py-16"
    >
      <Icon name="database-x" class="h-16 w-16 mx-auto mb-4 opacity-70" />
      <h2 class="text-lg font-medium">There is No Data to give Information about.</h2>
    </div>

    <!-- Create/Edit Modal -->
    <CreateEditModal
      v-model:open="showModal"
      :isAdmin="isAdmin"
      :tenants="props.tenants"
      :trucks="props.trucks"
      :vendors="props.vendors"
      :woStatuses="props.woStatuses"
      :areasOfConcern="props.areasOfConcern"
      :form="form"
      :formAction="formAction"
      :areasMap="areasMap"
      :availableAreas="availableAreas"
      @submitForm="submitForm"
      @closeModal="closeModal"
      @addArea="addArea"
      @removeArea="removeArea"
    />

    <!-- Delete One -->
    <DeleteOneDialog
      v-model:open="showDeleteModal"
      @confirmDelete="confirmDelete"
      @cancel="showDeleteModal = false"
    />

    <!-- Bulk Delete -->
    <BulkDeleteDialog
      v-model:open="showBulkDeleteModal"
      :selectedCount="selectedIds.length"
      @deleteBulk="deleteBulk"
      @cancel="showBulkDeleteModal = false"
    />

    <!-- Areas Modal -->
    <AreasModal
      v-model:open="showAreasModal"
      :areasOfConcern="props.areasOfConcern"
      :areas="areasOfConcern"
      :areaForm="areaForm"
      @submitArea="submitArea"
      @deleteArea="deleteArea"
      @restoreArea="restoreArea"
      @close="showAreasModal = false"
    />

    <!-- Vendors Modal -->
    <VendorsModal
      v-model:open="showVendorsModal"
      :vendors="vendors"
      :vendorForm="vendorForm"
      @submitVendor="submitVendor"
      @deleteVendor="deleteVendor"
      @restoreVendor="restoreVendor"
      @forceDeleteVendor="forceDeleteVendor"
      @close="showVendorsModal = false"
    />

    <!-- Statuses Modal -->
    <StatusesModal
      v-model:open="showStatusModal"
      :woStatuses="props.woStatuses"
      :statusForm="statusForm"
      @submitStatus="submitStatus"
      @deleteStatus="deleteStatus"
      @restoreStatus="restoreStatus"
      @forceDeleteStatus="forceDeleteStatus"
      @close="showStatusModal = false"
    />

    <!-- Dialog for Canceled QS Invoices -->
    <CanceledQSInvoicesDialog
      v-model:open="showCanceledQSInvoicesDialog"
      :canceledQSInvoices="props.canceledQSInvoices"
      :formatCurrency="formatCurrency"
      @editInvoice="openEditFromCanceled"
    />
  </div>

  <!-- Import Modal -->
  <ImportModal
    v-model:open="showImportModal"
    v-model:importType="importType"
    v-model:importTenantId="importTenantId"
    :importValidationResults="importValidationResults"
    :isValidating="isValidating"
    :isImporting="isImporting"
    :isAdmin="isAdmin"
    :tenants="props.tenants"
    :templateUrl="templateUrl"
    :isDragging="isDragging"
    :hasWarnings="hasWarnings"
    @openImportModal="openImportModal"
    @closeImportModal="closeImportModal"
    @confirmImport="confirmImport"
    @downloadErrorReport="downloadErrorReport"
    @onImportInputChange="onImportInputChange"
    @onDragEnter="onDragEnter"
    @onDragOver="onDragOver"
    @onDragLeave="onDragLeave"
    @onDrop="onDrop"
    :formatCurrency="formatCurrency"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Icon from "@/components/Icon.vue";
import SortIndicator from "@/components/SortIndicator.vue";

// ✅ NEW imports (partials)
import AlertsSection from "./Partials/AlertsSection.vue";
import ActionsBar from "./Partials/ActionsBar.vue";
import CanceledQSInvoicesAlert from "./Partials/CanceledQSInvoicesAlert.vue";
import DateFilterTabs from "./Partials/DateFilterTabs.vue";
import TopPanels from "./Partials/TopPanels.vue";
import OutstandingInvoicesFilter from "./Partials/OutstandingInvoicesFilter.vue";
import OutstandingInvoicesSection from "./Partials/OutstandingInvoicesSection.vue";
import FiltersCard from "./Partials/FiltersCard.vue";
import RepairOrdersTable from "./Partials/RepairOrdersTable.vue";
import CreateEditModal from "./Partials/CreateEditModal.vue";
import DeleteOneDialog from "./Partials/DeleteOneDialog.vue";
import BulkDeleteDialog from "./Partials/BulkDeleteDialog.vue";
import AreasModal from "./Partials/AreasModal.vue";
import VendorsModal from "./Partials/VendorsModal.vue";
import StatusesModal from "./Partials/StatusesModal.vue";
import CanceledQSInvoicesDialog from "./Partials/CanceledQSInvoicesDialog.vue";
import ImportModal from "./Partials/ImportModal.vue";

const props = defineProps({
  repairOrders: { type: Object, default: () => ({ data: [], links: [] }) },
  tenantSlug: { type: String, default: null },
  SuperAdmin: { type: Boolean, default: false },
  tenants: { type: Array, default: () => [] },
  trucks: { type: Array, default: () => [] },
  vendors: { type: Array, default: () => [] },
  areasOfConcern: { type: Array, default: () => [] },
  dateRange: { type: Object, default: null },
  dateFilter: { type: String, default: "yesterday" },
  woStatuses: { type: Array, default: () => [] },
  weekNumber: { type: Number, default: null },
  startWeekNumber: { type: Number, default: null },
  endWeekNumber: { type: Number, default: null },
  year: { type: Number, default: null },
  canceledQSInvoices: { type: Array, default: () => [] },
  outstandingInvoices: { type: Array, default: () => [] },
  workOrdersByTruck: { type: Array, default: () => [] },
  workOrderByAreasOfConcern: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({
      search: "",
      vendor_id: "",
      status_id: "",
    }),
  },
  perPage: { type: Number, default: 10 },
  openedComponent: { type: String, default: "trucks" },
  permissions: { type: Array, default: () => [] },
  importValidation: { type: Object, default: null },
});

// State
const filter = ref({ ...props.filters, dateFilter: props.dateFilter });
const localPerPage = ref(props.perPage);
const selectedIds = ref<number[]>([]);
const showFilters = ref(false);
const successMessage = ref("");
const errorMessage = ref("");
const showUpload = ref(false);

const showModal = ref(false);
const showDeleteModal = ref(false);
const showBulkDeleteModal = ref(false);
const showAreasModal = ref(false);
const showVendorsModal = ref(false);
const showStatusModal = ref(false);
const showImportModal = ref(false);

const importValidationResults = ref<any>(null);
const isValidating = ref(false);
const isImporting = ref(false);

// Import type + tenant selection for QS
const importType = ref<"template" | "quicksight">("template");
const importTenantId = ref<string | number>("");

// ✅ NEW: drag state (same as your working template)
const isDragging = ref(false);
let dragDepth = 0;

// Prevent browser from opening dropped file outside dropzone
onMounted(() => {
  const prevent = (e: DragEvent) => e.preventDefault();
  window.addEventListener("dragover", prevent);
  window.addEventListener("drop", prevent);

  onUnmounted(() => {
    window.removeEventListener("dragover", prevent);
    window.removeEventListener("drop", prevent);
  });
});

const formAction = ref<"Create" | "Update">("Create");
const form = useForm({
  id: null,
  tenant_id: props.SuperAdmin ? "" : null,
  ro_number: "",
  ro_open_date: "",
  ro_close_date: "",
  truck_id: null,
  vendor_id: null,
  wo_number: "",
  wo_status_id: null,
  invoice: "",
  invoice_amount: "",
  invoice_received: false,
  on_qs: "no",
  qs_invoice_date: "",
  disputed: false,
  dispute_outcome: "",
  repairs_made: "",
  area_of_concerns: [],
});

const areaForm = useForm({ concern: "" });
const vendorForm = useForm({ vendor_name: "" });
const statusForm = useForm({ name: "" });

// Computed
const isAdmin = computed(() => props.SuperAdmin);
const hasData = computed(() => props.repairOrders.data.length > 0);

const allSelected = computed(
  () =>
    hasData.value &&
    props.repairOrders.data.every((o: any) => selectedIds.value.includes(o.id))
);

const dateOptions = [
  { label: "Yesterday", value: "yesterday" },
  { label: "WTD", value: "current-week" },
  { label: "T6W", value: "6w" },
  { label: "Quarterly", value: "quarterly" },
];

const templateUrl = "/storage/upload-data-temps/Repair Orders Template.csv";

// Helpers
const formatDate = (dateStr: string) => {
  if (!dateStr) return "";
  const parts = dateStr.split("-");
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
};

function formatCurrency(a: any) {
  return a
    ? new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" }).format(a)
    : "$0.00";
}

function selectDate(val: string) {
  filter.value.dateFilter = val;
  applyFilters();
}

const hasWarnings = computed(() => {
  const res: any = importValidationResults.value;
  if (!res) return false;
  return (res.invalid || []).some((r: any) => (r.warnings || []).length > 0);
});

function applyFilters() {
  const routeName = props.SuperAdmin
    ? "repair_orders.index.admin"
    : "repair_orders.index";

  const params = {
    ...(props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug }),
    ...filter.value,
    perPage: localPerPage.value,
    dateFilter: filter.value.dateFilter,
    openedComponent: "repairOrders",
  };

  router.visit(route(routeName, params), {
    only: [
      "repairOrders",
      "filters",
      "perPage",
      "dateFilter",
      "weekNumber",
      "startWeekNumber",
      "endWeekNumber",
      "year",
      "workOrdersByTruck",
      "workOrderByAreasOfConcern",
      "openedComponent",
      "dateRange",
    ],
  });
}

function resetFilters() {
  filter.value = { ...filter.value, search: "", vendor_id: "", status_id: "" };
  applyFilters();
}

const sortState = ref({ column: "ro_number", direction: "asc" });

function sort(col: string) {
  if (sortState.value.column === col)
    sortState.value.direction = sortState.value.direction === "asc" ? "desc" : "asc";
  else sortState.value = { column: col, direction: "asc" };

  applyFilters();
}

function changePerPage() {
  applyFilters();
}

function go(url?: string) {
  if (!url) return;

  const urlObj = new URL(url);
  const baseUrl = urlObj.origin + urlObj.pathname;

  router.get(
    baseUrl,
    {
      ...filter.value,
      perPage: localPerPage.value,
      dateFilter: filter.value.dateFilter,
      openedComponent: "repairOrders",
      page: urlObj.searchParams.get("page") || 1,
    },
    { preserveScroll: true }
  );
}

function toggleAll(e: Event) {
  const chk = (e.target as HTMLInputElement).checked;
  selectedIds.value = chk ? props.repairOrders.data.map((o: any) => o.id) : [];
}

const page = usePage();

// CSV Export
function exportCsv() {
  if (!hasData.value) {
    errorMessage.value = "No data";
    return;
  }
  window.location.href = props.tenantSlug
    ? route("repair_orders.export", { tenantSlug: props.tenantSlug })
    : route("repair_orders.export.admin");
}

// ✅ UPDATED: open import modal resets import state + drag state
function openImportModal() {
  showImportModal.value = true;
  importValidationResults.value = null;
  isValidating.value = false;
  isImporting.value = false;
  importType.value = "template";
  importTenantId.value = "";

  isDragging.value = false;
  dragDepth = 0;
}

// Create/Edit handlers
function openCreateModal() {
  form.reset();
  formAction.value = "Create";
  showModal.value = true;
}
function openEdit(o: any) {
  form.reset();

  form.id = o.id;
  form.tenant_id = o.tenant_id;
  form.ro_number = o.ro_number;
  form.ro_open_date = o.ro_open_date;
  form.ro_close_date = o.ro_close_date || "";
  form.truck_id = o.truck_id;
  form.vendor_id = o.vendor_id;
  form.wo_number = o.wo_number ?? "";
  form.wo_status_id = o.wo_status_id ?? null;
  form.invoice = o.invoice || "";
  form.invoice_amount = o.invoice_amount || "";
  form.invoice_received = Boolean(o.invoice_received);
  form.on_qs = o.on_qs || "no";
  form.qs_invoice_date = o.qs_invoice_date || "";
  form.disputed = Boolean(o.disputed);
  form.dispute_outcome = o.dispute_outcome || "";
  form.repairs_made = o.repairs_made || "";

  form.area_of_concerns = [];
  if (o.areas_of_concern && Array.isArray(o.areas_of_concern)) {
    form.area_of_concerns = o.areas_of_concern.map((area: any) => area.id);
  }

  formAction.value = "Update";
  showModal.value = true;
}

function closeModal() {
  form.reset();
  showModal.value = false;
}

function submitForm() {
  if (form.wo_status_id === ("" as any)) form.wo_status_id = null;
  if (form.wo_number === (null as any)) form.wo_number = "";

  const endpoint =
    formAction.value === "Update"
      ? props.tenantSlug
        ? route("repair_orders.update", [props.tenantSlug, form.id])
        : route("repair_orders.update.admin", form.id)
      : props.tenantSlug
      ? route("repair_orders.store", { tenantSlug: props.tenantSlug })
      : route("repair_orders.store.admin");

  form[formAction.value === "Update" ? "put" : "post"](endpoint, {
    onSuccess: () => {
      successMessage.value = `${formAction.value}d!`;
      showModal.value = false;
    },
  });
}

// Delete handlers
let deleteId: number | null = null;
function deleteOne(id: number) {
  deleteId = id;
  showDeleteModal.value = true;
}
function confirmDelete() {
  if (deleteId === null) return;
  const r = props.tenantSlug
    ? route("repair_orders.destroy", [props.tenantSlug, deleteId])
    : route("repair_orders.destroy.admin", deleteId);
  router.delete(r, {
    onSuccess: () => {
      successMessage.value = "Deleted";
      showDeleteModal.value = false;
    },
  });
}
function confirmBulkDelete() {
  showBulkDeleteModal.value = true;
}
function deleteBulk() {
  const r = props.tenantSlug
    ? route("repair_orders.destroyBulk", { tenantSlug: props.tenantSlug })
    : route("repair_orders.destroyBulk.admin");
  useForm({ ids: selectedIds.value }).delete(r, {
    onSuccess: () => {
      successMessage.value = `${selectedIds.value.length} deleted`;
      selectedIds.value = [];
      showBulkDeleteModal.value = false;
    },
  });
}

// Areas/Vendors/Statuses handlers
function openAreasModal() {
  areaForm.reset();
  showAreasModal.value = true;
}
function submitArea() {
  areaForm.post(route("area_of_concerns.store.admin"), {
    onSuccess: () => areaForm.reset(),
  });
}
function deleteArea(id: number) {
  router.delete(route("area_of_concerns.destroy.admin", id));
}
function restoreArea(id: number) {
  router.post(route("area_of_concerns.restore.admin", id));
}

function openVendorsModal() {
  vendorForm.reset();
  showVendorsModal.value = true;
}
function submitVendor() {
  vendorForm.post(route("vendors.store.admin"), { onSuccess: () => vendorForm.reset() });
}
function deleteVendor(id: number) {
  router.delete(route("vendors.destroy.admin", id));
}
function restoreVendor(id: number) {
  router.post(route("vendors.restore.admin", id));
}
function forceDeleteVendor(id: number) {
  router.delete(route("vendors.forceDelete.admin", id));
}

function openStatusModal() {
  statusForm.reset();
  showStatusModal.value = true;
}
function submitStatus() {
  statusForm.post(route("wo_statuses.store.admin"), {
    onSuccess: () => statusForm.reset(),
  });
}
function deleteStatus(id: number) {
  router.delete(route("wo_statuses.destroy.admin", id));
}
function restoreStatus(id: number) {
  router.post(route("wo_statuses.restore.admin", id));
}
function forceDeleteStatus(id: number) {
  router.delete(route("wo_statuses.forceDelete.admin", id));
}

// Other computed/utility
const hasCanceledQSInvoices = computed(() => props.canceledQSInvoices.length > 0);
const showCanceledQSInvoicesDialog = ref(false);

const areasMap = computed(() =>
  Object.fromEntries((props.areasOfConcern as any[]).map((a: any) => [a.id, a.concern]))
);
const availableAreas = computed(() =>
  (props.areasOfConcern as any[]).filter(
    (a: any) => !form.area_of_concerns.includes(a.id)
  )
);

function addArea(e: any) {
  const id = Number(e.target.value);
  if (id && !form.area_of_concerns.includes(id)) form.area_of_concerns.push(id);
  e.target.value = "";
}
function removeArea(id: number) {
  form.area_of_concerns = form.area_of_concerns.filter((x: number) => x !== id);
}

const weekNumberText = computed(() => {
  if (
    (props.dateFilter === "yesterday" || props.dateFilter === "current-week") &&
    props.weekNumber &&
    props.year
  ) {
    return `Week ${props.weekNumber}, ${props.year}`;
  }
  if (
    (props.dateFilter === "6w" || props.dateFilter === "quarterly") &&
    props.startWeekNumber &&
    props.endWeekNumber &&
    props.year
  ) {
    return `Weeks ${props.startWeekNumber}-${props.endWeekNumber}, ${props.year}`;
  }
  return "";
});

// Click outside
onMounted(() => {
  const h = (e: Event) => {
    if (showUpload.value && !(e.target as HTMLElement).closest(".relative"))
      showUpload.value = false;
  };
  document.addEventListener("click", h);
  onUnmounted(() => document.removeEventListener("click", h));
});

// Auto-clear success
watch(successMessage, (val) => {
  if (!val) return;
  setTimeout(() => (successMessage.value = ""), 5000);
});

// Top panels
const topAreasOfConcern = computed(() =>
  (props.workOrderByAreasOfConcern || []).slice(0, 5)
);
const topWorkOrdersByTruck = computed(() => (props.workOrdersByTruck || []).slice(0, 5));

const minInvoiceAmount = ref<number | null>(null);
const outstandingDate = ref<string | null>(null);
const showOutstandingInvoicesSection = ref(false);

const filteredOutstandingInvoices = computed(() => {
  return (props.outstandingInvoices as any[]).filter((inv: any) => {
    const meetsAmount =
      minInvoiceAmount.value != null
        ? Number(inv.invoice_amount) >= minInvoiceAmount.value
        : true;

    const meetsDate = outstandingDate.value
      ? new Date(inv.qs_invoice_date) > new Date(outstandingDate.value)
      : true;

    return meetsAmount && meetsDate;
  });
});

const totalFilteredOutstanding = computed(() => {
  return filteredOutstandingInvoices.value.reduce(
    (sum: number, inv: any) => sum + Number(inv.invoice_amount || 0),
    0
  );
});

const permissionNames = computed(() =>
  (props.permissions as any[]).map((p: any) => p.name)
);

const activeFilterBadges = computed(() => {
  const badges: string[] = [];
  if (filter.value.search) badges.push(`Search: "${filter.value.search}"`);

  const selectedVendor = (props.vendors as any[]).find(
    (v: any) => v.id == filter.value.vendor_id
  );
  if (selectedVendor) badges.push(`Vendor: ${selectedVendor.vendor_name}`);

  const selectedStatus = (props.woStatuses as any[]).find(
    (s: any) => s.id == filter.value.status_id
  );
  if (selectedStatus) badges.push(`Status: ${selectedStatus.name}`);

  return badges;
});

function closeImportModal() {
  showImportModal.value = false;
  importValidationResults.value = null;
  isValidating.value = false;
  isImporting.value = false;
  importType.value = "template";
  importTenantId.value = "";

  isDragging.value = false;
  dragDepth = 0;
}

/** input change -> shared handler */
function onImportInputChange(file: File) {
  handleImportFile(file);
}

/** Shared handler */
function handleImportFile(file: File) {
  if (!file) return;

  // Require tenant when SuperAdmin + QS
  if (isAdmin.value && importType.value === "quicksight" && !importTenantId.value) {
    errorMessage.value = "Please select a company before validating a QuickSight CSV.";
    setTimeout(() => (errorMessage.value = ""), 4000);
    return;
  }

  const isCsv =
    file.type === "text/csv" ||
    file.name.toLowerCase().endsWith(".csv") ||
    file.type === "";

  if (!isCsv) {
    errorMessage.value = "Please upload a valid CSV file.";
    setTimeout(() => (errorMessage.value = ""), 4000);
    return;
  }

  isValidating.value = true;

  const formData = new FormData();
  formData.append("file", file);
  formData.append("importType", importType.value);

  if (isAdmin.value && importType.value === "quicksight") {
    formData.append("tenant_id", String(importTenantId.value));
  }

  const endpoint = props.tenantSlug
    ? route("repair_orders.validateImport", { tenantSlug: props.tenantSlug })
    : route("repair_orders.validateImport.admin");

  router.post(endpoint, formData, {
    forceFormData: true,
    preserveScroll: true,
    only: ["flash"],
    onFinish: () => {
      isValidating.value = false;
    },
    onError: () => {
      isValidating.value = false;
      errorMessage.value = "Failed to validate CSV file";
    },
  });
}

/** Drag handlers */
function onDragEnter() {
  dragDepth += 1;
  isDragging.value = true;
}
function onDragOver() {
  isDragging.value = true;
}
function onDragLeave() {
  dragDepth -= 1;
  if (dragDepth <= 0) {
    dragDepth = 0;
    isDragging.value = false;
  }
}
function onDrop(file: File) {
  dragDepth = 0;
  isDragging.value = false;
  handleImportFile(file);
}

// Confirm Import
function confirmImport() {
  if (!importValidationResults.value || importValidationResults.value.summary.invalid > 0)
    return;

  if (isAdmin.value && importType.value === "quicksight" && !importTenantId.value) {
    errorMessage.value = "Please select a company before importing a QuickSight CSV.";
    return;
  }

  isImporting.value = true;

  const endpoint = props.tenantSlug
    ? route("repair_orders.confirmImport", { tenantSlug: props.tenantSlug })
    : route("repair_orders.confirmImport.admin");

  router.post(
    endpoint,
    {
      importType: importType.value,
      ...(isAdmin.value && importType.value === "quicksight"
        ? { tenant_id: importTenantId.value }
        : {}),
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        isImporting.value = false;
        successMessage.value = `Successfully imported ${importValidationResults.value.summary.valid} repair orders`;
        closeImportModal();
      },
      onError: () => {
        isImporting.value = false;
        errorMessage.value = "Failed to import repair orders";
      },
    }
  );
}

function downloadErrorReport() {
  const endpoint = props.tenantSlug
    ? route("repair_orders.downloadErrorReport", { tenantSlug: props.tenantSlug })
    : route("repair_orders.downloadErrorReport.admin");

  window.location.href = endpoint;
}

// Watch flash validation payload
watch(
  () => (page.props as any).flash?.importValidation,
  (payload: any) => {
    if (!payload) return;
    if (payload.results) {
      importValidationResults.value = payload.results;
      if (payload.header_error) {
        importValidationResults.value.header_error = payload.header_error;
      }
      showImportModal.value = true;
      return;
    }
    if (payload.message) errorMessage.value = payload.message;
  },
  { immediate: false }
);

async function openEditFromCanceled(invoice: any) {
  // 1) Close canceled dialog first
  showCanceledQSInvoicesDialog.value = false;

  // 2) Wait for DOM/dialog state to update (prevents modal stacking)
  await nextTick();

  // 3) Find the full record if needed, otherwise use the invoice directly
  const match =
    (props.repairOrders?.data || []).find((o: any) => o.id === invoice.id) ||
    (props.repairOrders?.data || []).find((o: any) => o.ro_number === invoice.ro_number);

  // 4) Open edit modal
  openEdit(match || invoice);
}
</script>
