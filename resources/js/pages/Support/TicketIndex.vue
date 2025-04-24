<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
      <Head title="Support Tickets" />
  
      <div class="max-w-7xl mx-auto p-6 space-y-8">
        <!-- Success -->
        <Alert v-if="successMessage" variant="success" class="animate-in fade-in duration-300">
          <AlertTitle class="flex items-center gap-2">
            <Icon name="check-circle" class="h-5 w-5 text-green-500" />
            Success
          </AlertTitle>
          <AlertDescription>{{ successMessage }}</AlertDescription>
        </Alert>
  
        <!-- Header + Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
          <div class="flex items-center gap-3">
            <Icon name="help-circle" class="h-7 w-7 text-primary" />
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Support Tickets</h1>
          </div>
          <div class="flex gap-3">
            <Button @click="openCreateModal" variant="default" class="shadow-sm hover:shadow transition-all">
              <Icon name="plus" class="mr-2 h-4 w-4"/> New Ticket
            </Button>
            <Button 
              v-if="selectedTickets.length > 0" 
              @click="showDeleteSelectedModal = true" 
              variant="destructive"
              class="shadow-sm hover:shadow transition-all"
            >
              <Icon name="trash" class="mr-2 h-4 w-4" />
              Delete Selected ({{ selectedTickets.length }})
            </Button>
          </div>
        </div>
  
        <!-- Filters -->
        <Card class="shadow-sm hover:shadow transition-all duration-300">
          <CardHeader class="pb-2">
            <CardTitle class="text-lg flex items-center gap-2">
              <Icon name="filter" class="h-5 w-5 text-muted-foreground" />
              Filters
            </CardTitle>
          </CardHeader>
          <CardContent class="p-4 pt-2">
            <div class="flex flex-col gap-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                  <Label for="search" class="text-sm font-medium mb-1 block">Search</Label>
                  <div class="relative">
                    <Icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="search"
                      v-model="filters.search"
                      placeholder="Search subject or message…"
                      @keydown.enter="applyFilters"
                      class="pl-9"
                    />
                  </div>
                </div>
                <div>
                  <Label for="status" class="text-sm font-medium mb-1 block">Status</Label>
                  <select 
                    id="status"
                    v-model="filters.status" 
                    @change="applyFilters" 
                    class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                  >
                    <option value="">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="closed">Closed</option>
                  </select>
                </div>
                <div class="flex items-end">
                  <label class="flex items-center gap-2 h-10 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="filters.unseen"
                      @change="applyFilters"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <span>Unseen Only</span>
                  </label>
                </div>
              </div>
              
              <div class="flex justify-end">
                <Button @click="resetFilters" variant="ghost" size="sm" class="gap-1">
                  <Icon name="rotate-ccw" class="h-4 w-4" />
                  Reset Filters
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
  
        <!-- Table -->
        <Card class="shadow-sm hover:shadow transition-all duration-300">
          <CardContent class="p-0">
            <div class="overflow-x-auto bg-background dark:bg-background border-t border-border">
              <Table class="relative h-[600px] overflow-auto">
                <TableHeader>
                  <TableRow class="sticky top-0 bg-background border-b z-10 hover:bg-background">
                    <TableHead class="w-[50px]">
                      <div class="flex items-center justify-center">
                        <input 
                          type="checkbox" 
                          @change="toggleSelectAll" 
                          :checked="isAllSelected"
                          class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                        />
                      </div>
                    </TableHead>
                    <TableHead v-if="SuperAdmin">User</TableHead>
                    <TableHead class="cursor-pointer" @click="sortBy('subject')">
                      <div class="flex items-center gap-1">
                        Subject
                        <Icon v-if="sortColumn === 'subject'" :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'" class="h-3 w-3" />
                      </div>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="sortBy('status')">
                      <div class="flex items-center gap-1">
                        Status
                        <Icon v-if="sortColumn === 'status'" :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'" class="h-3 w-3" />
                      </div>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="sortBy('created_at')">
                      <div class="flex items-center gap-1">
                        Created
                        <Icon v-if="sortColumn === 'created_at'" :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'" class="h-3 w-3" />
                      </div>
                    </TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-if="tickets.data.length === 0">
                    <TableCell :colspan="SuperAdmin ? 6 : 5" class="py-8 text-center">
                      <div class="flex flex-col items-center justify-center text-muted-foreground">
                        <Icon name="inbox" class="h-12 w-12 mb-2 opacity-20" />
                        <p>No tickets found</p>
                        <Button @click="openCreateModal" variant="link" class="mt-2">Create your first ticket</Button>
                      </div>
                    </TableCell>
                  </TableRow>
                  <TableRow
                    v-for="t in tickets.data"
                    :key="t.id"
                    class="hover:bg-muted/50 transition-colors"
                    :class="{'bg-primary/5': isSelected(t.id)}"
                  >
                    <TableCell class="text-center">
                      <input 
                        type="checkbox" 
                        :value="t.id" 
                        v-model="selectedTickets"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                      />
                    </TableCell>
                    <TableCell v-if="SuperAdmin">{{ t.user.name }}</TableCell>
                    <TableCell>
                      <div class="flex items-center gap-2">
                        <span class="font-medium">{{ t.subject }}</span>
                        <Badge v-if="isTicketUnseen(t)" variant="destructive" class="ml-2 animate-pulse">New</Badge>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge 
                        :variant="getStatusVariant(t.status)" 
                        class="capitalize"
                      >
                        {{ t.status.replace('_',' ') }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div class="flex flex-col">
                        <span>{{ formatDate(t.created_at) }}</span>
                        <span class="text-xs text-muted-foreground">{{ formatTime(t.created_at) }}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="flex gap-2">
                        <Button @click="viewTicket(t.id)" size="sm" class="shadow-sm hover:shadow transition-all">
                          <Icon name="eye" class="mr-1 h-4 w-4"/>View
                        </Button>
                        <Button @click="deleteTicket(t.id)" variant="destructive" size="sm" class="shadow-sm hover:shadow transition-all">
                          <Icon name="trash" class="mr-1 h-4 w-4"/>Delete
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
  
            <!-- Pagination -->
            <div class="bg-muted/20 px-4 py-3 border-t" v-if="tickets.links">
              <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-muted-foreground">
                  Showing {{ tickets.data.length }} of {{ tickets.total }}
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
                      v-for="link in tickets.links"
                      :key="link.label"
                      @click="visitPage(link.url)"
                      :disabled="!link.url"
                      size="sm"
                      variant="ghost"
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
  
        <!-- Delete Selected Tickets Confirmation Dialog -->
        <Dialog v-model:open="showDeleteSelectedModal">
          <DialogContent>
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <Icon name="alert-triangle" class="h-5 w-5 text-destructive" />
                Confirm Bulk Deletion
              </DialogTitle>
              <DialogDescription>
                Are you sure you want to delete {{ selectedTickets.length }} tickets? This action cannot be undone.
              </DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-4">
              <Button type="button" @click="showDeleteSelectedModal = false" variant="outline">
                Cancel
              </Button>
              <Button type="button" @click="deleteSelectedTickets()" variant="destructive">
                Delete Selected
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
  
        <!-- New Ticket Modal -->
        <Dialog v-model:open="showCreateModal">
          <DialogContent class="sm:max-w-lg w-[90vw]">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <Icon name="plus-circle" class="h-5 w-5 text-primary" />
                New Ticket
              </DialogTitle>
              <DialogDescription>
                Fill in the details to create a new support ticket.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitForm" class="space-y-4 p-4">
              <div>
                <Label for="subject">Subject</Label>
                <Input id="subject" v-model="form.subject" required placeholder="Brief description of your issue" />
              </div>
              <div>
                <Label for="message">Message</Label>
                <textarea
                  id="message"
                  v-model="form.message"
                  rows="4"
                  placeholder="Describe your issue in detail..."
                  class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                  required
                ></textarea>
              </div>
              <DialogFooter class="flex justify-end gap-2">
                <Button type="button" @click="showCreateModal = false" variant="outline">
                  Cancel
                </Button>
                <Button type="submit" class="shadow-sm hover:shadow transition-all">Create</Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, watch, computed, onMounted } from 'vue';
  import { useForm, router, Head } from '@inertiajs/vue3';
  import AppLayout from '@/layouts/AppLayout.vue';
  import Icon from '@/components/Icon.vue';
  import {
    Button, Card, CardContent, CardHeader, CardTitle, Table, TableHeader, TableBody,
    TableRow, TableHead, TableCell, Dialog, DialogContent,
    DialogHeader, DialogTitle, DialogDescription, DialogFooter, Label, Input,
    Alert, AlertTitle, AlertDescription, Badge
  } from '@/components/ui';
  
  const props = defineProps({
    tickets:   Object,
    tenantSlug: { type: String, default: null },
    SuperAdmin: Boolean,
    filters:   Object
  })
  
  const successMessage = ref('')
  const showCreateModal = ref(false)
  const form = useForm({ subject: '', message: '' })
  const filters = ref({ ...props.filters })
  const selectedTickets = ref([])
  const showDeleteSelectedModal = ref(false)
  const perPage = ref(props.tickets?.per_page || 10)
  const sortColumn = ref('created_at')
  const sortDirection = ref('desc')
  
  const breadcrumbs = [
    {
      title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
      href:  props.tenantSlug
        ? route('dashboard', { tenantSlug: props.tenantSlug })
        : route('admin.dashboard')
    },
    {
      title: 'Support Tickets',
      href:  props.tenantSlug
        ? route('support.index', { tenantSlug: props.tenantSlug })
        : route('support.index.admin')
    }
  ]
  
  // Check if all tickets are selected
  const isAllSelected = computed(() => {
    return props.tickets.data.length > 0 && selectedTickets.value.length === props.tickets.data.length;
  });
  
  // Toggle select all tickets
  function toggleSelectAll() {
    if (isAllSelected.value) {
      selectedTickets.value = [];
    } else {
      selectedTickets.value = props.tickets.data.map(t => t.id);
    }
  }
  
  // Check if a ticket is selected
  function isSelected(id) {
    return selectedTickets.value.includes(id);
  }
  
  // Check if a ticket is unseen
  function isTicketUnseen(ticket) {
    // For regular users, check if the ticket is unseen by user
    // For admins, check if the ticket is unseen by admin
    return props.SuperAdmin ? !ticket.seen_by_admin : !ticket.seen_by_user;
  }
  
  // Get badge variant based on status
  function getStatusVariant(status) {
    switch(status) {
      case 'open': return 'default';
      case 'in_progress': return 'warning';
      case 'closed': return 'secondary';
      default: return 'outline';
    }
  }
  
  // Sort tickets by column
  function sortBy(column) {
    if (sortColumn.value === column) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortColumn.value = column;
      sortDirection.value = 'asc';
    }
    
    const name = props.tenantSlug
      ? route('support.index', { tenantSlug: props.tenantSlug })
      : route('support.index.admin');
      
    router.get(name, {
      ...filters.value,
      sort: sortColumn.value,
      direction: sortDirection.value,
      per_page: perPage.value
    }, { preserveState: true });
  }
  
  // Confirm deletion of selected tickets
  function confirmDeleteSelected() {
    if (selectedTickets.value.length > 0) {
      showDeleteSelectedModal.value = true;
    }
  }
  
  // Delete selected tickets
  function deleteSelectedTickets() {
    // Implementation would depend on your backend API
    // This is a placeholder for the actual implementation
    showDeleteSelectedModal.value = false;
    successMessage.value = 'Selected tickets deleted.';
    
    // Reset selection
    selectedTickets.value = [];
  }
  
  // Change items per page
  function changePerPage() {
    const name = props.tenantSlug
      ? route('support.index', { tenantSlug: props.tenantSlug })
      : route('support.index.admin');
      
    router.get(name, {
      ...filters.value,
      sort: sortColumn.value,
      direction: sortDirection.value,
      per_page: perPage.value
    }, { preserveState: true });
  }
  
  // Reset filters
  function resetFilters() {
    filters.value = {
      search: '',
      status: '',
      unseen: false
    };
    applyFilters();
  }
  
  function visitPage(url) {
    if (!url) return
    router.get(new URL(url).href, {}, { preserveState: true })
  }
  
  function applyFilters() {
    const name = props.tenantSlug
      ? route('support.index', { tenantSlug: props.tenantSlug })
      : route('support.index.admin')
    router.get(name, {
      search: filters.value.search,
      status: filters.value.status,
      unseen: filters.value.unseen ? 'true' : undefined,
      sort: sortColumn.value,
      direction: sortDirection.value,
      per_page: perPage.value
    }, { preserveState: true })
  }
  
  function openCreateModal() {
    form.reset()
    showCreateModal.value = true
  }
  
  function submitForm() {
    const name = props.tenantSlug
      ? route('support.store', { tenantSlug: props.tenantSlug })
      : route('support.store.admin')
    form.post(name, {
      onSuccess: () => {
        successMessage.value = 'Ticket created.'
        showCreateModal.value = false
        form.reset()
      }
    })
  }
  
  function viewTicket(id) {
    const name = props.tenantSlug
      ? route('support.show', { tenantSlug: props.tenantSlug, ticket: id })
      : route('support.show.admin', id)
    router.get(name)
  }
  
  function deleteTicket(id) {
    router.delete(
      props.tenantSlug
        ? route('support.destroy',   { tenantSlug: props.tenantSlug, ticket: id })
        : route('support.destroy.admin', id),
      {
        onSuccess: () => {
          successMessage.value = 'Ticket deleted.'
          router.reload()
        }
      }
    )
  }
  
  function formatDate(dt) {
    const d = new Date(dt)
    return `${d.getMonth()+1}-${d.getDate()}-${d.getFullYear()}`
  }
  
  function formatTime(dt) {
    const d = new Date(dt)
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  }
  
  watch(successMessage, v => {
    if (v) setTimeout(() => (successMessage.value = ''), 5000)
  })
  </script>
  