<template>
  <!-- Main layout wrapped in AppLayout with breadcrumbs and tenantSlug props -->
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="props.permissions">

    <Head title="Safety" />
    <!-- responsive here -->
    <div
      class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6">
      <!-- Success message notification -->
      <Alert v-if="successMessage" variant="success">
        <AlertTitle>Success</AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>
      <!-- Error Message -->
      <Alert v-if="errorMessage" variant="destructive">
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>{{ errorMessage }}</AlertDescription>
      </Alert>
      <!-- Actions Section -->
      <!-- responsive here -->
      <div class="flex flex-col sm:flex-row justify-between items-center px-2 mb-2 md:mb-4 lg:mb-6">
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold text-gray-800 dark:text-gray-200">
          Safety Management
        </h1>
        <div class="flex flex-wrap gap-3 ml-3">
          <!-- Create New Entry button -->
          <Button class="px-2 py-0 md:px-4 md:py-2" @click="openCreateModal" variant="default"
            v-if="permissionNames.includes('safety-data.create')">
            <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" />
            Create New Entry
          </Button>

          <!-- Delete Selected button - only shows when items are selected -->
          <Button class="px-2 py-0 md:px-4 md:py-2" v-if="
            selectedEntries.length > 0 && permissionNames.includes('safety-data.delete')
          " @click="confirmDeleteSelected()" variant="destructive">
            <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" />
            Delete Selected ({{ selectedEntries.length }})
          </Button>

          <!-- Tenant selection for SuperAdmin (only visible if SuperAdmin) -->
          <div v-if="SuperAdmin" class="flex items-center gap-2">
            <select v-model="importForm.tenant_id"
              class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
              <option disabled value="">Select Company Name</option>
              <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                {{ tenant.name }}
              </option>
            </select>
          </div>

          <!-- Date input for the import file -->
          <div class="flex items-center gap-2" v-if="permissionNames.includes('safety-data.import')">
            <Popover v-model:open="importDateOpen">
              <PopoverTrigger as-child>
                <Button variant="outline" class="justify-start text-left font-normal min-w-[220px]">
                  <CalendarIcon class="mr-2 h-4 w-4" />
                  {{
                    importDatePicker
                      ? df.format(importDatePicker.toDate(getLocalTimeZone()))
                      : 'Pick import date'
                  }}
                </Button>
              </PopoverTrigger>

              <PopoverContent class="w-auto p-0">
                <Calendar :model-value="importDatePicker" layout="month-and-year"
                  @update:model-value="handleImportDateSelect" />
              </PopoverContent>
            </Popover>
          </div>

          <!-- Import XLSX button -->
          <label class="cursor-pointer">
            <Button class="px-2 py-0 md:px-4 md:py-2" variant="secondary" as="span"
              v-if="permissionNames.includes('safety-data.import')">
              <Icon name="upload" class="mr-1 h-4 w-4 md:mr-2" />
              Upload XLSX
            </Button>
            <input type="file" class="hidden" @change="handleImport" accept=".xlsx" />
          </label>

          <!-- Export CSV button -->
          <Button class="px-2 py-0 md:px-4 md:py-2" @click.prevent="exportCSV" variant="outline"
            v-if="permissionNames.includes('safety-data.export')">
            <Icon name="download" class="mr-1 h-4 w-4 md:mr-2" />
            Download CSV
          </Button>
        </div>
      </div>

      <!-- Date Filter Tabs -->
      <Card>
        <CardContent class="p-2 md:p-4 lg:p-6">
          <div class="flex flex-col items-center md:items-start gap-2">
            <div class="flex flex-wrap gap-1 md:gap-2">
              <Button @click="selectDateFilter('yesterday')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary': activeTab === 'yesterday',
              }">
                Yesterday
              </Button>
              <Button @click="selectDateFilter('current-week')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary':
                  activeTab === 'current-week',
              }">
                WTD
              </Button>
              <Button @click="selectDateFilter('6w')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary': activeTab === '6w',
              }">
                T6W
              </Button>
              <Button @click="selectDateFilter('quarterly')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary': activeTab === 'quarterly',
              }">
                Quarterly
              </Button>
              <Button @click="selectDateFilter('custom')" variant="outline" size="sm" :class="{
                'border-primary bg-primary/10 text-primary': activeTab === 'custom',
              }">
                Custom
              </Button>
              <!-- <Button 
                @click="selectDateFilter('full')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'full'}"
              >
                Full
              </Button> -->
            </div>

            <!-- Date range info and Freeze Column toggle in separate row -->
            <div class="flex items-center justify-between">
              <div v-if="dateRange" class="text-sm text-muted-foreground">
                <span v-if="activeTab === 'yesterday' && dateRange.start">
                  Showing data from {{ formatDate(dateRange.start) }}
                </span>
                <span v-else-if="dateRange.start && dateRange.end">
                  Showing data from {{ formatDate(dateRange.start) }} to
                  {{ formatDate(dateRange.end) }}
                </span>
                <span v-else>
                  {{ dateRange.label }}
                </span>
                <span v-if="weekNumberText" class="ml-1">({{ weekNumberText }})</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <div v-if="props.entries.data.length === 0"
        class="flex flex-col items-center justify-center rounded-lg border bg-muted/20 py-16">
        <Icon name="database-x" class="mb-4 h-16 w-16 text-muted-foreground" />
        <h2 class="text-center text-2xl font-bold text-muted-foreground">
          There is No Data to give Information about.
        </h2>
      </div>
      <div v-else>
        <SafetySummary v-if="!SuperAdmin" :data="safetyDataWithLabel" :activeTab="activeTab" />

        <!-- Data Table Section -->
        <!-- Toggle Freeze Column Button -->
        <Button @click="toggleFreezeColumn" variant="outline" size="sm" class="my-2 md:my-4"
          :class="{ 'border-primary bg-primary/10 text-primary': freezeColumns }">
          <Icon :name="freezeColumns ? 'lock' : 'unlock'" class="mr-2 h-4 w-4" />
          {{ freezeColumns ? "Unfreeze Names" : "Freeze Names" }}
        </Button>
        <Card class="mx-auto max-w-[95vw] md:max-w-[64vw] lg:max-w-full overflow-x-auto">
          <CardContent class="p-0">
            <div class="safety-table-wrapper overflow-x-auto border-t border-border bg-background dark:bg-background">
              <Table class="relative h-[500px] overflow-auto">
                <TableHeader>
                  <TableRow class="border-b bg-background hover:bg-background">
                    <!-- Checkbox column for selecting all -->
                    <TableHead v-if="permissionNames.includes('safety-data.delete')" class="w-[50px]"
                      :ref="(el) => setStickyHeaderRef('select', el)" :style="getStickyHeaderStyle('select')">
                      <div class="flex items-center justify-center">
                        <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                          class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                      </div>
                    </TableHead>
                    <!-- If SuperAdmin, show Tenant column -->
                    <TableHead v-if="SuperAdmin" :ref="(el) => setStickyHeaderRef('company', el)"
                      :style="getStickyHeaderStyle('company')">
                      Company Name
                    </TableHead>
                    <!-- Dynamically render table columns from the tableColumns array -->
                    <!-- here where we filter out the user_name, group, and group_hierarchy columns and the impact columns -->
                    <TableHead v-for="col in visibleTableColumns" :key="col" class="whitespace-nowrap"
                      :ref="stickyColumnOrder.includes(col) ? (el) => setStickyHeaderRef(col, el) : undefined"
                      :style="stickyColumnOrder.includes(col) ? getStickyHeaderStyle(col) : {}">
                      {{
                        col.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase())
                      }}
                    </TableHead>
                    <!-- Actions column - removed freezing -->
                    <TableHead v-if="
                      permissionNames.includes('safety-data.update') ||
                      permissionNames.includes('safety-data.delete')
                    ">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-if="entries.data.length === 0">
                    <TableCell :colspan="SuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2
                      " class="py-8 text-center">
                      No entries found
                    </TableCell>
                  </TableRow>
                  <TableRow v-for="item in entries.data" :key="item.id" class="hover:bg-muted/50">
                    <!-- Checkbox for selecting individual row -->
                    <TableCell v-if="permissionNames.includes('safety-data.delete')" class="text-center"
                      :style="getStickyBodyStyle('select')">
                      <input type="checkbox" :value="item.id" v-model="selectedEntries"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                    </TableCell>
                    <!-- Display Tenant name for SuperAdmin -->
                    <TableCell v-if="SuperAdmin" :style="getStickyBodyStyle('company')">
                      {{ item.tenant?.name ?? "—" }}
                    </TableCell>
                    <!-- Render each field for the entry -->
                    <TableCell v-for="col in visibleTableColumns" :key="col" class="whitespace-nowrap"
                      :style="stickyColumnOrder.includes(col) ? getStickyBodyStyle(col) : {}">
                      {{
                        typeof item[col] === "string" &&
                          /^\d{4}-\d{2}-\d{2}/.test(item[col])
                          ? formatDate(item[col])
                          : typeof item[col] === "string" && !isNaN(parseFloat(item[col]))
                            ? Math.round(parseFloat(item[col]))
                            : typeof item[col] === "number"
                              ? Math.round(item[col])
                              : item[col]
                      }}
                    </TableCell>
                    <!-- Actions for each entry - removed freezing -->
                    <TableCell v-if="
                      permissionNames.includes('safety-data.delete') ||
                      permissionNames.includes('safety-data.update')
                    ">
                      <div class="flex space-x-2">
                        <Button @click="openEditModal(item)" variant="warning" size="sm"
                          v-if="permissionNames.includes('safety-data.update')">
                          <Icon name="pencil" class="mr-1 h-4 w-4" />
                          Edit
                        </Button>
                        <Button @click="confirmDelete(item.id)" variant="destructive" size="sm"
                          v-if="permissionNames.includes('safety-data.delete')">
                          <Icon name="trash" class="mr-1 h-4 w-4" />
                          Delete
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <!-- Pagination -->
            <div class="border-t bg-muted/20 px-4 py-3" v-if="entries.links">
              <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
                <div class="text-sm text-muted-foreground">
                  Showing {{ entries.data.length }} entries
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 w-full sm:w-auto">
                  <div class="flex items-center gap-2">
                    <Label for="perPage" class="text-sm">Per page:</Label>
                    <select id="perPage" v-model="perPage" @change="changePerPage"
                      class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                      <option value="100">100</option>
                    </select>
                  </div>
                  <div class="flex flex-wrap">
                    <Button v-for="link in entries.links" :key="link.label" @click="visitPage(link.url)"
                      :disabled="!link.url" variant="ghost" size="sm" class="mx-1" :class="{
                        'border-primary bg-primary/10 text-primary': link.active,
                      }">
                      <span v-html="link.label"></span>
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Delete Selected Entries Confirmation Dialog -->
        <Dialog v-model:open="showDeleteSelectedModal">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Confirm Bulk Deletion</DialogTitle>
              <DialogDescription>
                Are you sure you want to delete {{ selectedEntries.length }} safety
                records? This action cannot be undone.
              </DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-4">
              <Button type="button" @click="showDeleteSelectedModal = false" variant="outline">
                Cancel
              </Button>
              <Button type="button" @click="deleteSelectedEntries()" variant="destructive">
                Delete Selected
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
        <!-- Hidden form for CSV export -->
        <form ref="exportForm" method="GET" class="hidden" />
      </div>
    </div>
    <!-- Delete Single Entry Confirmation Dialog -->
    <Dialog v-model:open="showDeleteModal">
      <DialogContent class="max-w-[95vw] sm:max-w-md">
        <DialogHeader class="px-4 sm:px-6">
          <DialogTitle class="text-lg sm:text-xl">Confirm Deletion</DialogTitle>
          <DialogDescription class="text-xs sm:text-sm">
            Are you sure you want to delete this safety record? This action cannot be
            undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="px-4 sm:px-6">
          <Button type="button" @click="showDeleteModal = false" variant="outline"
            class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm">
            Cancel
          </Button>
          <Button type="button" @click="deleteEntry(entryToDelete)" variant="destructive"
            class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm">
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Modal for creating/editing an entry -->
    <Dialog v-model:open="showModal">
      <DialogContent class="w-[90vw] sm:max-w-lg md:max-w-2xl lg:max-w-4xl">
        <DialogHeader>
          <DialogTitle>{{ formTitle }}</DialogTitle>
          <DialogDescription>
            Fill in the details to {{ formAction.toLowerCase() }} an entry.
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="submitForm" class="grid max-h-[70vh] gap-6 overflow-y-auto p-1">
          <!-- Tenant dropdown for SuperAdmin users -->
          <div v-if="SuperAdmin" class="col-span-full">
            <Label for="tenant">Company Name</Label>
            <div class="relative">
              <select id="tenant" v-model="form.tenant_id"
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                <option disabled value="">Select Company</option>
                <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                  {{ tenant.name }}
                </option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                  fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Date field (important, so keep it separate at the top) -->
          <div class="col-span-full">
            <Label for="date" class="font-medium">Date</Label>

            <Popover v-model:open="formDateOpen">
              <PopoverTrigger as-child>
                <Button id="date" variant="outline" class="w-full justify-start text-left font-normal">
                  <CalendarIcon class="mr-2 h-4 w-4" />
                  {{
                    formDatePicker
                      ? df.format(formDatePicker.toDate(getLocalTimeZone()))
                      : 'Pick a date'
                  }}
                </Button>
              </PopoverTrigger>

              <PopoverContent class="w-auto p-0">
                <Calendar :model-value="formDatePicker" layout="month-and-year"
                  @update:model-value="handleFormDateSelect" />
              </PopoverContent>
            </Popover>
          </div>

          <!-- Group fields into sections for better organization -->
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
            <!-- Driver Information Section -->
            <div class="col-span-full mb-2">
              <h3 class="text-md border-b pb-1 font-semibold">Driver Information</h3>
            </div>

            <div v-for="col in [
              'driver_name',
              'user_name',
              'group',
              'group_hierarchy',
              'vehicle_type',
            ]" :key="col" class="space-y-1">
              <Label :for="col" class="capitalize">{{ col.replace(/_/g, " ") }}</Label>
              <Input :id="col" v-model="form[col]" :type="getInputType(col)" :step="getStep(col)" :min="getMin(col)" />
            </div>

            <!-- Performance Metrics Section -->
            <div class="col-span-full mb-2 mt-4">
              <h3 class="text-md border-b pb-1 font-semibold">Performance Metrics</h3>
            </div>

            <div v-for="col in [
              'minutes_analyzed',
              'green_minutes_percent',
              'overspeeding_percent',
              'driver_score',
              'safety_normalisation_factor',
            ]" :key="col" class="space-y-1">
              <Label :for="col" class="capitalize">{{ col.replace(/_/g, " ") }}</Label>
              <Input :id="col" v-model="form[col]" :type="getInputType(col)" :step="getStep(col)" :min="getMin(col)" />
            </div>

            <!-- Events Section -->
            <div class="col-span-full mb-2 mt-4">
              <h3 class="text-md border-b pb-1 font-semibold">Events & Violations</h3>
            </div>

            <!-- Remaining fields in a grid -->
            <div v-for="col in formColumns.filter(
              (c) =>
                ![
                  'driver_name',
                  'user_name',
                  'group',
                  'group_hierarchy',
                  'vehicle_type',
                  'minutes_analyzed',
                  'green_minutes_percent',
                  'overspeeding_percent',
                  'driver_score',
                  'safety_normalisation_factor',
                  'date',
                ].includes(c)
            )" :key="col" class="space-y-1">
              <Label :for="col" class="text-sm capitalize">{{
                col.replace(/_/g, " ")
                }}</Label>
              <Input :id="col" v-model="form[col]" :type="getInputType(col)" :step="getStep(col)" :min="getMin(col)" />
            </div>
          </div>

          <DialogFooter
            class="mt-6 flex-col space-y-2 border-t pt-4 sm:flex-row sm:justify-end sm:space-x-2 sm:space-y-0">
            <Button type="button" @click="closeModal" variant="outline" class="w-full sm:w-auto">
              Cancel
            </Button>
            <Button type="submit" variant="default" class="w-full sm:w-auto">
              {{ formAction }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
    <Dialog v-model:open="showCustomDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Select Custom Date Range</DialogTitle>
          <DialogDescription>
            Choose a start and end date.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4">
          <div>
            <Label>Start Date</Label>

            <Popover v-model:open="startDateOpen">
              <PopoverTrigger as-child>
                <Button variant="outline" class="w-full justify-start text-left font-normal">
                  <CalendarIcon class="mr-2 h-4 w-4" />
                  {{
                    startDatePicker
                      ? df.format(startDatePicker.toDate(getLocalTimeZone()))
                      : 'Pick a start date'
                  }}
                </Button>
              </PopoverTrigger>

              <PopoverContent class="w-auto p-0">
                <Calendar :model-value="startDatePicker" @update:model-value="handleStartDateSelect" />
              </PopoverContent>
            </Popover>
          </div>

          <div>
            <Label>End Date</Label>

            <Popover v-model:open="endDateOpen">
              <PopoverTrigger as-child>
                <Button variant="outline" class="w-full justify-start text-left font-normal">
                  <CalendarIcon class="mr-2 h-4 w-4" />
                  {{
                    endDatePicker
                      ? df.format(endDatePicker.toDate(getLocalTimeZone()))
                      : 'Pick an end date'
                  }}
                </Button>
              </PopoverTrigger>

              <PopoverContent class="w-auto p-0">
                <Calendar :model-value="endDatePicker" @update:model-value="handleEndDateSelect" />
              </PopoverContent>
            </Popover>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showCustomDialog = false">
            Cancel
          </Button>
          <Button @click="applyCustomRange">
            Apply
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup>
import Icon from "@/components/Icon.vue";
import {
  Alert,
  AlertDescription,
  AlertTitle,
  Button,
  Card,
  CardContent,
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  Input,
  Label,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui";
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref, watch, nextTick, onMounted, onBeforeUnmount } from "vue";
import SafetySummary from "./SafetySummary.vue";
import { CalendarIcon } from "lucide-vue-next";
import { DateFormatter, getLocalTimeZone, parseDate } from "@internationalized/date";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
// Define props passed from the backend via Inertia
const props = defineProps({
  entries: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  tenantSlug: String,
  SuperAdmin: Boolean,
  tenants: Array,
  dateRange: Object,
  dateFilter: {
    type: String,
    default: "yesterday",
  },
  safetyData: {
    type: Object,
    default: () => ({}),
  },
  weekNumber: {
    type: Number,
    default: null,
  },
  startWeekNumber: {
    type: Number,
    default: null,
  },
  endWeekNumber: {
    type: Number,
    default: null,
  },
  year: {
    type: Number,
    default: null,
  },
  permissions: Array,
});
// Reactive state variables
const errorMessage = ref("");
const successMessage = ref("");
const showModal = ref(false);
const formTitle = ref("Create Entry");
const formAction = ref("Create");
const exportForm = ref(null);
const activeTab = ref(props.dateFilter || "full");
const perPage = ref(10);
const selectedEntries = ref([]);
const showDeleteSelectedModal = ref(false);
const freezeColumns = ref(false); // Changed to false by default
const showDeleteModal = ref(false);
const entryToDelete = ref(null);
const startDatePicker = ref(null);
const endDatePicker = ref(null);
const startDateOpen = ref(false);
const endDateOpen = ref(false);

const df = new DateFormatter("en-US", { dateStyle: "medium" });
// Define breadcrumbs for the layout
const breadcrumbs = [
  {
    title: props.tenantSlug ? "Dashboard" : "Admin Dashboard",
    href: props.tenantSlug
      ? route("dashboard", { tenantSlug: props.tenantSlug })
      : route("admin.dashboard"),
  },
  {
    title: "Safety",
    href: props.tenantSlug
      ? route("safety.index", { tenantSlug: props.tenantSlug })
      : route("safety.index.admin"),
  },
];
const permissionNames = computed(() => props.permissions.map((p) => p.name));

const customStartDate = ref(null);
const customEndDate = ref(null);
const showCustomDialog = ref(false);
// Toggle column freezing function
function toggleFreezeColumn() {
  freezeColumns.value = !freezeColumns.value;
}
const importDatePicker = ref(null);
const formDatePicker = ref(null);
const importDateOpen = ref(false);
const formDateOpen = ref(false);
const weekNumberText = computed(() => {
  // For yesterday and current-week, show single week
  if (
    (activeTab.value === "yesterday" || activeTab.value === "current-week") &&
    props.weekNumber &&
    props.year
  ) {
    return `Week ${props.weekNumber}, ${props.year}`;
  }

  // For 6w and quarterly, show start-end week range if available
  if (
    (activeTab.value === "6w" || activeTab.value === "quarterly") &&
    props.startWeekNumber &&
    props.endWeekNumber &&
    props.year
  ) {
    return `Weeks ${props.startWeekNumber}-${props.endWeekNumber}, ${props.year}`;
  }

  return "";
});
const handleStartDateSelect = (val) => {
  startDatePicker.value = val ?? null;
  customStartDate.value = val
    ? val.toDate(getLocalTimeZone()).toISOString().split("T")[0]
    : null;

  startDateOpen.value = false;
};
const handleImportDateSelect = (val) => {
  importDatePicker.value = val ?? null;
  importForm.date = val
    ? val.toDate(getLocalTimeZone()).toISOString().split("T")[0]
    : "";
  importDateOpen.value = false;
};

const handleFormDateSelect = (val) => {
  formDatePicker.value = val ?? null;
  form.date = val
    ? val.toDate(getLocalTimeZone()).toISOString().split("T")[0]
    : "";
  formDateOpen.value = false;
};
const handleEndDateSelect = (val) => {
  endDatePicker.value = val ?? null;
  customEndDate.value = val
    ? val.toDate(getLocalTimeZone()).toISOString().split("T")[0]
    : null;

  endDateOpen.value = false;
};
// Field definitions based on your migration
const fieldTypes = {
  driver_name: "text",
  user_name: "text",
  group: "text",
  group_hierarchy: "text",
  minutes_analyzed: "decimal",
  green_minutes_percent: "decimal",
  overspeeding_percent: "decimal",
  driver_score: "decimal",
  total_events_avg_fd_impact: "decimal",
  average_following_distance_sec: "decimal",
  average_following_distance_gz_impact: "decimal",
  total_events: "decimal",
  high_g: "decimal",
  low_impact: "decimal",
  driver_initiated: "decimal",
  potential_collision: "decimal",
  sign_violations: "decimal",
  sign_violations_gz_impact: "decimal",
  traffic_light_violation: "decimal",
  traffic_light_violation_gz_impact: "decimal",
  u_turn: "decimal",
  u_turn_gz_impact: "decimal",
  hard_braking: "decimal",
  hard_braking_gz_impact: "decimal",
  hard_turn: "decimal",
  hard_turn_gz_impact: "decimal",
  hard_acceleration: "decimal",
  hard_acceleration_gz_impact: "decimal",
  driver_distraction: "decimal",
  driver_distraction_gz_impact: "decimal",
  following_distance: "decimal",
  following_distance_gz_impact: "decimal",
  speeding_violations: "decimal",
  speeding_violations_gz_impact: "decimal",
  seatbelt_compliance: "decimal",
  camera_obstruction: "decimal",
  driver_drowsiness: "decimal",
  weaving: "decimal",
  weaving_gz_impact: "decimal",
  swerve: "decimal",
  collision_warning: "decimal",
  collision_warning_gz_impact: "decimal",
  requested_video: "decimal",
  backing: "decimal",
  roadside_parking: "decimal",
  lane_conduct: "decimal",
  driver_distracted_hard_brake: "decimal",
  following_distance_hard_brake: "decimal",
  driver_distracted_following_distance: "decimal",
  driver_star: "decimal",
  driver_star_gz_impact: "decimal",
  vehicle_type: "text",
  safety_normalisation_factor: "decimal",
  date: "date",
};

// Derive formColumns and tableColumns from fieldTypes keys.
const formColumns = Object.keys(fieldTypes);
const tableColumns = [...formColumns];
const stickyColumnOrder = ["select", "company", "driver_name"];
const visibleTableColumns = computed(() =>
  tableColumns.filter(
    (col) =>
      ![
        "user_name",
        "group",
        "group_hierarchy",
        "requested_video",
        "safety_normalisation_factor",
      ].includes(col) && !col.toLowerCase().includes("impact")
  )
);
const stickyColumnVisibility = computed(() => ({
  select: permissionNames.value.includes("safety-data.delete"),
  company: props.SuperAdmin,
  driver_name: visibleTableColumns.value.includes("driver_name"),
}));
const activeStickyColumns = computed(() =>
  stickyColumnOrder.filter((key) => stickyColumnVisibility.value[key])
);
const stickyHeaderRefs = ref({});
const stickyOffsets = ref({});
let stickyResizeObserver = null;

function setStickyHeaderRef(key, el) {
  const target = el?.$el ?? el;

  if (target instanceof Element) {
    stickyHeaderRefs.value[key] = target;
  } else {
    delete stickyHeaderRefs.value[key];
  }
}
function calculateStickyOffsets() {
  const offsets = {};
  let currentLeft = 0;

  for (const key of activeStickyColumns.value) {
    offsets[key] = currentLeft;

    const el = stickyHeaderRefs.value[key];
    const width = el instanceof Element ? el.getBoundingClientRect().width : 0;

    currentLeft += width;
  }

  stickyOffsets.value = offsets;
}
function getStickyHeaderStyle(key) {
  if (!freezeColumns.value) return {};
  if (!activeStickyColumns.value.includes(key)) return {};

  return {
    left: `${stickyOffsets.value[key] ?? 0}px`,
    zIndex: 40,
    backgroundColor: "hsl(var(--background))",
  };
}

function getStickyBodyStyle(key) {
  if (!freezeColumns.value) return {};
  if (!activeStickyColumns.value.includes(key)) return {};

  return {
    position: "sticky",
    left: `${stickyOffsets.value[key] ?? 0}px`,
    zIndex: 20,
    backgroundColor: "hsl(var(--background))",
  };
}
function setupStickyResizeObserver() {
  if (stickyResizeObserver) {
    stickyResizeObserver.disconnect();
  }

  stickyResizeObserver = new ResizeObserver(() => {
    calculateStickyOffsets();
  });

  Object.values(stickyHeaderRefs.value).forEach((el) => {
    if (el instanceof Element) {
      stickyResizeObserver.observe(el);
    }
  });
}

async function refreshStickyLayout() {
  await nextTick();
  calculateStickyOffsets();
  setupStickyResizeObserver();
}
watch(
  [
    freezeColumns,
    activeStickyColumns,
    visibleTableColumns,
    () => props.entries.data.length,
  ],
  () => {
    refreshStickyLayout();
  },
  { immediate: true, deep: true }
);
onMounted(() => {
  refreshStickyLayout();
  window.addEventListener("resize", calculateStickyOffsets);
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", calculateStickyOffsets);

  if (stickyResizeObserver) {
    stickyResizeObserver.disconnect();
  }
});
function isStickyColumn(key) {
  return activeStickyColumns.value.includes(key);
}

function getColumnStickyKey(col) {
  return stickyColumnOrder.includes(col) ? col : null;
}
// Initialize form state using Inertia's useForm helper.
const form = useForm({
  tenant_id: null,
  ...Object.fromEntries(formColumns.map((col) => [col, ""])),
  id: null,
});

// Initialize import form state.
const importForm = useForm({
  csv_file: null,
  date: "",
  tenant_id: null,
});

// Initialize delete form state.
const deleteForm = useForm({});

// Helper functions to determine input type, step, and minimum value.
function getInputType(field) {
  const type = fieldTypes[field];
  if (type === "decimal" || type === "integer") return "number";
  if (type === "date") return "date";
  return "text";
}

function getStep(field) {
  const type = fieldTypes[field];
  if (type === "decimal") return "0.01";
  if (type === "integer") return "1";
  return null;
}

function getMin(field) {
  const type = fieldTypes[field];
  return type === "decimal" || type === "integer" ? "-99999" : null;
}

// Open the modal to create a new entry.
function openCreateModal() {
  form.reset();
  formTitle.value = "Create Entry";
  formDatePicker.value = form.date ? parseDate(form.date) : null;

  formAction.value = "Create";
  showModal.value = true;
}

// Open the modal to edit an existing entry.
function openEditModal(item) {
  formTitle.value = "Edit Entry";
  formAction.value = "Update";
  form.tenant_id = props.SuperAdmin ? item.tenant_id : null;
  form.id = item.id;
  formDatePicker.value = form.date ? parseDate(form.date) : null;
  formColumns.forEach((col) => {
    form[col] = item[col];
  });
  showModal.value = true;
}

// Close the modal.
function closeModal() {
  showModal.value = false;
  formDateOpen.value = false;
}

// Submit the form data to create or update an entry.
function submitForm() {
  const isCreate = formAction.value === "Create";
  const routeName = isCreate
    ? props.SuperAdmin
      ? route("safety.store.admin")
      : route("safety.store", props.tenantSlug)
    : props.SuperAdmin
      ? route("safety.update.admin", [form.id])
      : route("safety.update", [props.tenantSlug, form.id]);
  const method = isCreate ? "post" : "put";
  form[method](routeName, {
    onSuccess: () => {
      successMessage.value = isCreate ? "Entry created." : "Entry updated.";
      closeModal();
    },
    onError: () => alert("Something went wrong."),
  });
}

// Delete an entry after confirmation.
function confirmDelete(id) {
  entryToDelete.value = id;
  showDeleteModal.value = true;
}

function deleteEntry(id) {
  const routeName = props.SuperAdmin
    ? route("safety.destroy.admin", [id])
    : route("safety.destroy", [props.tenantSlug, id]);
  deleteForm.delete(routeName, {
    onSuccess: () => {
      successMessage.value = "Entry deleted.";
      showDeleteModal.value = false;
    },
  });
}
const applyCustomRange = () => {
  if (!customStartDate.value || !customEndDate.value) return;

  activeTab.value = 'custom';
  showCustomDialog.value = false;

  const routeName = props.tenantSlug
    ? route("safety.index", { tenantSlug: props.tenantSlug })
    : route("safety.index.admin");

  router.get(
    routeName,
    {
      dateFilter: 'custom',
      perPage: perPage.value,
      startDate: customStartDate.value,
      endDate: customEndDate.value,
    },
    { preserveState: true }
  );
};
// Handle file import for XLSX file.
function handleImport(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  if (!importForm.date) {
    alert("Please select the date for this import.");
    return;
  }
  if (props.SuperAdmin && !importForm.tenant_id) {
    alert("Please select a tenant for import.");
    return;
  }
  importForm.csv_file = file;
  const routeName = props.SuperAdmin
    ? route("safety.import.admin")
    : route("safety.import", props.tenantSlug);
  importForm.post(routeName, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = "Data imported successfully.";
      importForm.reset();
      importDatePicker.value = null;
    },
    onError: () => alert("Import failed."),
  });
}

// Export CSV by submitting a hidden form.
function exportCSV() {
  if (props.entries.data.length === 0) {
    errorMessage.value = "No data available to export";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
    return;
  }
  const routeName = props.SuperAdmin
    ? route("safety.export.admin")
    : route("safety.export", props.tenantSlug);
  exportForm.value?.setAttribute("action", routeName);
  exportForm.value?.submit();
}

// Remove this duplicate visitPage function
// const visitPage = (url) => {
//   if (url) {
//     router.get(url, {}, {only: ['entries'] });
//   }
// };

// Auto-hide success message after 5 seconds
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = "";
    }, 5000);
  }
});

// Function to handle date filter selection
function selectDateFilter(filter) {
  activeTab.value = filter;
  if (filter === 'custom') {
    showCustomDialog.value = true;
    return;
  }
  const routeName = props.tenantSlug
    ? route("safety.index", { tenantSlug: props.tenantSlug })
    : route("safety.index.admin");

  router.get(
    routeName,
    {
      dateFilter: filter,
      perPage: perPage.value,
    },
    { preserveState: true }
  );
}

// Function to handle per page change
function changePerPage() {
  const routeName = props.tenantSlug
    ? route("safety.index", { tenantSlug: props.tenantSlug })
    : route("safety.index.admin");

  router.get(
    routeName,
    {
      dateFilter: activeTab.value,
      perPage: perPage.value,
    },
    { preserveState: true }
  );
}

// Format date string helper function
function formatDate(dateStr) {
  if (!dateStr) return "";
  const parts = dateStr.split("-");
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
}

// Update visitPage to preserve perPage and dateFilter
function visitPage(url) {
  if (url) {
    // Add perPage and dateFilter parameters to the URL
    const urlObj = new URL(url);
    urlObj.searchParams.set("perPage", perPage.value);
    urlObj.searchParams.set("dateFilter", activeTab.value);

    router.get(urlObj.href, {}, { only: ["entries"] });
  }
}

// Computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
  return (
    props.entries.data.length > 0 &&
    selectedEntries.value.length === props.entries.data.length
  );
});

// Bulk selection functions
function toggleSelectAll(event) {
  if (event.target.checked) {
    selectedEntries.value = props.entries.data.map((entry) => entry.id);
  } else {
    selectedEntries.value = [];
  }
}

function confirmDeleteSelected() {
  if (selectedEntries.value.length > 0) {
    showDeleteSelectedModal.value = true;
  }
}

// Create a computed property that adds the dateRange label to the safetyData
const safetyDataWithLabel = computed(() => {
  if (!props.safetyData) return {};

  return {
    ...props.safetyData,
    dateRangeLabel: props.dateRange?.label || activeTab.value,
  };
});

function deleteSelectedEntries() {
  const form = useForm({
    ids: selectedEntries.value,
  });

  const routeName = props.SuperAdmin ? "safety.destroyBulk.admin" : "safety.destroyBulk";
  const routeParams = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };

  form.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = `${selectedEntries.value.length} safety records deleted successfully.`;
      selectedEntries.value = [];
      showDeleteSelectedModal.value = false;
    },
    onError: (errors) => {
      console.error(errors);
    },
  });
}


</script>

<style scoped>
:deep(.safety-table-wrapper) {
  position: relative;
  background-color: hsl(var(--background));
  min-height: 500px;
}

:deep(.safety-table-wrapper table) {
  border-collapse: separate;
  border-spacing: 0;
  width: 100%;
  background-color: hsl(var(--background));
}

:deep(.safety-table-wrapper th),
:deep(.safety-table-wrapper td) {
  border-right: 1px solid hsl(var(--border));
  background-color: hsl(var(--background));
}

:deep(.safety-table-wrapper th:last-child),
:deep(.safety-table-wrapper td:last-child) {
  border-right: none;
}

/* header cells own the vertical sticky behavior */
:deep(.safety-table-wrapper thead th) {
  position: sticky;
  top: 0;
  z-index: 30;
  background-color: hsl(var(--background));
  border-bottom: 2px solid hsl(var(--border));
}

/* cells that are also horizontally frozen */
:deep(.safety-table-wrapper th[style*="left:"]),
:deep(.safety-table-wrapper td[style*="left:"]) {
  box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.1);
  background-color: hsl(var(--background)) !important;
  background-clip: padding-box;
  isolation: isolate;
  border-right: 2px solid hsl(var(--border));
}

/* frozen header cells above everything else */
:deep(.safety-table-wrapper thead th[style*="left:"]) {
  z-index: 40 !important;
}

/* frozen body cells below header but above normal cells */
:deep(.safety-table-wrapper tbody td[style*="left:"]) {
  z-index: 20 !important;
}

:deep(.safety-table-wrapper tbody tr:hover td) {
  background-color: hsl(var(--muted)) !important;
}

/* keep frozen body cells matching hover */
:deep(.safety-table-wrapper tbody tr:hover td[style*="left:"]) {
  background-color: hsl(var(--muted)) !important;
}

:deep(.safety-table-wrapper .h-[500px]) {
  overflow: auto;
  position: relative;
  min-height: 500px;
  background-color: hsl(var(--background));
}

:deep(.safety-table-wrapper tbody tr:last-child td) {
  border-bottom: 1px solid hsl(var(--border));
}

:deep(.safety-table-wrapper tbody),
:deep(.safety-table-wrapper tr),
:deep(.safety-table-wrapper tr td),
:deep(.safety-table-wrapper tr th) {
  background-color: hsl(var(--background));
}

:deep(.safety-table-wrapper tr td),
:deep(.safety-table-wrapper tr th) {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}
</style>