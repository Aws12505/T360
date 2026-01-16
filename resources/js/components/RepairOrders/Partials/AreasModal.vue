<template>
  <Dialog v-model:open="openProxy">
    <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>Manage Areas of Concern</DialogTitle>
        <DialogDescription class="text-sm sm:text-base">
          Add or remove areas of concern for repair orders.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 sm:space-y-6">
        <form @submit.prevent="$emit('submitArea')" class="space-y-3 sm:space-y-4">
          <div class="space-y-1 sm:space-y-2">
            <Label for="concern">Area of Concern</Label>
            <Input
              id="concern"
              v-model="areaForm.concern"
              required
              class="text-sm sm:text-base"
            />
          </div>

          <Button type="submit" class="w-full">Add Area of Concern</Button>
        </form>

        <div class="rounded-md border">
          <div class="max-h-[40vh] overflow-y-auto sm:max-h-[300px]">
            <Table>
              <TableHeader class="sticky top-0 z-10 bg-background">
                <TableRow>
                  <TableHead class="text-xs sm:text-sm">Area of Concern</TableHead>
                  <TableHead class="w-20 text-xs sm:text-sm">Actions</TableHead>
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-if="areas.length === 0">
                  <TableCell colspan="2" class="py-4 text-center text-sm">
                    No areas of concern found.
                  </TableCell>
                </TableRow>

                <TableRow v-for="a in areas" :key="a.id">
                  <TableCell class="text-xs sm:text-sm">
                    {{ a.concern }}
                    <span v-if="a.deleted_at" class="ml-1 text-xs text-red-500"
                      >(Deleted)</span
                    >
                  </TableCell>

                  <TableCell>
                    <div class="flex space-x-1 sm:space-x-2">
                      <Button
                        v-if="a.deleted_at"
                        type="button"
                        @click="$emit('restoreArea', a.id)"
                        variant="outline"
                        size="sm"
                      >
                        <Icon name="undo" class="h-4 w-4" />
                      </Button>

                      <Button
                        v-if="!a.deleted_at"
                        type="button"
                        @click="$emit('deleteArea', a.id)"
                        variant="destructive"
                        size="sm"
                      >
                        <Icon name="trash" class="h-4 w-4" />
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
        >
          Close
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
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from "@/components/ui";

const props = defineProps<{
  open: boolean;
  areasOfConcern: any[];
  areas: any[];
  areaForm: any;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submitArea"): void;
  (e: "deleteArea", id: number): void;
  (e: "restoreArea", id: number): void;
  (e: "close"): void;
}>();

const openProxy = computed({
  get: () => props.open,
  set: (v) => emit("update:open", v),
});
</script>
