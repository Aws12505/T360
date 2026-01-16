<template>
  <Dialog v-model:open="openProxy">
    <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>Manage Vendors</DialogTitle>
        <DialogDescription class="text-sm sm:text-base">
          Add or remove vendors for repair orders.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 sm:space-y-6">
        <form @submit.prevent="$emit('submitVendor')" class="space-y-3 sm:space-y-4">
          <div class="space-y-1 sm:space-y-2">
            <Label for="vendor_name">Vendor Name</Label>
            <Input
              id="vendor_name"
              v-model="vendorForm.vendor_name"
              required
              class="text-sm sm:text-base"
            />
          </div>

          <Button type="submit" class="w-full">Add Vendor</Button>
        </form>

        <div class="overflow-hidden rounded-md border">
          <div class="max-h-[40vh] overflow-y-auto sm:max-h-[300px]">
            <Table>
              <TableHeader class="sticky top-0 z-10 bg-background">
                <TableRow class="sticky top-0 z-10 border-b bg-background">
                  <TableHead class="text-xs sm:text-sm">Vendor Name</TableHead>
                  <TableHead class="text-xs sm:text-sm">Actions</TableHead>
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-if="vendors.length === 0">
                  <TableCell colspan="2" class="py-4 text-center text-sm"
                    >No vendors found.</TableCell
                  >
                </TableRow>

                <TableRow v-for="vendor in vendors" :key="vendor.id">
                  <TableCell class="text-xs sm:text-sm">
                    {{ vendor.vendor_name }}
                    <span v-if="vendor.deleted_at" class="ml-1 text-xs text-red-500"
                      >(Deleted)</span
                    >
                  </TableCell>

                  <TableCell>
                    <div class="flex space-x-1 sm:space-x-2">
                      <Button
                        v-if="vendor.deleted_at"
                        type="button"
                        @click="$emit('restoreVendor', vendor.id)"
                        variant="outline"
                        size="sm"
                      >
                        <Icon name="undo" class="h-4 w-4" />
                      </Button>

                      <Button
                        v-if="!vendor.deleted_at"
                        type="button"
                        @click="$emit('deleteVendor', vendor.id)"
                        variant="destructive"
                        size="sm"
                      >
                        <Icon name="trash" class="h-4 w-4" />
                      </Button>

                      <Button
                        v-if="vendor.deleted_at"
                        type="button"
                        @click="$emit('forceDeleteVendor', vendor.id)"
                        variant="destructive"
                        size="sm"
                      >
                        <Icon name="x" class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button
          type="button"
          @click="$emit('close')"
          variant="outline"
          class="w-full sm:w-auto"
          >Close</Button
        >
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
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from "@/components/ui";

const props = defineProps<{
  open: boolean;
  vendors: any[];
  vendorForm: any;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submitVendor"): void;
  (e: "deleteVendor", id: number): void;
  (e: "restoreVendor", id: number): void;
  (e: "forceDeleteVendor", id: number): void;
  (e: "close"): void;
}>();

const openProxy = computed({
  get: () => props.open,
  set: (v) => emit("update:open", v),
});
</script>
