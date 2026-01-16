<template>
  <Dialog v-model:open="openProxy">
    <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-4xl">
      <DialogHeader class="px-4 sm:px-6 border-b pb-3">
        <div class="flex items-center gap-2">
          <Icon :name="form.id ? 'pencil' : 'plus-circle'" class="h-5 w-5 text-primary" />
          <DialogTitle class="text-lg sm:text-xl font-semibold">
            {{ formAction }} Repair Order
          </DialogTitle>
        </div>
        <DialogDescription class="text-xs sm:text-sm mt-1 text-muted-foreground">
          Fill in the details to {{ formAction.toLowerCase() }} a repair order.
        </DialogDescription>
      </DialogHeader>

      <div class="max-h-[70vh] overflow-y-auto px-4 sm:px-6">
        <form
          @submit.prevent="$emit('submitForm')"
          class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 sm:gap-4 sm:p-4"
        >
          <!-- Company (Admin only) -->
          <div v-if="isAdmin" class="col-span-2 mb-1">
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="building" class="h-4 w-4 text-muted-foreground" />
              Company
            </Label>
            <div class="relative">
              <select
                v-model="form.tenant_id"
                required
                class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm ring-offset-background focus-visible:ring-2"
              >
                <option disabled value="">Select</option>
                <option v-for="t in tenants" :key="t.id" :value="t.id">
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
          </div>

          <!-- Basic Information -->
          <div class="col-span-2 border-b pb-1 mb-2 flex items-center gap-2">
            <Icon name="info" class="h-4 w-4 text-primary" />
            <h3 class="text-md font-semibold text-primary">Basic Information</h3>
          </div>

          <div class="grid grid-cols-2 gap-3 col-span-2">
            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="hash" class="h-4 w-4 text-muted-foreground" />
                RO#
              </Label>
              <Input v-model="form.ro_number" required class="h-9 w-full" />
            </div>

            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="truck" class="h-4 w-4 text-muted-foreground" />
                Truck
              </Label>
              <div class="relative">
                <select
                  v-model="form.truck_id"
                  required
                  class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm"
                >
                  <option disabled value="">Select</option>
                  <option v-for="t in trucks" :key="t.id" :value="t.id">
                    {{ t.truckid }}
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

            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="calendar" class="h-4 w-4 text-muted-foreground" />
                Open Date
              </Label>
              <Input
                type="date"
                v-model="form.ro_open_date"
                required
                class="h-9 w-full"
              />
            </div>

            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="calendar-check" class="h-4 w-4 text-muted-foreground" />
                Close Date
              </Label>
              <Input type="date" v-model="form.ro_close_date" class="h-9 w-full" />
            </div>
          </div>

          <!-- Repair Details -->
          <div class="col-span-2 border-b pb-1 mb-2 mt-1 flex items-center gap-2">
            <Icon name="wrench" class="h-4 w-4 text-primary" />
            <h3 class="text-md font-semibold text-primary">Repair Details</h3>
          </div>

          <div class="col-span-2">
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="alert-triangle" class="h-4 w-4 text-muted-foreground" />
              Areas of Concern
            </Label>

            <div
              class="flex flex-wrap gap-1 mb-2 bg-muted/30 p-2 rounded-md min-h-[40px]"
            >
              <span
                v-for="id in form.area_of_concerns"
                :key="id"
                class="badge bg-primary/10 text-primary px-2 py-1 rounded-md flex items-center"
              >
                {{ areasMap[id] }}
                <button
                  type="button"
                  @click="$emit('removeArea', id)"
                  class="ml-1 hover:text-red-500 focus:outline-none"
                >
                  ×
                </button>
              </span>

              <span
                v-if="!form.area_of_concerns.length"
                class="text-muted-foreground text-sm italic"
              >
                No areas selected
              </span>
            </div>

            <div class="relative">
              <select
                @change="$emit('addArea', $event)"
                class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm"
              >
                <option value="">Select an area to add</option>
                <option v-for="a in availableAreas" :key="a.id" :value="a.id">
                  {{ a.concern }}
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

          <div class="col-span-2">
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="clipboard-check" class="h-4 w-4 text-muted-foreground" />
              Repairs Made
            </Label>
            <textarea
              v-model="form.repairs_made"
              class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
              rows="2"
            ></textarea>
          </div>

          <!-- Vendor & Invoice Information -->
          <div class="col-span-2 border-b pb-1 mb-2 mt-1 flex items-center gap-2">
            <Icon name="receipt" class="h-4 w-4 text-primary" />
            <h3 class="text-md font-semibold text-primary">
              Vendor & Invoice Information
            </h3>
          </div>

          <div class="grid grid-cols-2 gap-3 col-span-2">
            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="building-2" class="h-4 w-4 text-muted-foreground" />
                Vendor
              </Label>
              <div class="relative">
                <select
                  v-model="form.vendor_id"
                  required
                  class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm"
                >
                  <option disabled value="">Select</option>
                  <option v-for="v in vendors" :key="v.id" :value="v.id">
                    {{ v.vendor_name }}
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

            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="file-text" class="h-4 w-4 text-muted-foreground" />
                WO#
              </Label>
              <Input v-model="form.wo_number" class="h-9 w-full" />
            </div>

            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="activity" class="h-4 w-4 text-muted-foreground" />
                WO Status
              </Label>

              <div class="relative">
                <select
                  v-model="form.wo_status_id"
                  class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm"
                >
                  <option :value="null">— None —</option>
                  <option v-for="s in woStatuses" :key="s.id" :value="s.id">
                    {{ s.name }}
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

            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="check-square" class="h-4 w-4 text-muted-foreground" />
                On QS
              </Label>
              <div class="relative">
                <select
                  v-model="form.on_qs"
                  required
                  class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm"
                >
                  <option value="yes">Yes</option>
                  <option value="no">No</option>
                  <option value="not expected">Not Expected</option>
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
          </div>

          <div class="grid grid-cols-2 gap-3 col-span-2">
            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="file-invoice" class="h-4 w-4 text-muted-foreground" />
                Invoice
              </Label>
              <Input v-model="form.invoice" class="h-9 w-full" />
            </div>

            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="dollar-sign" class="h-4 w-4 text-muted-foreground" />
                Amount
              </Label>
              <div class="relative">
                <span
                  class="absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground"
                  >$</span
                >
                <Input
                  type="number"
                  step="0.01"
                  v-model="form.invoice_amount"
                  class="pl-7 h-9 w-full"
                />
              </div>
            </div>

            <div class="flex items-center space-x-2">
              <input
                type="checkbox"
                v-model="form.invoice_received"
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-1 focus:ring-primary"
              />
              <Label class="flex items-center gap-1.5 text-sm font-medium">
                <Icon name="inbox" class="h-4 w-4 text-muted-foreground" />
                Invoice Received
              </Label>
            </div>

            <div>
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="calendar-days" class="h-4 w-4 text-muted-foreground" />
                QS Invoice Date
              </Label>
              <Input type="date" v-model="form.qs_invoice_date" class="h-9 w-full" />
            </div>
          </div>

          <!-- Dispute Information (Update only) -->
          <div v-if="formAction === 'Update'" class="col-span-2">
            <div class="border-b pb-1 mb-2 mt-1 flex items-center gap-2">
              <Icon name="alert-octagon" class="h-4 w-4 text-primary" />
              <h3 class="text-md font-semibold text-primary">Dispute Information</h3>
            </div>

            <div class="flex items-center space-x-2">
              <input
                type="checkbox"
                v-model="form.disputed"
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-1 focus:ring-primary"
              />
              <Label class="flex items-center gap-1.5 text-sm font-medium">
                <Icon name="alert-circle" class="h-4 w-4 text-muted-foreground" />
                Disputed?
              </Label>
            </div>

            <div v-if="form.disputed" class="mt-2">
              <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
                <Icon name="message-square" class="h-4 w-4 text-muted-foreground" />
                Dispute Outcome
              </Label>
              <textarea
                v-model="form.dispute_outcome"
                class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                rows="2"
              ></textarea>
            </div>
          </div>
        </form>
      </div>

      <DialogFooter class="px-4 sm:px-6 flex justify-end gap-2 mt-2 pt-3 border-t">
        <Button
          variant="outline"
          @click="$emit('closeModal')"
          class="h-9 px-4 py-1 text-xs sm:text-sm"
        >
          Cancel
        </Button>
        <Button
          type="button"
          @click="$emit('submitForm')"
          class="h-9 px-4 py-1 text-xs sm:text-sm"
        >
          {{ formAction }}
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
  Input,
  Label,
} from "@/components/ui";

const props = defineProps<{
  open: boolean;
  isAdmin: boolean;
  tenants: any[];
  trucks: any[];
  vendors: any[];
  woStatuses: any[];
  areasOfConcern: any[];
  form: any;
  formAction: "Create" | "Update";
  areasMap: Record<number, string>;
  availableAreas: any[];
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submitForm"): void;
  (e: "closeModal"): void;
  (e: "addArea", ev: Event): void;
  (e: "removeArea", id: number): void;
}>();

const openProxy = computed({
  get: () => props.open,
  set: (v) => emit("update:open", v),
});
</script>
