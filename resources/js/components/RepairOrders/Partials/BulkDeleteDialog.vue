<template>
  <Dialog v-model:open="openProxy">
    <DialogContent class="max-w-[95vw] sm:max-w-md">
      <DialogHeader class="px-4 sm:px-6">
        <DialogTitle class="text-lg sm:text-xl">
          Delete {{ selectedCount }} Orders?
        </DialogTitle>
        <DialogDescription class="text-xs sm:text-sm">
          This action cannot be undone.
        </DialogDescription>
      </DialogHeader>

      <DialogFooter class="px-4 sm:px-6 flex gap-2">
        <Button
          variant="outline"
          @click="openProxy = false"
          class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
        >
          Cancel
        </Button>
        <Button
          variant="destructive"
          @click="$emit('deleteBulk')"
          class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
        >
          Delete
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from "vue";
import {
  Button,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui";

const props = defineProps<{
  open: boolean;
  selectedCount: number;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "deleteBulk"): void;
}>();

const openProxy = computed({
  get: () => props.open,
  set: (v) => emit("update:open", v),
});
</script>
