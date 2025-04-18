<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
      <Head title="Miles Driven" />
      <div class="max-w-7xl mx-auto p-6 space-y-8">
        <!-- Success Message -->
        <Alert v-if="successMessage" variant="success">
          <AlertTitle>Success</AlertTitle>
          <AlertDescription>{{ successMessage }}</AlertDescription>
        </Alert>
  
        <!-- Actions Section -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
          <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Miles Driven</h1>
          <div class="flex flex-wrap gap-3">
            <Button @click="openModal()" variant="default">
              <Icon name="plus" class="mr-2 h-4 w-4" />
              Add Miles Driven
            </Button>
          </div>
        </div>

        <!-- Date Filter Tabs -->
        <Card>
          <CardContent class="p-4">
            <div class="flex flex-col gap-2">
              <div class="flex flex-wrap gap-2">
                <Button 
                  @click="selectDateFilter('yesterday')" 
                  variant="outline"
                  size="sm"
                  :class="{'bg-primary/10 text-primary border-primary': activeTab === 'yesterday'}"
                >
                  Yesterday
                </Button>
                <Button 
                  @click="selectDateFilter('current-week')" 
                  variant="outline"
                  size="sm"
                  :class="{'bg-primary/10 text-primary border-primary': activeTab === 'current-week'}"
                >
                  Current Week
                </Button>
                <Button 
                  @click="selectDateFilter('6w')" 
                  variant="outline"
                  size="sm"
                  :class="{'bg-primary/10 text-primary border-primary': activeTab === '6w'}"
                >
                  6 Weeks
                </Button>
                <Button 
                  @click="selectDateFilter('quarterly')" 
                  variant="outline"
                  size="sm"
                  :class="{'bg-primary/10 text-primary border-primary': activeTab === 'quarterly'}"
                >
                  Quarterly
                </Button>
                <Button 
                  @click="selectDateFilter('full')" 
                  variant="outline"
                  size="sm"
                  :class="{'bg-primary/10 text-primary border-primary': activeTab === 'full'}"
                >
                  Full
                </Button>
              </div>
              <div v-if="dateRange" class="text-sm text-muted-foreground">
                <span v-if="activeTab === 'yesterday' && dateRange.start">
                  Showing data from {{ formatDate(dateRange.start) }}
                </span>
                <span v-else-if="dateRange.start && dateRange.end">
                  Showing data from {{ formatDate(dateRange.start) }} to {{ formatDate(dateRange.end) }}
                </span>
                <span v-else>
                  {{ dateRange.label }}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>
  
        <!-- Filters -->
        <Card>
          <CardHeader>
            <CardTitle>Filters</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex flex-col gap-4">
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
                <div>
                  <Label for="search">Search</Label>
                  <Input
                    id="search"
                    v-model="filters.search"
                    type="text"
                    placeholder="Search..."
                    @input="applyFilters"
                  />
                </div>
                <div>
                  <Label for="dateFrom">Date From</Label>
                  <Input
                    id="dateFrom"
                    v-model="filters.dateFrom"
                    type="date"
                    @change="applyFilters"
                  />
                </div>
                <div>
                  <Label for="dateTo">Date To</Label>
                  <Input
                    id="dateTo"
                    v-model="filters.dateTo"
                    type="date"
                    @change="applyFilters"
                  />
                </div>
              </div>
              <div class="flex justify-end">
                <Button @click="resetFilters" variant="ghost" size="sm">
                  <Icon name="rotate_ccw" class="mr-2 h-4 w-4" />
                  Reset Filters
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
  
        <!-- Table -->
        <Card>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <Table class="relative h-[500px] overflow-auto">
                <TableHeader>
                  <TableRow class="sticky top-0 bg-background border-b z-10 hover:bg-background">
                    <!-- Company Name column for SuperAdmin -->
                    <TableHead v-if="SuperAdmin" class="whitespace-nowrap">Company Name</TableHead>
                    <TableHead class="whitespace-nowrap cursor-pointer" @click="sortBy('week_start_date')">
                      <div class="flex items-center">
                        Week Start Date
                        <div class="ml-2">
                          <svg
                            v-if="sortColumn === 'week_start_date' && sortDirection === 'asc'"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M5 15l7-7 7 7" />
                          </svg>
                          <svg
                            v-else-if="sortColumn === 'week_start_date' && sortDirection === 'desc'"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M19 9l-7 7-7-7" />
                          </svg>
                          <svg
                            v-else
                            class="h-4 w-4 opacity-50"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M5 10l7-7 7 7M5 14l7 7 7-7" />
                          </svg>
                        </div>
                      </div>
                    </TableHead>
                    <TableHead class="whitespace-nowrap cursor-pointer" @click="sortBy('week_end_date')">
                      <div class="flex items-center">
                        Week End Date
                        <div class="ml-2">
                          <svg
                            v-if="sortColumn === 'week_end_date' && sortDirection === 'asc'"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M5 15l7-7 7 7" />
                          </svg>
                          <svg
                            v-else-if="sortColumn === 'week_end_date' && sortDirection === 'desc'"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M19 9l-7 7-7-7" />
                          </svg>
                          <svg
                            v-else
                            class="h-4 w-4 opacity-50"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M5 10l7-7 7 7M5 14l7 7 7-7" />
                          </svg>
                        </div>
                      </div>
                    </TableHead>
                    <TableHead class="whitespace-nowrap cursor-pointer" @click="sortBy('miles')">
                      <div class="flex items-center">
                        Miles
                        <div class="ml-2">
                          <svg
                            v-if="sortColumn === 'miles' && sortDirection === 'asc'"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M5 15l7-7 7 7" />
                          </svg>
                          <svg
                            v-else-if="sortColumn === 'miles' && sortDirection === 'desc'"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M19 9l-7 7-7-7" />
                          </svg>
                          <svg
                            v-else
                            class="h-4 w-4 opacity-50"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                          >
                            <path d="M5 10l7-7 7 7M5 14l7 7 7-7" />
                          </svg>
                        </div>
                      </div>
                    </TableHead>
                    <TableHead class="whitespace-nowrap">Notes</TableHead>
                    <TableHead class="whitespace-nowrap">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="item in filteredEntries" :key="item.id" class="hover:bg-muted/50">
                    <TableCell v-if="SuperAdmin">{{ item.tenant?.name }}</TableCell>
                    <TableCell>{{ formatDate(item.week_start_date) }}</TableCell>
                    <TableCell>{{ formatDate(item.week_end_date) }}</TableCell>
                    <TableCell>{{ formatNumber(item.miles) }}</TableCell>
                    <TableCell>{{ truncateText(item.notes, 30) }}</TableCell>
                    <TableCell>
                      <div class="flex space-x-2">
                        <Button variant="outline" size="sm" @click="openModal(item)">
                          <Icon name="pencil" class="mr-1 h-4 w-4" />
                          Edit
                        </Button>
                        <Button variant="destructive" size="sm" @click="confirmDelete(item)">
                          <Icon name="trash" class="mr-1 h-4 w-4" />
                          Delete
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                  <TableRow v-if="filteredEntries.length === 0">
                    <TableCell :colspan="SuperAdmin ? 6 : 5" class="text-center py-4">
                      No miles driven records found
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            <!-- Pagination -->
            <div class="bg-muted/20 px-4 py-3 border-t" v-if="entries.links">
              <div class="flex justify-between items-center">
                <div class="text-sm text-muted-foreground">
                  Showing {{ filteredEntries.length }} of {{ entries.data.length }} entries
                </div>
                <div class="flex items-center gap-4">
                  <div class="flex items-center gap-2">
                    <Label for="perPage" class="text-sm">Per page:</Label>
                    <select 
                      id="perPage" 
                      v-model="perPage" 
                      @change="changePerPage"
                      class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                      <option value="100">100</option>
                    </select>
                  </div>
                  <div class="flex">
                    <Button 
                      v-for="link in entries.links" 
                      :key="link.label" 
                      @click="visitPage(link.url)" 
                      :disabled="!link.url" 
                      variant="ghost"
                      size="sm"
                      class="mx-1"
                      :class="{'bg-primary/10 text-primary border-primary': link.active}"
                    >
                      <span v-html="link.label"></span>
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
  
      <!-- Add/Edit Modal -->
      <Dialog v-model:open="isModalOpen">
        <DialogContent class="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>{{ form.id ? 'Edit Miles Driven' : 'Add Miles Driven' }}</DialogTitle>
            <DialogDescription>
              {{ form.id ? 'Update the miles driven record details.' : 'Enter the details for the new miles driven record.' }}
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="submitForm" class="grid gap-4 py-4">
            <!-- Tenant selection for SuperAdmin -->
            <div v-if="SuperAdmin" class="col-span-2">
              <Label for="tenant_id">Company</Label>
              <div class="relative">
                <select 
                  id="tenant_id" 
                  v-model="form.tenant_id" 
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm"
                >
                  <option value="">Select Company</option>
                  <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                    {{ tenant.name }}
                  </option>
                </select>
              </div>
              <div v-if="form.errors.tenant_id" class="text-sm text-destructive mt-1">
                {{ form.errors.tenant_id }}
              </div>
            </div>
  
            <!-- Week Start Date -->
            <div>
              <Label for="week_start_date">Week Start Date</Label>
              <Input
                id="week_start_date"
                v-model="form.week_start_date"
                type="date"
              />
              <div v-if="form.errors.week_start_date" class="text-sm text-destructive mt-1">
                {{ form.errors.week_start_date }}
              </div>
            </div>
  
            <!-- Week End Date -->
            <div>
              <Label for="week_end_date">Week End Date</Label>
              <Input
                id="week_end_date"
                v-model="form.week_end_date"
                type="date"
              />
              <div v-if="form.errors.week_end_date" class="text-sm text-destructive mt-1">
                {{ form.errors.week_end_date }}
              </div>
            </div>
  
            <!-- Miles -->
            <div>
              <Label for="miles">Miles</Label>
              <Input
                id="miles"
                v-model="form.miles"
                type="number"
                step="0.01"
                min="0"
              />
              <div v-if="form.errors.miles" class="text-sm text-destructive mt-1">
                {{ form.errors.miles }}
              </div>
            </div>
  
            <!-- Notes -->
            <div>
              <Label for="notes">Notes</Label>
              <textarea
                id="notes"
                v-model="form.notes"
                placeholder="Optional notes"
                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
              ></textarea>
              <div v-if="form.errors.notes" class="text-sm text-destructive mt-1">
                {{ form.errors.notes }}
              </div>
            </div>
            
            <DialogFooter class="mt-4">
              <Button type="button" @click="isModalOpen = false" variant="outline">
                Cancel
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.id ? 'Update' : 'Create' }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
  
      <!-- Delete Confirmation Dialog -->
      <Dialog v-model:open="isDeleteModalOpen">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this miles driven record? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" @click="isDeleteModalOpen = false">
              Cancel
            </Button>
            <Button variant="destructive" @click="deleteRecord" :disabled="deleteForm.processing">
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, computed, watch } from 'vue'
  import { Head, useForm, router } from '@inertiajs/vue3'
  import {
    Alert,
    AlertDescription,
    AlertTitle,
    Button,
    Card,
    CardContent,
    CardHeader,
    CardTitle,
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
  } from '@/Components/ui'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import Icon from '@/components/Icon.vue'
  
  const props = defineProps({
    entries: Object,
    tenantSlug: String,
    SuperAdmin: Boolean,
    tenants: Array,
    dateRange: Object,
    dateFilter: {
      type: String,
      default: 'full'
    }
  })
  
  const successMessage = ref('')
  
  // Modal state
  const isModalOpen = ref(false)
  const isDeleteModalOpen = ref(false)
  const milesDrivenToDelete = ref(null)
  
  // Sorting state
  const sortColumn = ref('week_start_date')
  const sortDirection = ref('desc')
  
  // Filtering state
  const filters = ref({
    search: '',
    dateFrom: '',
    dateTo: '',
  })

  // Add activeTab ref
  const activeTab = ref(props.dateFilter || 'full')

  // Add perPage ref
  const perPage = ref(10)
  
  // Breadcrumbs
  const breadcrumbs = [
    {
      title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
      href: props.tenantSlug
        ? route('dashboard', { tenantSlug: props.tenantSlug })
        : route('admin.dashboard'),
    },
    {
      title: 'Miles Driven',
      href: props.tenantSlug
        ? route('miles_driven.index', { tenantSlug: props.tenantSlug })
        : route('miles_driven.index.admin'),
    }
  ]
  
  // Inertia form for create/update
  const form = useForm({
    id: null,
    tenant_id: props.SuperAdmin ? '' : null,
    week_start_date: '',
    week_end_date: '',
    miles: '',
    notes: '',
  })
  
  // Inertia form for deletion
  const deleteForm = useForm({})
  
  // Computed property for filtered and sorted entries
  const filteredEntries = computed(() => {
    let result = [...props.entries.data]
  
    // Search filter
    if (filters.value.search) {
      const term = filters.value.search.toLowerCase()
      result = result.filter(item =>
        (item.tenant?.name?.toLowerCase().includes(term)) ||
        (item.notes?.toLowerCase().includes(term))
      )
    }
  
    // Date filters
    if (filters.value.dateFrom) {
      result = result.filter(item => item.week_start_date >= filters.value.dateFrom)
    }
    if (filters.value.dateTo) {
      result = result.filter(item => item.week_end_date <= filters.value.dateTo)
    }
  
    // Sorting
    result.sort((a, b) => {
      let aVal = a[sortColumn.value]
      let bVal = b[sortColumn.value]
  
      if (aVal === null) return 1
      if (bVal === null) return -1
  
      if (typeof aVal === 'string') {
        aVal = aVal.toLowerCase()
        bVal = bVal.toLowerCase()
      }
      return sortDirection.value === 'asc' ? (aVal > bVal ? 1 : -1) : (aVal < bVal ? 1 : -1)
    })
  
    return result
  })
  
  // Sort function
  const sortBy = (column) => {
    if (sortColumn.value === column) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortColumn.value = column
      sortDirection.value = 'asc'
    }
  }
  
  // Filter trigger (handled reactively via computed)
  const applyFilters = () => {}
  
  // Reset filters
  const resetFilters = () => {
    filters.value = {
      search: '',
      dateFrom: '',
      dateTo: '',
    }
  }
  
  // Pagination navigation
  const visitPage = (url) => {
    if (url) {
      router.visit(url)
    }
  }
  
  // Open modal for create/edit
  const openModal = (milesDriven = null) => {
    form.reset()
    form.clearErrors()
    if (milesDriven) {
      form.id = milesDriven.id
      form.tenant_id = milesDriven.tenant_id
      form.week_start_date = milesDriven.week_start_date ? formatDateForInput(new Date(milesDriven.week_start_date)) : ''
      form.week_end_date = milesDriven.week_end_date ? formatDateForInput(new Date(milesDriven.week_end_date)) : ''
      form.miles = milesDriven.miles
      form.notes = milesDriven.notes
    } else {
      // Set default dates for new record (current week)
      const today = new Date()
      const dayOfWeek = today.getDay()
      const startDate = new Date(today)
      startDate.setDate(today.getDate() - dayOfWeek)
      const endDate = new Date(startDate)
      endDate.setDate(startDate.getDate() + 6)
      form.week_start_date = formatDateForInput(startDate)
      form.week_end_date = formatDateForInput(endDate)
    }
    isModalOpen.value = true
  }
  
  // Confirm deletion and open delete modal
  const confirmDelete = (item) => {
    if (!confirm('Are you sure you want to delete this miles driven record?')) return;
    milesDrivenToDelete.value = item
    isDeleteModalOpen.value = true
  }
  
  // Submit form for create/update
  const submitForm = () => {
    if (form.id) {
      // Update record
      const routeName = props.SuperAdmin ? 'miles_driven.update.admin' : 'miles_driven.update'
      // FIX: Changed "miles_driven" to "milesDriven" to match the route parameter
      const routeParams = props.SuperAdmin 
        ? { milesDriven: form.id }
        : { tenantSlug: props.tenantSlug, milesDriven: form.id }
      form.put(route(routeName, routeParams), {
        onSuccess: () => {
          isModalOpen.value = false
          successMessage.value = 'Miles driven record updated successfully.'
        }
      })
    } else {
      // Create record
      const routeName = props.SuperAdmin ? 'miles_driven.store.admin' : 'miles_driven.store'
      const routeParams = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug }
      form.post(route(routeName, routeParams), {
        onSuccess: () => {
          isModalOpen.value = false
          successMessage.value = 'Miles driven record created successfully.'
        }
      })
    }
  }
  
  // Delete record function
  const deleteRecord = () => {
    const routeName = props.SuperAdmin ? 'miles_driven.destroy.admin' : 'miles_driven.destroy'
    // FIX: Changed "miles_driven" to "milesDriven" to match the route parameter
    const routeParams = props.SuperAdmin 
      ? { milesDriven: milesDrivenToDelete.value.id }
      : { tenantSlug: props.tenantSlug, milesDriven: milesDrivenToDelete.value.id }
    deleteForm.delete(route(routeName, routeParams), {
      preserveScroll: true,
      onSuccess: () => {
        isDeleteModalOpen.value = false
        successMessage.value = 'Miles driven record deleted successfully.'
      }
    })
  }
  
  // Helper functions
  const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    const utcDate = new Date(date.getTime() + date.getTimezoneOffset() * 60000)
    return utcDate.toLocaleDateString()
  }
  
  const formatDateForInput = (date) => {
    return date.toISOString().split('T')[0]
  }
  
  const formatNumber = (number) => {
    return Number(number).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  }
  
  const truncateText = (text, maxLength) => {
    if (!text) return ''
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
  }
  
  // Auto-hide success message after 5 seconds
  watch(successMessage, (newVal) => {
    if (newVal) {
      setTimeout(() => {
        successMessage.value = ''
      }, 5000)
    }
  })
  
  // Function to handle date filter selection
  function selectDateFilter(filter) {
    activeTab.value = filter
    
    const routeName = props.tenantSlug 
      ? route('miles_driven.index', { tenantSlug: props.tenantSlug }) 
      : route('miles_driven.index.admin')
      
    router.get(routeName, { 
      dateFilter: filter,
      perPage: perPage.value 
    }, { preserveState: true })
  }

  // Function to handle per page change
  function changePerPage() {
    const routeName = props.tenantSlug 
      ? route('miles_driven.index', { tenantSlug: props.tenantSlug }) 
      : route('miles_driven.index.admin')
      
    router.get(routeName, { 
      dateFilter: activeTab.value,
      perPage: perPage.value 
    }, { preserveState: true })
  }
  </script>
  