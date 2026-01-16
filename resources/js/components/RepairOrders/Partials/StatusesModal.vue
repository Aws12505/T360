<template>
  <Dialog v-model:open="openProxy">
    <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>Manage Work Order Statuses</DialogTitle>
        <DialogDescription class="text-sm sm:text-base">
          Add or remove work order statuses for repair orders.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 sm:space-y-6">
        <form @submit.prevent="$emit('submitStatus')" class="space-y-3 sm:space-y-4">
          <div class="space-y-1 sm:space-y-2">
            <Label for="status_name">Status Name</Label>
            <Input
              id="status_name"
              v-model="statusForm.name"
              required
              class="text-sm sm:text-base"
            />
          </div>

          <Button type="submit" class="w-full">Add Work Order Status</Button>
        </form>

        <div class="overflow-hidden rounded-md border">
          <div class="max-h-[40vh] overflow-y-auto sm:max-h-[300px]">
            <Table>
              <TableHeader class="sticky top-0 z-10 bg-background">
                <TableRow class="sticky top-0 z-10 border-b bg-background">
                  <TableHead class="text-xs sm:text-sm">Status Name</TableHead>
                  <TableHead class="text-xs sm:text-sm">Actions</TableHead>
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-if="woStatuses.length === 0">
                  <TableCell colspan="2" class="py-4 text-center text-sm">
                    No work order statuses found.
                  </TableCell>
                </TableRow>

                <TableRow v-for="s in woStatuses" :key="s.id">
                  <TableCell class="text-xs sm:text-sm">
                    {{ s.name }}
                    <span v-if="s.deleted_at" class="ml-1 text-xs text-red-500"
                      >(Deleted)</span
                    >
                  </TableCell>

                  <TableCell>
                    <div class="flex space-x-1 sm:space-x-2">
                      <Button
                        v-if="s.deleted_at"
                        type="button"
                        @click="$emit('restoreStatus', s.id)"
                        variant="outline"
                        size="sm"
                      >
                        <Icon name="undo" class="h-4 w-4" />
                      </Button>

                      <Button
                        v-if="!s.deleted_at"
                        type="button"
                        @click="$emit('deleteStatus', s.id)"
                        variant="destructive"
                        size="sm"
                      >
                        <Icon name="trash" class="h-4 w-4" />
                      </Button>

                      <Button
                        v-if="s.deleted_at"
                        type="button"
                        @click="$emit('forceDeleteStatus', s.id)"
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
  woStatuses: any[];
  statusForm: any;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submitStatus"): void;
  (e: "deleteStatus", id: number): void;
  (e: "restoreStatus", id: number): void;
  (e: "forceDeleteStatus", id: number): void;
  (e: "close"): void;
}>();

const openProxy = computed({
  get: () => props.open,
  set: (v) => emit("update:open", v),
});
</script>
