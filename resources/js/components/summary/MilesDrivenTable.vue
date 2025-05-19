<template>
  <div
    class="bg-card rounded-lg border shadow-sm p-4 md:p-6 mb-6"
  >
    <!-- Header with Add button -->
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-base md:text-xl font-semibold">
        Miles Driven
      </h3>
      <Button
        @click="openModal()"
        variant="default"
      >
        <Icon
          name="plus"
          class="mr-2 h-4 w-4"
        />
        <span>Add Miles Driven</span>
      </Button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto max-h-80">
      <Table class="w-full">
        <TableHeader>
          <TableRow
            class="sticky top-0 z-10 border-b"
          >
            <TableHead
              class="cursor-pointer"
              @click="sortBy('week_start_date')"
            >
              Week Start
              <span>
                <Icon
                  v-if="sortColumn==='week_start_date'&&sortDirection==='asc'"
                  name="chevron-up"
                />
                <Icon
                  v-else-if="sortColumn==='week_start_date'&&sortDirection==='desc'"
                  name="chevron-down"
                />
                <Icon
                  v-else
                  name="chevron-up-down"
                  class="opacity-50"
                />
              </span>
            </TableHead>

            <TableHead
              class="cursor-pointer"
              @click="sortBy('week_end_date')"
            >
              Week End
              <span>
                <Icon
                  v-if="sortColumn==='week_end_date'&&sortDirection==='asc'"
                  name="chevron-up"
                />
                <Icon
                  v-else-if="sortColumn==='week_end_date'&&sortDirection==='desc'"
                  name="chevron-down"
                />
                <Icon
                  v-else
                  name="chevron-up-down"
                  class="opacity-50"
                />
              </span>
            </TableHead>

            <TableHead
              class="cursor-pointer"
              @click="sortBy('miles')"
            >
              Miles
              <span>
                <Icon
                  v-if="sortColumn==='miles'&&sortDirection==='asc'"
                  name="chevron-up"
                />
                <Icon
                  v-else-if="sortColumn==='miles'&&sortDirection==='desc'"
                  name="chevron-down"
                />
                <Icon
                  v-else
                  name="chevron-up-down"
                  class="opacity-50"
                />
              </span>
            </TableHead>

            <TableHead>Notes</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <TableRow
            v-for="item in paginatedEntries"
            :key="item.id"
          >
            <TableCell>
              {{ formatDate(item.week_start_date) }}
            </TableCell>
            <TableCell>
              {{ formatDate(item.week_end_date) }}
            </TableCell>
            <TableCell>
              {{ formatNumber(item.miles) }}
            </TableCell>
            <TableCell>
              {{ truncateText(item.notes, 30) }}
            </TableCell>
            <TableCell>
              <div class="flex space-x-2">
                <Button
                  variant="outline"
                  size="sm"
                  @click="openModal(item)"
                >
                  <Icon
                    name="pencil"
                    class="mr-1 h-4 w-4"
                  />
                  <span>Edit</span>
                </Button>
                <Button
                  variant="destructive"
                  size="sm"
                  @click="confirmDelete(item)"
                >
                  <Icon
                    name="trash"
                    class="mr-1 h-4 w-4"
                  />
                  <span>Delete</span>
                </Button>
              </div>
            </TableCell>
          </TableRow>
          <TableRow v-if="!milesEntries.length">
            <TableCell
              :colspan="5"
              class="py-4 text-center text-muted-foreground"
            >
              No miles driven records found
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div
      class="border-t mt-4 px-4 py-3 bg-muted/20"
      v-if="totalPages > 1"
    >
      <div
        class="flex flex-col sm:flex-row items-center justify-between gap-2"
      >
        <div class="text-sm text-muted-foreground">
          Showing {{ paginatedEntries.length }} of {{ milesEntries.length }} entries
        </div>
        <div class="flex flex-wrap gap-2">
          <Button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            variant="ghost"
            size="sm"
          >
            Previous
          </Button>
          <Button
            v-for="page in paginationRange"
            :key="page"
            @click="goToPage(page)"
            variant="ghost"
            size="sm"
            :class="{
              'border-primary bg-primary/10 text-primary': currentPage === page
            }"
          >
            {{ page }}
          </Button>
          <Button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            variant="ghost"
            size="sm"
          >
            Next
          </Button>
        </div>
      </div>
    </div>

    <!-- Add / Edit Modal -->
    <Dialog v-model:open="isModalOpen">
      <DialogContent
        class="sm:max-w-md w-full max-h-[90vh] overflow-auto"
      >
        <DialogHeader>
          <DialogTitle>
            {{ form.id ? 'Edit Miles Driven' : 'Add Miles Driven' }}
          </DialogTitle>
          <DialogDescription class="text-muted-foreground">
            {{ form.id
              ? 'Update this record.'
              : 'Fill in the new miles driven record.' }}
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="submitForm" class="grid gap-4 py-4">
          <!-- Year & Week Number -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="year">Year</Label>
              <Input
                id="year"
                type="number"
                v-model.number="form.year"
                min="2000"
                max="2100"
                @input="computeWeekSpan"
              />
            </div>
            <div>
              <Label for="week_number">Week #</Label>
              <Input
                id="week_number"
                type="number"
                v-model.number="form.week_number"
                min="1"
                max="53"
                @input="computeWeekSpan"
              />
            </div>
          </div>

          <!-- Computed Sunday–Saturday -->
          <div
            v-if="form.week_start_date"
            class="grid grid-cols-2 gap-4"
          >
            <div>
              <Label>Start (Sun)</Label>
              <p class="mt-1">{{ formatDate(form.week_start_date) }}</p>
            </div>
            <div>
              <Label>End (Sat)</Label>
              <p class="mt-1">{{ formatDate(form.week_end_date) }}</p>
            </div>
          </div>

          <!-- Miles -->
          <div>
            <Label for="miles">Miles</Label>
            <Input
              id="miles"
              v-model="form.miles"
              type="number"
              step="0.0001"
              min="0"
              max="999999.9999"
            />
          </div>

          <!-- Notes -->
          <div>
            <Label for="notes">Notes</Label>
            <textarea
              id="notes"
              v-model="form.notes"
              class="w-full h-24 resize-none rounded-md border border-input bg-background px-3 py-2 text-sm"
            ></textarea>
          </div>

          <DialogFooter class="mt-4">
            <Button variant="outline" type="button" @click="isModalOpen = false">   
            Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">Submit</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

  
<script setup lang="ts">
import {
  Button,
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  Input,
  Label,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableCell,
  TableHead,
} from '@/components/ui'
import Icon from '@/components/Icon.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps<{
  milesEntries: Array<any>,
  tenantSlug: string | null
}>()

// local state
const isModalOpen = ref(false)
const sortColumn = ref<'week_start_date' | 'week_end_date' | 'miles'>(
  'week_start_date'
)
const sortDirection = ref<'asc' | 'desc'>('desc')

// pagination state
const currentPage = ref(1)
const itemsPerPage = ref(10)

// form
const form = useForm({
  id: null,
  year: '',
  week_number: '',
  week_start_date: '',
  week_end_date: '',
  miles: '',
  notes: '',
})

// sorting
const sortedEntries = computed(() => {
  const data = [...props.milesEntries]
  data.sort((a, b) => {
    let av = a[sortColumn.value]
    let bv = b[sortColumn.value]
    if (av > bv) return sortDirection.value === 'asc' ? 1 : -1
    if (av < bv) return sortDirection.value === 'asc' ? -1 : 1
    return 0
  })
  return data
})

// pagination
const totalPages = computed(() => {
  return Math.ceil(sortedEntries.value.length / itemsPerPage.value)
})

const paginatedEntries = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value
  const endIndex = startIndex + itemsPerPage.value
  return sortedEntries.value.slice(startIndex, endIndex)
})

const paginationRange = computed(() => {
  const range = []
  const maxVisiblePages = 5
  
  if (totalPages.value <= maxVisiblePages) {
    // Show all pages if there are few
    for (let i = 1; i <= totalPages.value; i++) {
      range.push(i)
    }
  } else {
    // Show a subset of pages with current page in the middle when possible
    let start = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2))
    let end = Math.min(totalPages.value, start + maxVisiblePages - 1)
    
    // Adjust start if we're near the end
    if (end === totalPages.value) {
      start = Math.max(1, end - maxVisiblePages + 1)
    }
    
    for (let i = start; i <= end; i++) {
      range.push(i)
    }
  }
  
  return range
})

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

function sortBy(col) {
  if (sortColumn.value === col) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = col
    sortDirection.value = 'asc'
  }
  // Reset to first page when sorting changes
  currentPage.value = 1
}

// open modal for add or edit
function openModal(item = null) {
  form.reset()
  form.clearErrors()
  if (item) {
    form.id = item.id
    form.week_start_date = item.week_start_date
    form.week_end_date = item.week_end_date
    form.miles = item.miles
    form.notes = item.notes
    const sd = new Date(item.week_start_date);
    const tmp = new Date(Date.UTC(sd.getUTCFullYear(), sd.getUTCMonth(), sd.getUTCDate()));
    tmp.setUTCDate(tmp.getUTCDate() + 4 - (tmp.getUTCDay() || 7));
    const Y = tmp.getUTCFullYear();
    const W = Math.ceil(((tmp - Date.UTC(Y, 0, 1)) / 86400000 + 1) / 7) + 1;
    form.year = Y;
    form.week_number = W;
  }
  isModalOpen.value = true
}

// calculate Sunday–Saturday span
function computeWeekSpan() {
  const Y = +form.year
  const W = +form.week_number
  if (!Y || !W) {
    form.week_start_date = ''
    form.week_end_date = ''
    return
  }
  const d = new Date(Date.UTC(Y, 0, 4))
  const day = d.getUTCDay() || 7
  d.setUTCDate(d.getUTCDate() + (W - 1) * 7 + (1 - day))
  const sunday = new Date(d)
  sunday.setUTCDate(d.getUTCDate() -1)
  const saturday = new Date(sunday)
  saturday.setUTCDate(sunday.getUTCDate() + 6)
  form.week_start_date = sunday.toISOString().slice(0, 10)
  form.week_end_date = saturday.toISOString().slice(0, 10)
}

// submit form
function submitForm() {
  const routeName = form.id ? 'miles_driven.update' : 'miles_driven.store'
  const params = form.id
    ? { tenantSlug: props.tenantSlug, milesDriven: form.id }
    : { tenantSlug: props.tenantSlug }

  form[ form.id ? 'put' : 'post' ](
    route(routeName, params),
    {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false
      },
    }
  )
}

// delete
const toDelete = ref(null)
function confirmDelete(item) {
  toDelete.value = item
  if (confirm('Really delete this record?')) deleteRecord()
}
function deleteRecord() {
  const name = 'miles_driven.destroy'
  const params = { tenantSlug: props.tenantSlug, milesDriven: toDelete.value.id }

  form.delete(
    route(name, params),
    {
      preserveScroll: true,
      onSuccess: () => {
        // modal already closed via confirmDelete
      },
    }
  )
}

// helpers
function formatDate(s) {
  if (!s) return '';
  const d = new Date(s);
  const utc = new Date(d.getTime() + d.getTimezoneOffset() * 60000);
  return utc.toLocaleDateString();
}
function formatNumber(n: any) {
  return Number(n).toLocaleString(undefined, {
    minimumFractionDigits: 4,
    maximumFractionDigits: 4,
  })
}
function truncateText(t: string, m: number) {
  return t?.length > m ? t.slice(0, m) + '…' : t || ''
}
</script>
  