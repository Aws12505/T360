<template>
  <Dialog v-model:open="openProxy">
    <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
      <!-- Header -->
      <DialogHeader class="px-4 sm:px-6 border-b pb-3">
        <div class="flex items-center gap-2">
          <Icon name="upload" class="h-5 w-5 text-primary" />
          <DialogTitle class="text-lg sm:text-xl font-semibold">Import Delays</DialogTitle>
        </div>
        <DialogDescription class="text-xs sm:text-sm mt-1 text-muted-foreground">
          Choose an import type, then upload your CSV file. The file will be validated
          before import.
        </DialogDescription>
      </DialogHeader>

      <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 space-y-5">
        <!-- ── Step 0 (Super Admin only): Choose Tenant ── -->
        <div v-if="isSuperAdmin" class="space-y-2">
          <p class="text-sm font-semibold flex items-center gap-2">
            <span
              class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">1</span>
            Select Company
          </p>
          <select v-model="selectedTenantId" class="select-base" :disabled="isValidating || isImporting"
            @change="resetFile">
            <option :value="null" disabled>— Choose a company —</option>
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
              {{ tenant.name }}
            </option>
          </select>
          <p v-if="!selectedTenantId" class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
            <Icon name="alert-triangle" class="h-3 w-3" />
            You must select a company before uploading a file.
          </p>
        </div>

        <!-- ── Step 1: Choose Import Type ── -->
        <div class="space-y-2" :class="{ 'opacity-50 pointer-events-none': isSuperAdmin && !selectedTenantId }">
          <p class="text-sm font-semibold flex items-center gap-2">
            <span
              class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">
              {{ isSuperAdmin ? "2" : "1" }}
            </span>
            Import Type
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label v-for="option in importOptions" :key="option.value"
              class="flex items-start gap-3 rounded-md border p-3 cursor-pointer hover:bg-muted/20 transition-colors"
              :class="selectedType === option.value
                ? 'border-primary bg-primary/5'
                : 'border-border'
                ">
              <input type="radio" class="mt-1 accent-primary" :value="option.value" v-model="selectedType" :disabled="isValidating || isImporting || (isSuperAdmin && !selectedTenantId)
                " @change="resetFile" />
              <div class="space-y-0.5">
                <div class="text-sm font-medium">{{ option.label }}</div>
                <div class="text-xs text-muted-foreground">{{ option.description }}</div>
              </div>
            </label>
          </div>
        </div>

        <!-- ── Step 2: Upload ── -->
        <template v-if="selectedType && !validationResults && (!isSuperAdmin || selectedTenantId)">
          <div class="space-y-2">
            <p class="text-sm font-semibold flex items-center gap-2">
              <span
                class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">
                {{ isSuperAdmin ? "3" : "2" }}
              </span>
              Upload File
            </p>
          </div>

          <!-- Dropzone -->
          <div
            class="flex flex-col items-center justify-center border-2 border-dashed rounded-lg p-8 bg-muted/10 transition-colors"
            :class="{
              'border-primary bg-primary/5': isDragging,
              'opacity-60 pointer-events-none': isValidating,
            }" @dragenter.prevent="onDragEnter" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
            @drop.prevent="onDrop">
            <Icon name="file-spreadsheet" class="h-12 w-12 text-muted-foreground mb-3" />
            <div class="text-center">
              <p class="text-sm font-medium">
                <span class="text-primary">Drag & drop</span> your CSV here
              </p>
              <p class="text-xs text-muted-foreground mt-1">or</p>
            </div>
            <label class="cursor-pointer mt-3">
              <span class="text-sm font-medium text-primary hover:underline">Choose CSV file</span>
              <input ref="mainFileInput" type="file" class="hidden" accept=".csv,text/csv" @change="onMainFileChange"
                :disabled="isValidating" />
            </label>
            <p v-if="selectedFile" class="mt-2 text-xs text-muted-foreground">
              Selected:
              <span class="font-medium text-foreground">{{ selectedFile.name }}</span>
            </p>
            <p v-else class="text-xs text-muted-foreground mt-2">CSV files only</p>
          </div>

          <!-- Validating spinner -->
          <div v-if="isValidating" class="flex items-center justify-center gap-2 py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            <span class="text-sm text-muted-foreground">Validating CSV file...</span>
          </div>
        </template>

        <!-- ── Step 3: Validation Results ── -->
        <template v-if="validationResults">
          <!-- Header error -->
          <div v-if="validationResults.header_error"
            class="rounded-md border border-destructive bg-destructive/10 p-4 space-y-2">
            <div class="flex items-center gap-2 text-destructive font-semibold text-sm">
              <Icon name="alert-circle" class="h-4 w-4" />
              Header Error
            </div>
            <p class="text-sm text-destructive">{{ validationResults.header_error }}</p>
            <Button variant="outline" size="sm" @click="resetFile">Try Again</Button>
          </div>

          <template v-else>
            <!-- Summary -->
            <div class="grid grid-cols-3 gap-3">
              <div class="rounded-md border bg-muted/20 p-3 text-center">
                <div class="text-2xl font-bold">
                  {{ validationResults.summary?.total ?? 0 }}
                </div>
                <div class="text-xs text-muted-foreground mt-1">Total Rows</div>
              </div>
              <div class="rounded-md border border-green-200 bg-green-50 dark:bg-green-900/10 p-3 text-center">
                <div class="text-2xl font-bold text-green-600">
                  {{ validationResults.summary?.valid ?? 0 }}
                </div>
                <div class="text-xs text-muted-foreground mt-1">Valid</div>
              </div>
              <div class="rounded-md border border-red-200 bg-red-50 dark:bg-red-900/10 p-3 text-center">
                <div class="text-2xl font-bold text-red-600">
                  {{ validationResults.summary?.invalid ?? 0 }}
                </div>
                <div class="text-xs text-muted-foreground mt-1">Invalid</div>
              </div>
            </div>
            <!-- ✅ NEW: Missing Arrival Time rows (requires user input) -->
            <div v-if="validationResults.needs_input?.length > 0" class="space-y-2">
              <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                  <Icon name="alert-triangle" class="h-4 w-4" />
                  Missing Arrival Time ({{ validationResults.needs_input.length }})
                </p>

                <p class="text-xs text-muted-foreground">
                  Please enter the missing date/time before importing.
                </p>
              </div>

              <div class="max-h-64 overflow-y-auto rounded-md border divide-y">
                <div v-for="row in validationResults.needs_input" :key="row.rowNumber"
                  class="p-3 bg-amber-50/50 dark:bg-amber-900/10 space-y-2">
                  <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div class="text-xs font-semibold whitespace-nowrap">
                      Row {{ row.rowNumber }}
                    </div>

                    <div class="flex flex-wrap gap-2">
                      <span v-for="p in row.preview || []" :key="p.key" class="text-xs bg-muted rounded px-1.5 py-0.5">
                        {{ p.label }}: <span class="font-medium">{{ p.value }}</span>
                      </span>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 items-end">
                    <div class="space-y-1">
                      <Label class="text-xs">
                        {{
                          row.missing_field === "Origin Yard Arrival Time"
                            ? "Origin Yard Arrival Time"
                            : "Destination Yard Arrival Time"
                        }}
                      </Label>

                      <DateTimePopoverField :model-value="row.manual_datetime"
                        @update:modelValue="setManualDatetime(row, $event)" :disabled="isImporting"
                        date-label="Arrival Date" time-label="Arrival Time" />

                      <p v-if="!row.manual_datetime" class="text-xs text-destructive">
                        This field is required to import this row.
                      </p>
                    </div>

                    <div class="text-xs text-muted-foreground">
                      This row will be imported with:
                      <span class="font-medium">Penalty 0</span>,
                      <span class="font-medium">no duration</span>,
                      <span class="font-medium">not controllable</span>,
                      <span class="font-medium">no category</span>.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Invalid rows -->
            <div v-if="validationResults.invalid?.length > 0" class="space-y-2">
              <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-destructive flex items-center gap-1">
                  <Icon name="alert-circle" class="h-4 w-4" />
                  Invalid Rows ({{ validationResults.invalid.length }})
                </p>
                <Button variant="outline" size="sm" @click="downloadErrorReport">
                  <Icon name="download" class="mr-1 h-3 w-3" />
                  Download Error Report
                </Button>
              </div>
              <div class="max-h-48 overflow-y-auto rounded-md border divide-y">
                <div v-for="row in validationResults.invalid" :key="row.rowNumber"
                  class="p-3 bg-red-50/50 dark:bg-red-900/5">
                  <div class="flex items-start gap-2">
                    <span class="text-xs font-semibold text-red-600 whitespace-nowrap">Row {{ row.rowNumber }}</span>
                    <div class="flex-1 space-y-1">
                      <div v-if="row.preview?.length" class="flex flex-wrap gap-2">
                        <span v-for="p in row.preview" :key="p.key" class="text-xs bg-muted rounded px-1.5 py-0.5">
                          {{ p.label }}: <span class="font-medium">{{ p.value }}</span>
                        </span>
                      </div>
                      <p v-for="err in row.errors" :key="err" class="text-xs text-red-600">
                        • {{ err }}
                      </p>
                      <p v-for="warn in row.warnings" :key="warn" class="text-xs text-amber-600">
                        ⚠ {{ warn }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- All valid confirmation -->
            <div v-if="
              validationResults.valid?.length > 0 &&
              validationResults.summary?.invalid === 0
            " class="space-y-2">
              <p class="text-sm font-semibold text-green-600 flex items-center gap-1">
                <Icon name="check-circle" class="h-4 w-4" />
                All {{ importReadyCount }} rows are valid and ready to import.
              </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2 pt-2">
              <Button variant="outline" size="sm" @click="resetFile">
                <Icon name="rotate_ccw" class="mr-1 h-3 w-3" />
                Upload Different File
              </Button>
              <Button v-if="(validationResults.summary?.invalid ?? 0) === 0" variant="default" size="sm"
                :disabled="isImporting || !canConfirmImport" @click="confirmImport">
                <p v-if="!canConfirmImport && validationResults.needs_input?.length" class="text-xs text-amber-600">
                  Please fill all missing arrival times to enable import.
                </p>
                <div v-if="isImporting" class="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-2"></div>
                <Icon v-else name="upload" class="mr-1 h-3 w-3" />
                {{ importButtonText }}
              </Button>
            </div>
          </template>
        </template>
      </div>

      <!-- Footer -->
      <div class="border-t px-4 sm:px-6 py-3 flex justify-end">
        <Button variant="outline" @click="handleClose" :disabled="isImporting">Close</Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Button from "@/components/ui/button/Button.vue";
import Icon from "@/components/Icon.vue";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from "@/components/ui/dialog";
import DateTimePopoverField from "@/components/ui/date-time-popover-field.vue";
const props = defineProps({
  open: { type: Boolean, default: false },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: { type: String, default: null },
  tenants: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:open", "success", "error"]);

const page = usePage();

const openProxy = computed({
  get: () => props.open,
  set: (v) => emit("update:open", v),
});

// ─── State ────────────────────────────────────────────────────────────────────

const selectedTenantId = ref<number | null>(null);
const selectedType = ref("");
const selectedFile = ref<File | null>(null);
const validationResults = ref<any>(null);
const isValidating = ref(false);
const isImporting = ref(false);
const isDragging = ref(false);
const mainFileInput = ref<HTMLInputElement | null>(null);
let dragDepth = 0;

const importOptions = [
  {
    value: "origin",
    label: "Origin Delays",
    description: "Import on-time to origin delays from the origin CSV report.",
  },
  {
    value: "destination",
    label: "Destination Delays",
    description: "Import on-time to destination delays from the destination CSV report.",
  },
];

// ─── Computed: selected tenant name for success message ───────────────────────
const needsInputRows = computed(() => validationResults.value?.needs_input ?? []);

function toDatetimeLocalValue(val: string | null | undefined) {
  if (!val) return "";
  // Accept either "YYYY-MM-DDTHH:mm" already, or "YYYY-MM-DD HH:mm"
  // Keep it simple: if it has a "T", assume it's good.
  if (val.includes("T")) return val.slice(0, 16);
  // convert "YYYY-MM-DD HH:mm" -> "YYYY-MM-DDTHH:mm"
  return val.replace(" ", "T").slice(0, 16);
}

function setManualDatetime(row: any, value: string | null) {
  row.manual_datetime = value || "";
}

const isMissingRequiredManualDates = computed(() => {
  const rows = needsInputRows.value;
  if (!rows.length) return false;
  return rows.some(
    (r: any) => !r.manual_datetime || String(r.manual_datetime).trim() === ""
  );
});

const canConfirmImport = computed(() => {
  // existing rule: only allow confirm when invalid === 0
  const invalid = validationResults.value?.summary?.invalid ?? 0;
  if (invalid !== 0) return false;

  // new rule: if needs_input exists, require all manual datetimes
  if (isMissingRequiredManualDates.value) return false;

  return true;
});
const selectedTenantName = computed(() => {
  if (!props.isSuperAdmin || !selectedTenantId.value) return null;
  const tenant = (props.tenants as any[]).find((t) => t.id === selectedTenantId.value);
  return tenant?.name ?? null;
});

// ─── Route helpers ────────────────────────────────────────────────────────────

function getRoute(action: string): string {
  const map: Record<string, { admin: string; tenant: string }> = {
    validate: { admin: "ontime.validateImport.admin", tenant: "ontime.validateImport" },
    confirm: { admin: "ontime.confirmImport.admin", tenant: "ontime.confirmImport" },
    downloadErrorReport: {
      admin: "ontime.downloadErrorReport.admin",
      tenant: "ontime.downloadErrorReport",
    },
  };
  const names = map[action];
  return props.isSuperAdmin
    ? route(names.admin)
    : route(names.tenant, { tenantSlug: props.tenantSlug });
}

// ─── File handling ────────────────────────────────────────────────────────────

function onMainFileChange(e: Event) {
  const input = e.target as HTMLInputElement;
  if (input.files?.[0]) {
    selectedFile.value = input.files[0];
    triggerValidation();
  }
}

function onDragEnter() {
  dragDepth++;
  isDragging.value = true;
}
function onDragOver() {
  isDragging.value = true;
}
function onDragLeave() {
  dragDepth--;
  if (dragDepth === 0) isDragging.value = false;
}

function onDrop(e: DragEvent) {
  dragDepth = 0;
  isDragging.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (file) {
    selectedFile.value = file;
    triggerValidation();
  }
}

function resetFile() {
  selectedFile.value = null;
  validationResults.value = null;
  if (mainFileInput.value) mainFileInput.value.value = "";
}

// ─── Validate ─────────────────────────────────────────────────────────────────

function triggerValidation() {
  if (!selectedFile.value || !selectedType.value) return;
  if (props.isSuperAdmin && !selectedTenantId.value) return;

  isValidating.value = true;

  const data = new FormData();
  data.append("file", selectedFile.value);
  data.append("import_type", selectedType.value);

  // Pass tenant_id for super admin so the backend knows which tenant to import for
  if (props.isSuperAdmin && selectedTenantId.value) {
    data.append("tenant_id", String(selectedTenantId.value));
  }

  router.post(getRoute("validate"), data, {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => {
      isValidating.value = false;
    },
    onError: () => {
      isValidating.value = false;
      emit("error", "Failed to validate CSV.");
    },
  });
}

// ─── Confirm import ───────────────────────────────────────────────────────────

function confirmImport() {
  isImporting.value = true;

  // Send tenant_id in confirm request so backend session can be verified
  const payload: Record<string, any> = {};

  if (props.isSuperAdmin && selectedTenantId.value) {
    payload.tenant_id = selectedTenantId.value;
  }

  // ✅ NEW: send corrected rows (rowNumber + manual_datetime)
  const corrected = (validationResults.value?.needs_input ?? []).map((r: any) => ({
    rowNumber: r.rowNumber,
    manual_datetime: r.manual_datetime, // "YYYY-MM-DDTHH:mm"
  }));

  payload.corrected_rows = corrected;

  router.post(getRoute("confirm"), payload, {
    preserveScroll: true,
    onSuccess: () => {
      const count = importReadyCount.value;
      emit(
        "success",
        `Successfully imported ${count} delay(s)${selectedTenantName.value ? ` for ${selectedTenantName.value}` : ""
        }.`
      );
      handleClose();
    },
    onError: () => emit("error", "Import failed. Please try again."),
    onFinish: () => {
      isImporting.value = false;
    },
  });
}
const needsInputFilledCount = computed(() => {
  const rows = needsInputRows.value;
  if (!rows.length) return 0;
  return rows.filter(
    (r: any) => r.manual_datetime && String(r.manual_datetime).trim() !== ""
  ).length;
});

const needsInputTotalCount = computed(() => needsInputRows.value.length);

const importReadyCount = computed(() => {
  const valid = validationResults.value?.summary?.valid ?? 0;
  return valid + needsInputFilledCount.value;
});

const missingManualCount = computed(
  () => needsInputTotalCount.value - needsInputFilledCount.value
);

const importButtonText = computed(() => {
  if (isImporting.value) return "Importing...";

  const ready = importReadyCount.value;

  // If there are rows waiting for manual dates, show progress
  if (needsInputTotalCount.value > 0) {
    const missing = missingManualCount.value;
    if (missing > 0) {
      return `Import ${ready} Delay(s) (missing ${missing} date${missing === 1 ? "" : "s"
        })`;
    }
    return `Import ${ready} Delay(s)`;
  }

  // Original behavior
  return `Import ${ready} Delay(s)`;
});
// ─── Error report ─────────────────────────────────────────────────────────────

function downloadErrorReport() {
  window.location.href = getRoute("downloadErrorReport");
}

// ─── Close ────────────────────────────────────────────────────────────────────

function handleClose() {
  openProxy.value = false;
}

watch(openProxy, (isOpen) => {
  if (!isOpen) {
    selectedTenantId.value = null;
    selectedType.value = "";
    selectedFile.value = null;
    validationResults.value = null;
    isValidating.value = false;
    isImporting.value = false;
    isDragging.value = false;
    dragDepth = 0;
  }
});

// ─── Flash: receive validation results ───────────────────────────────────────

watch(
  () => (page.props as any).flash?.importValidation,
  (payload) => {
    if (!payload) return;

    isValidating.value = false;

    if (payload.results) {
      // ✅ Keep full structure including needs_input
      validationResults.value = { ...payload.results };

      // ✅ Normalize needs_input to always exist
      if (!validationResults.value.needs_input) {
        validationResults.value.needs_input = [];
      }

      // ✅ Pre-fill manual_datetime for rows that need it (empty default)
      validationResults.value.needs_input = validationResults.value.needs_input.map(
        (r: any) => ({
          ...r,
          manual_datetime: toDatetimeLocalValue(r.manual_datetime || ""), // usually empty
        })
      );

      if (payload.header_error) {
        validationResults.value.header_error = payload.header_error;
      }
    } else if (payload.message) {
      emit("error", payload.message);
    }
  },
  { immediate: true }
);
</script>

<style scoped>
.select-base {
  @apply flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background appearance-none focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50;
}
</style>
