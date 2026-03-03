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
            <!-- Company selector (SuperAdmin only) -->
            <div v-if="isAdmin" class="rounded-lg border p-4 bg-muted/10 space-y-3">
              <div class="flex items-center gap-2">
                <Icon name="building" class="h-4 w-4 text-muted-foreground" />
                <div class="text-sm font-semibold">Company (Required)</div>
              </div>

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
                v-if="isAdmin && !importTenantIdProxy"
                variant="destructive"
                class="mt-3"
              >
                <AlertTitle class="flex items-center gap-2">
                  <Icon name="alert_circle" class="h-5 w-5" />
                  Required
                </AlertTitle>
                <AlertDescription>
                  Please select a company before validating the CSV.
                </AlertDescription>
              </Alert>
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
                  <span class="text-primary">Drag & drop</span> your QuickSight CSV here
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
                  :disabled="isValidating || (isAdmin && !importTenantIdProxy)"
                  @change="onInputInternal"
                />
              </label>

              <p class="text-xs text-muted-foreground mt-2">QuickSight CSV only</p>

              <div v-if="isDragging" class="mt-3 text-xs text-primary font-medium">
                Drop file to validate
              </div>
            </div>

            <div v-if="isValidating" class="flex items-center justify-center gap-2 p-4">
              <div
                class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
              ></div>
              <span class="text-sm text-muted-foreground"> Validating CSV file... </span>
            </div>
          </div>
        </div>

        <!-- Step 2 (unchanged) -->
        <div v-else class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Badge variant="outline" class="text-xs">
                {{ importType === "template" ? "Template Import" : "QuickSight Import" }}
              </Badge>
              <Badge
                v-if="isAdmin && importType === 'quicksight' && importTenantId"
                variant="outline"
                class="text-xs"
              >
                Tenant ID: {{ importTenantId }}
              </Badge>
            </div>
          </div>

          <div
            v-if="importValidationResults.headers?.length"
            class="rounded-lg border p-3"
          >
            <div class="flex items-center justify-between">
              <div class="text-sm font-semibold">CSV Headers</div>
              <div class="text-xs text-muted-foreground">
                {{ importValidationResults.headers.length }} columns
              </div>
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
              <span
                v-for="h in importValidationResults.headers"
                :key="h"
                class="rounded-full bg-muted px-2 py-0.5 text-xs"
              >
                {{ h }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <Card class="border-2">
              <CardContent class="p-4 text-center">
                <div class="text-2xl font-bold">
                  {{ importValidationResults.summary.total }}
                </div>
                <div class="text-sm text-muted-foreground">Total Rows</div>
              </CardContent>
            </Card>

            <Card class="border-2 border-green-500/50 bg-green-50 dark:bg-green-900/10">
              <CardContent class="p-4 text-center">
                <div class="text-2xl font-bold text-green-600">
                  {{ importValidationResults.summary.valid }}
                </div>
                <div class="text-sm text-muted-foreground">Valid</div>
              </CardContent>
            </Card>

            <Card class="border-2 border-red-500/50 bg-red-50 dark:bg-red-900/10">
              <CardContent class="p-4 text-center">
                <div class="text-2xl font-bold text-red-600">
                  {{ importValidationResults.summary.invalid }}
                </div>
                <div class="text-sm text-muted-foreground">Invalid</div>
              </CardContent>
            </Card>
          </div>

          <Alert v-if="importValidationResults.header_error" variant="destructive">
            <AlertTitle class="flex items-center gap-2">
              <Icon name="alert_circle" class="h-5 w-5" />
              Header Error
            </AlertTitle>
            <AlertDescription>{{
              importValidationResults.header_error
            }}</AlertDescription>
          </Alert>

          <div v-if="importValidationResults.invalid?.length">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-lg font-semibold text-red-600 flex items-center gap-2">
                <Icon name="alert-triangle" class="h-5 w-5" />
                Validation Errors ({{ importValidationResults.invalid.length }})
              </h3>

              <Button
                @click="downloadErrorReport"
                variant="outline"
                size="sm"
                class="flex items-center gap-2"
              >
                <Icon name="download" class="h-4 w-4" />
                Download Error Report
              </Button>
            </div>

            <div class="border rounded-lg overflow-hidden">
              <div class="max-h-96 overflow-y-auto">
                <Table>
                  <TableHeader class="sticky top-0 bg-background">
                    <TableRow>
                      <TableHead class="w-20">Row #</TableHead>
                      <TableHead>Preview</TableHead>
                      <TableHead>Errors</TableHead>
                      <TableHead v-if="hasWarnings">Warnings</TableHead>
                    </TableRow>
                  </TableHeader>

                  <TableBody>
                    <TableRow
                      v-for="row in importValidationResults.invalid"
                      :key="row.rowNumber"
                      class="hover:bg-muted/50"
                    >
                      <TableCell class="font-medium">{{ row.rowNumber }}</TableCell>

                      <TableCell class="text-sm text-muted-foreground">
                        <div class="flex flex-wrap gap-x-3 gap-y-1">
                          <span
                            v-for="p in row.preview"
                            :key="p.key"
                            class="whitespace-nowrap"
                          >
                            <span class="font-medium text-foreground"
                              >{{ p.label }}:</span
                            >
                            {{ p.value }}
                          </span>
                        </div>
                      </TableCell>

                      <TableCell>
                        <div class="space-y-1">
                          <div
                            v-for="(error, idx) in row.errors"
                            :key="idx"
                            class="text-xs text-red-600 flex items-start gap-1"
                          >
                            <Icon name="x-circle" class="h-3 w-3 mt-0.5 flex-shrink-0" />
                            <span>{{ error }}</span>
                          </div>
                        </div>
                      </TableCell>

                      <TableCell v-if="hasWarnings">
                        <div class="space-y-1">
                          <div
                            v-for="(warning, idx) in row.warnings"
                            :key="idx"
                            class="text-xs text-yellow-600 flex items-start gap-1"
                          >
                            <Icon
                              name="alert-triangle"
                              class="h-3 w-3 mt-0.5 flex-shrink-0"
                            />
                            <span>{{ warning }}</span>
                          </div>
                          <div
                            v-if="!row.warnings?.length"
                            class="text-xs text-muted-foreground"
                          >
                            —
                          </div>
                        </div>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
          </div>

          <div v-if="importValidationResults.valid?.length">
            <h3 class="text-lg font-semibold text-green-600 flex items-center gap-2 mb-3">
              <Icon name="check-circle" class="h-5 w-5" />
              Valid Rows ({{ importValidationResults.valid.length }})
            </h3>

            <div class="text-sm text-muted-foreground mb-2">
              Showing first 5 valid rows
            </div>

            <div class="border rounded-lg overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Row #</TableHead>
                    <TableHead>Preview</TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow
                    v-for="row in importValidationResults.valid.slice(0, 5)"
                    :key="row.rowNumber"
                  >
                    <TableCell class="font-medium">{{ row.rowNumber }}</TableCell>

                    <TableCell class="text-sm">
                      <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <span
                          v-for="p in row.preview"
                          :key="p.key"
                          class="whitespace-nowrap"
                        >
                          <span class="font-medium">{{ p.label }}:</span>
                          {{ p.value }}
                        </span>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
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
