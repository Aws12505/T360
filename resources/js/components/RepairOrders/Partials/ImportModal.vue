<template>
  <Dialog v-model:open="openProxy">
    <DialogContent
      class="max-w-[95vw] sm:max-w-[90vw] md:max-w-5xl max-h-[90vh] overflow-hidden flex flex-col"
    >
      <DialogHeader class="px-4 sm:px-6 border-b pb-3">
        <div class="flex items-center gap-2">
          <Icon name="upload" class="h-5 w-5 text-primary" />
          <DialogTitle class="text-lg sm:text-xl font-semibold">
            Import Repair Orders
          </DialogTitle>
        </div>
        <DialogDescription class="text-xs sm:text-sm mt-1 text-muted-foreground">
          Choose an import format, then upload a CSV. The file will be validated before
          import.
        </DialogDescription>
      </DialogHeader>

      <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4">
        <!-- Step 1 -->
        <div v-if="!importValidationResults">
          <div class="space-y-4">
            <!-- Import type selector -->
            <div class="rounded-lg border p-4 bg-muted/10 space-y-3">
              <div class="flex items-center gap-2">
                <Icon name="sliders" class="h-4 w-4 text-muted-foreground" />
                <div class="text-sm font-semibold">Import format</div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label
                  class="flex items-start gap-3 rounded-md border p-3 cursor-pointer hover:bg-muted/20 transition-colors"
                  :class="
                    importTypeProxy === 'template' ? 'border-primary bg-primary/5' : ''
                  "
                >
                  <input
                    type="radio"
                    class="mt-1"
                    value="template"
                    v-model="importTypeProxy"
                    :disabled="isValidating"
                  />
                  <div class="space-y-1">
                    <div class="text-sm font-medium">Template Import</div>
                    <div class="text-xs text-muted-foreground">
                      Uses the standard Repair Orders CSV template (download below).
                    </div>
                  </div>
                </label>

                <label
                  class="flex items-start gap-3 rounded-md border p-3 cursor-pointer hover:bg-muted/20 transition-colors"
                  :class="
                    importTypeProxy === 'quicksight' ? 'border-primary bg-primary/5' : ''
                  "
                >
                  <input
                    type="radio"
                    class="mt-1"
                    value="quicksight"
                    v-model="importTypeProxy"
                    :disabled="isValidating"
                  />
                  <div class="space-y-1">
                    <div class="text-sm font-medium">QuickSight CSV Import</div>
                    <div class="text-xs text-muted-foreground">
                      Upload a QuickSight-exported CSV and we’ll map columns
                      automatically.
                    </div>
                  </div>
                </label>
              </div>

              <!-- tenant select for SuperAdmin + quicksight -->
              <div
                v-if="isAdmin && importTypeProxy === 'quicksight'"
                class="pt-2 border-t"
              >
                <Label class="flex items-center gap-1.5 mb-2 text-sm font-medium">
                  <Icon name="building" class="h-4 w-4 text-muted-foreground" />
                  Company (required for QuickSight import)
                </Label>

                <div class="relative">
                  <select
                    v-model="importTenantIdProxy"
                    class="flex h-10 w-full appearance-none items-center rounded-md border bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                    :disabled="isValidating"
                  >
                    <option value="">Select a company</option>
                    <option v-for="t in tenants" :key="t.id" :value="String(t.id)">
                      {{ t.name }}
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

                <Alert
                  v-if="
                    isAdmin && importTypeProxy === 'quicksight' && !importTenantIdProxy
                  "
                  variant="destructive"
                  class="mt-3"
                >
                  <AlertTitle class="flex items-center gap-2">
                    <Icon name="alert_circle" class="h-5 w-5" />
                    Required
                  </AlertTitle>
                  <AlertDescription>
                    Please select a company before validating a QuickSight CSV.
                  </AlertDescription>
                </Alert>
              </div>
            </div>

            <!-- Dropzone -->
            <div
              class="flex flex-col items-center justify-center border-2 border-dashed rounded-lg p-8 bg-muted/20 transition-colors"
              :class="{
                'border-primary bg-primary/5': isDragging,
                'opacity-60 pointer-events-none': isValidating,
              }"
              @dragenter.prevent="$emit('onDragEnter')"
              @dragover.prevent="$emit('onDragOver')"
              @dragleave.prevent="$emit('onDragLeave')"
              @drop.prevent="onDropInternal"
            >
              <Icon
                name="file-spreadsheet"
                class="h-12 w-12 text-muted-foreground mb-3"
              />

              <div class="text-center">
                <div class="text-sm font-medium">
                  <span class="text-primary">Drag & drop</span> your CSV here
                </div>
                <p class="text-xs text-muted-foreground mt-1">or</p>
              </div>

              <label class="cursor-pointer mt-3">
                <span class="text-sm font-medium text-primary hover:underline">
                  Choose CSV file
                </span>

                <input
                  type="file"
                  class="hidden"
                  accept=".csv,text/csv"
                  :disabled="
                    isValidating ||
                    (isAdmin && importTypeProxy === 'quicksight' && !importTenantIdProxy)
                  "
                  @change="onInputInternal"
                />
              </label>

              <p class="text-xs text-muted-foreground mt-2">CSV only</p>

              <div v-if="isDragging" class="mt-3 text-xs text-primary font-medium">
                Drop file to validate
              </div>
            </div>

            <!-- Template download only for template import -->
            <div
              v-if="importTypeProxy === 'template'"
              class="flex items-center gap-2 text-sm text-muted-foreground"
            >
              <Icon name="info" class="h-4 w-4" />
              <a :href="templateUrl" download class="text-primary hover:underline">
                Download CSV Template
              </a>
            </div>

            <div v-if="isValidating" class="flex items-center justify-center gap-2 p-4">
              <div
                class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
              ></div>
              <span class="text-sm text-muted-foreground">Validating CSV file...</span>
            </div>
          </div>
        </div>

        <!-- Step 2 (unchanged) -->
        <div v-else class="space-y-4">
          <!-- ... keep your Step 2 exactly as-is ... -->
          <!-- (no changes needed for your switching bug) -->

          <!-- NOTE: your existing Step 2 markup can stay exactly as you already have it. -->
          <!-- I’m not re-pasting it to avoid duplicating a huge block, since the bug is Step 1 binding. -->
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t p-4 flex justify-end gap-3">
        <Button
          @click="$emit('closeImportModal')"
          variant="outline"
          :disabled="isImporting"
        >
          Close
        </Button>

        <Button
          v-if="importValidationResults && importValidationResults.summary.valid > 0"
          @click="$emit('confirmImport')"
          variant="default"
          :disabled="
            isImporting ||
            importValidationResults.summary.invalid > 0 ||
            Boolean(importValidationResults.header_error)
          "
          class="flex items-center gap-2"
        >
          <div
            v-if="isImporting"
            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
          ></div>
          <Icon v-else name="check" class="h-4 w-4" />
          {{
            isImporting
              ? "Importing..."
              : `Import ${importValidationResults.summary.valid} Rows`
          }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Icon from "@/components/Icon.vue";
import {
  Alert,
  AlertTitle,
  AlertDescription,
  Button,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  Label,
} from "@/components/ui";

const props = defineProps<{
  open: boolean;
  importValidationResults: any;
  isValidating: boolean;
  isImporting: boolean;

  // ✅ v-model props
  importType: "template" | "quicksight";
  importTenantId: string | number;

  isAdmin: boolean;
  tenants: any[];
  templateUrl: string;
  isDragging: boolean;
  hasWarnings: boolean;
  formatCurrency: (a: any) => string;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "update:importType", v: "template" | "quicksight"): void;
  (e: "update:importTenantId", v: string | number): void;

  (e: "closeImportModal"): void;
  (e: "confirmImport"): void;
  (e: "downloadErrorReport"): void;

  (e: "onImportInputChange", file: File): void;
  (e: "onDragEnter"): void;
  (e: "onDragOver"): void;
  (e: "onDragLeave"): void;
  (e: "onDrop", file: File): void;
}>();

const openProxy = computed({
  get: () => props.open,
  set: (v) => emit("update:open", v),
});

/**
 * ✅ Proper two-way binding (fixes switching)
 */
const importTypeProxy = computed({
  get: () => props.importType,
  set: (v) => emit("update:importType", v),
});

const importTenantIdProxy = computed<string>({
  get: () => String(props.importTenantId ?? ""),
  set: (v) => emit("update:importTenantId", v),
});

function onInputInternal(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  emit("onImportInputChange", file);
  input.value = "";
}

function onDropInternal(e: DragEvent) {
  const file = e.dataTransfer?.files?.[0];
  if (!file) return;
  emit("onDrop", file);
}
</script>
