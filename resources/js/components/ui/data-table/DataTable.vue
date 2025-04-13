<script setup lang="ts">
import { computed, onMounted, watch } from 'vue';
import {
  useVueTable,
  getCoreRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  type ColumnDef,
  type ColumnFiltersState,
  type SortingState,
  type VisibilityState,
  type Table as TableType,
  createColumnHelper,
  renderComponent,
} from '@tanstack/vue-table';

import { ref } from 'vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps<{
  columns: ColumnDef<any, any>[];
  data: any[];
  options?: Partial<TableOptions<any>>;
  selectable?: boolean;
  selectedIds?: any[];
}>();

const emit = defineEmits(['selectionChange', 'toggleSelectAll']);

const sorting = ref<SortingState>([]);
const selectedRows = ref<any[]>(props.selectedIds || []);

// Watch for changes in the selectedIds prop
watch(() => props.selectedIds, (newSelectedIds) => {
  if (newSelectedIds) {
    selectedRows.value = [...newSelectedIds];
  }
}, { immediate: true });

// Computed property to check if all rows are selected
const allSelected = computed(() => {
  return props.data.length > 0 && props.data.every(row => selectedRows.value.includes(row.id));
});

// Function to toggle select all rows
function toggleSelectAll(event: Event) {
  const checked = (event.target as HTMLInputElement).checked;
  if (checked) {
    selectedRows.value = props.data.map(row => row.id);
  } else {
    selectedRows.value = [];
  }
  emit('selectionChange', selectedRows.value);
}

// Function to toggle selection of a single row
function toggleRowSelection(rowId: any) {
  const index = selectedRows.value.indexOf(rowId);
  if (index === -1) {
    selectedRows.value.push(rowId);
  } else {
    selectedRows.value.splice(index, 1);
  }
  emit('selectionChange', selectedRows.value);
}

const table = useVueTable({
  get data() {
    return props.data;
  },
  get columns() {
    return props.columns;
  },
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  onSortingChange: (updaterOrValue) => {
    sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
  },
  state: {
    get sorting() {
      return sorting.value;
    },
  },
  ...props.options,
});

// Helper function to render cell content
function renderCell(column, context) {
  if (typeof column.cell === 'function') {
    return column.cell(context);
  }
  return context.getValue();
}
</script>

<template>
  <div>
    <div class="rounded-md border border-border/50">
      <Table class="relative h-[500px] overflow-auto">
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="sticky top-0 bg-background border-b z-10">
            <!-- Add checkbox column for selecting all if selectable prop is true -->
            <TableHead v-if="selectable" class="w-[50px]">
              <div class="flex items-center justify-center">
                <input 
                  type="checkbox" 
                  @change="toggleSelectAll" 
                  :checked="allSelected"
                  class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                />
              </div>
            </TableHead>
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <div
                v-if="!header.isPlaceholder"
                class="flex items-center space-x-2"
                :class="{ 'cursor-pointer select-none': header.column.getCanSort() }"
                @click="header.column.getToggleSortingHandler()?.()"
              >
                {{ header.column.columnDef.header }}
                <span v-if="header.column.getCanSort()">
                  <template v-if="header.column.getIsSorted() === 'asc'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-narrow-wide"><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/><path d="M11 12h4"/><path d="M11 16h7"/><path d="M11 20h10"/></svg>
                  </template>
                  <template v-else-if="header.column.getIsSorted() === 'desc'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-narrow-wide"><path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="M11 4h4"/><path d="M11 8h7"/><path d="M11 12h10"/></svg>
                  </template>
                  <template v-else>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-down"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg>
                  </template>
                </span>
              </div>
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
            >
              <!-- Add checkbox for selecting individual row if selectable prop is true -->
              <TableCell v-if="selectable" class="text-center">
                <input 
                  type="checkbox" 
                  :value="row.original.id" 
                  @change="toggleRowSelection(row.original.id)"
                  :checked="selectedRows.includes(row.original.id)"
                  class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                />
              </TableCell>
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <template v-if="cell.column.columnDef.id === 'actions'">
                  <slot name="cell-actions" :cell="renderCell(cell.column.columnDef, cell.getContext())"></slot>
                </template>
                <template v-else>
                  {{ renderCell(cell.column.columnDef, cell.getContext()) }}
                </template>
              </TableCell>
            </TableRow>
          </template>
          <template v-else>
            <TableRow>
              <TableCell :colspan="selectable ? props.columns.length + 1 : props.columns.length" class="h-24 text-center">
                No results.
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </div>
  </div>
</template>