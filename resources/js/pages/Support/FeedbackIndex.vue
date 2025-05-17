<!-- FeedbackIndex.vue -->
<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
      <Head title="User Feedback" />
  
      <div class="w-[95%] mx-auto p-6 space-y-8">
        <!-- Success -->
        <Alert v-if="successMessage" variant="success" class="animate-in fade-in duration-300">
          <AlertTitle class="flex items-center gap-2">
            <Icon name="check-circle" class="h-5 w-5 text-green-500" />
            Feedback Submitted Successfully!
          </AlertTitle>
          <AlertDescription class="whitespace-pre-line">{{ successMessage }}</AlertDescription>
        </Alert>
  
        <!-- Header + Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
          <div class="flex items-center gap-3">
            <Icon name="message-circle" class="h-7 w-7 text-primary" />
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">User Feedback</h1>
          </div>
          <div class="flex gap-3">
            <Button v-if="!SuperAdmin" @click="openCreateModal" variant="default" class="shadow-sm hover:shadow transition-all">
              <Icon name="plus" class="mr-2 h-4 w-4" />
              Submit Feedback
            </Button>
            <Button
              v-if="selectedFeedback.length > 0"
              @click="showDeleteSelectedModal = true"
              variant="destructive"
              class="shadow-sm hover:shadow transition-all"
            >
              <Icon name="trash" class="mr-2 h-4 w-4" />
              Delete Selected ({{ selectedFeedback.length }})
            </Button>
            <Button v-if="SuperAdmin" @click="openSubjectModal" variant="outline">
              <Icon name="settings" class="mr-2 h-4 w-4" />
              Manage Categories
            </Button>
          </div>
        </div>
  
        <!-- Filters -->
        <Card class="shadow-sm hover:shadow transition-all duration-300">
          <CardHeader class="pb-2"></CardHeader>
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
                      placeholder="Search by category or ID"
                      @keydown.enter.prevent="applyFilters"
                      class="pl-9"
                    />
                  </div>
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
                  <Icon name="rotate-ccw" class="h-4 w-4" /> Reset Filters
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
                    <TableHead class="w-[70px] cursor-pointer" @click="sortBy('id')">
                      <div class="flex items-center gap-1">
                        ID
                        <Icon
                          v-if="sortColumn === 'id'"
                          :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'"
                          class="h-3 w-3"
                        />
                      </div>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="sortBy('subject')">
                      <div class="flex items-center gap-1">
                        Category
                        <Icon
                          v-if="sortColumn === 'subject'"
                          :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'"
                          class="h-3 w-3"
                        />
                      </div>
                    </TableHead>
                    <TableHead class="cursor-pointer" @click="sortBy('created_at')">
                      <div class="flex items-center gap-1">
                        Created
                        <Icon
                          v-if="sortColumn === 'created_at'"
                          :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'"
                          class="h-3 w-3"
                        />
                      </div>
                    </TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-if="filteredFeedback.length === 0">
                    <TableCell :colspan="5" class="py-8 text-center">
                      <div class="flex flex-col items-center justify-center text-muted-foreground">
                        <Icon name="inbox" class="h-12 w-12 mb-2 opacity-20" />
                        <p>No feedback found</p>
                        <Button @click="openCreateModal" variant="link" class="mt-2">Submit first feedback</Button>
                      </div>
                    </TableCell>
                  </TableRow>
                  <TableRow
                    v-for="fb in filteredFeedback"
                    :key="fb.id"
                    class="hover:bg-muted/50 transition-colors"
                    :class="{ 'bg-primary/5': isSelected(fb.id) }"
                  >
                    <TableCell class="text-center">
                      <input
                        type="checkbox"
                        :value="fb.id"
                        v-model="selectedFeedback"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                      />
                    </TableCell>
                    <TableCell class="text-center font-mono text-sm">{{ fb.id }}</TableCell>
                    <TableCell>
                      <div class="flex items-center gap-2">
                        <span class="font-medium">{{ fb.subject }}</span>
                        <Badge v-if="SuperAdmin && !fb.seen_by_admin" variant="destructive" class="ml-2 animate-pulse">New</Badge>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="flex flex-col">
                        <span>{{ formatDateTime(fb.created_at).split(' ')[0] }}</span>
                        <span class="text-xs text-muted-foreground">{{ formatDateTime(fb.created_at).split(' ')[1] }}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="flex gap-2">
                        <Button @click="viewFeedback(fb.id)" size="sm" class="shadow-sm hover:shadow transition-all">
                          <Icon name="eye" class="mr-1 h-4 w-4"/>View
                        </Button>
                        <Button @click="confirmDeleteFeedback(fb.id)" variant="destructive" size="sm" class="shadow-sm hover:shadow transition-all">
                          <Icon name="trash" class="mr-1 h-4 w-4"/>Delete
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
  
            <!-- Pagination -->
            <div class="bg-muted/20 px-4 py-3 border-t" v-if="paginationLinks.length">
              <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-muted-foreground">
                  Showing {{ filteredFeedback.length }} of {{ totalCount }}
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
                      v-for="link in paginationLinks"
                      :key="link.label"
                      @click="visitPage(link.url)"
                      :disabled="!link.url"
                      size="sm"
                      variant="ghost"
                      class="mx-1"
                      :class="{ 'bg-primary/10 text-primary border-primary': link.active }"
                    >
                      <span v-html="link.label"></span>
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
  
        <!-- Bulk Delete Dialog -->
        <Dialog v-model:open="showDeleteSelectedModal">
          <DialogContent>
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <Icon name="alert-triangle" class="h-5 w-5 text-destructive" />
                Confirm Bulk Deletion
              </DialogTitle>
              <DialogDescription>
                Are you sure you want to delete {{ selectedFeedback.length }} items? This action cannot be undone.
              </DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-4">
              <Button variant="outline" @click="showDeleteSelectedModal=false">Cancel</Button>
              <Button variant="destructive" @click="deleteSelectedFeedback">Delete</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
  
        <!-- New Feedback Modal -->
        <Dialog v-model:open="showCreateModal">
          <DialogContent class="sm:max-w-lg w-[90vw]">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <Icon name="plus-circle" class="h-5 w-5 text-primary" />
                New Feedback
              </DialogTitle>
              <DialogDescription>
                Submit your feedback below so we can improve our services and address your concerns.
              </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitForm" class="space-y-4 p-4">
              <div>
                <Label for="subjectSelect">Category</Label>
                <div class="space-y-2">
                  <select
                    id="subjectSelect"
                    v-model="selectedSubject"
                    @change="handleSubjectChange"
                    class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                    required
                  >
                    <option value="" disabled selected>Select a category</option>
                    <option v-for="sub in activeSubjects" :key="sub.id" :value="sub.name">
                      {{ sub.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div>
                <Label for="message">Message</Label>
                <textarea
                  id="message"
                  v-model="form.message"
                  rows="4"
                  placeholder="Describe your feedback in detail..."
                  class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                  required
                ></textarea>
              </div>
              <DialogFooter class="flex justify-end gap-2">
                <Button type="button" @click="showCreateModal = false" variant="outline">
                  Cancel
                </Button>
                <Button type="submit" class="shadow-sm hover:shadow transition-all">Submit</Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
  
        <!-- Manage Categories Modal -->
        <Dialog v-model:open="showSubjectModal">
          <DialogContent class="sm:max-w-lg">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <Icon name="list" class="h-5 w-5 text-primary" />
                Manage Categories
              </DialogTitle>
              <DialogDescription>
                Create and manage feedback categories.
              </DialogDescription>
            </DialogHeader>
            <div class="space-y-4 p-4">
              <form @submit.prevent="submitSubjectForm" class="flex gap-2 mb-6">
                <div class="flex-1">
                  <Input
                    v-model="subjectForm.name"
                    placeholder="Enter category name..."
                    required
                    class="w-full"
                  />
                </div>
                <Button type="submit" class="shadow-sm hover:shadow transition-all">
                  <Icon name="plus" class="mr-2 h-4 w-4" />
                  Add
                </Button>
              </form>
              <div class="border rounded-md">
                <div class="bg-muted/30 px-4 py-2 border-b">
                  <h3 class="font-medium">Available Categories</h3>
                </div>
                <div class="divide-y max-h-[300px] overflow-y-auto">
                  <div v-if="activeSubjects.length === 0" class="p-4 text-center text-muted-foreground">
                    No categories found. Create your first one above.
                  </div>
                  <div v-for="sub in activeSubjects" :key="sub.id" class="flex items-center justify-between p-3 hover:bg-muted/30">
                    <span>{{ sub.name }}</span>
                    <Button 
                      variant="ghost" 
                      size="sm" 
                      @click="deleteSubject(sub.id)"
                      class="h-8 w-8 p-0"
                      title="Delete Category"
                    >
                      <Icon name="trash" class="h-4 w-4 text-destructive" />
                    </Button>
                  </div>
                </div>
              </div>
              <div v-if="deletedSubjects.length" class="border rounded-md mt-4">
                <div class="bg-muted/30 px-4 py-2 border-b">
                  <h3 class="font-medium">Deleted Categories</h3>
                </div>
                <div class="divide-y max-h-[200px] overflow-y-auto">
                  <div v-for="sub in deletedSubjects" :key="sub.id" class="flex items-center justify-between p-3 hover:bg-muted/30">
                    <span class="text-muted-foreground flex items-center">
                      {{ sub.name }}
                      <span class="ml-2 text-xs text-red-500">(Deleted)</span>
                    </span>
                    <div class="flex gap-2">
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="restoreSubject(sub.id)"
                        class="h-8 w-8 p-0"
                        title="Restore Category"
                      >
                        <Icon name="undo" class="h-4 w-4 text-primary" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="forceDeleteSubject(sub.id)"
                        class="h-8 w-8 p-0"
                        title="Permanently Delete"
                      >
                        <Icon name="x" class="h-4 w-4 text-destructive" />
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <DialogFooter>
              <Button @click="showSubjectModal=false" variant="outline">Close</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </AppLayout>
  </template>
  <script setup>
  import { ref, watch, computed } from 'vue';
  import { useForm, router, Head } from '@inertiajs/vue3';
  import AppLayout from '@/layouts/AppLayout.vue';
  import Icon from '@/components/Icon.vue';
  import { Button, Card, CardContent, Table, TableHeader, TableBody, TableRow, TableHead, TableCell, Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, Label, Input, Alert, AlertTitle, AlertDescription, Badge } from '@/components/ui';
  
  // Props with defaults to avoid undefined
  const props = defineProps({
    feedbacks: { type: Object, default: () => ({ data: [], links: [], total: 0, per_page: 10 }) },
    tenantSlug: { type: String, default: null },
    SuperAdmin: { type: Boolean, default: false },
    filters: { type: Object, default: () => ({ search: '', unseen: false }) },
    feedback_subjects: { type: Array, default: () => [] }
  });
  
  // Reactive state
  const feedbacksList = computed(() => props.feedbacks.data || []);
  const paginationLinks = computed(() => props.feedbacks.links || []);
  const totalCount = computed(() => props.feedbacks.total || 0);
  const perPage = ref(props.feedbacks.per_page || 10);
  const filters = ref({ ...props.filters });
  const successMessage = ref('');
  const showCreateModal = ref(false);
  const showSubjectModal = ref(false);
  const form = useForm({ subject: '', message: '' });
  const subjectForm = useForm({ name: '' });
  const selectedFeedback = ref([]);
  const showDeleteSelectedModal = ref(false);
  const sortColumn = ref('created_at');
  const sortDirection = ref('desc');
  const selectedSubject = ref('');
  
  // Breadcrumbs
  const breadcrumbs = [
    { title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard', href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard') },
    { title: 'User Feedback', href: props.tenantSlug ? route('support.feedback.index', { tenantSlug: props.tenantSlug }) : route('support.feedback.index.admin') }
  ];
  
  // Computeds
  const isAllSelected = computed(() => feedbacksList.value.length > 0 && selectedFeedback.value.length === feedbacksList.value.length);
  const activeSubjects = computed(() => props.feedback_subjects.filter(s => !s.deleted_at));
  const deletedSubjects = computed(() => props.feedback_subjects.filter(s => s.deleted_at));
  const filteredFeedback = computed(() => {
    let list = [...feedbacksList.value];
    const q = filters.value.search?.toLowerCase() || '';
    if (q) list = list.filter(f => f.subject?.toLowerCase().includes(q) || String(f.id).includes(q));
    if (filters.value.unseen) list = list.filter(f => props.SuperAdmin ? !f.seen_by_admin : false);
    return list;
  });
  
  // Methods
  function toggleSelectAll() {
    isAllSelected.value ? selectedFeedback.value = [] : selectedFeedback.value = feedbacksList.value.map(f => f.id);
  }
  function isSelected(id) { return selectedFeedback.value.includes(id); }
  function sortBy(col) {
    if (sortColumn.value === col) sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    else { sortColumn.value = col; sortDirection.value = 'asc'; }
    const name = props.tenantSlug ? route('support.feedback.index', { tenantSlug: props.tenantSlug }) : route('support.feedback.index.admin');
    router.get(name, { ...filters.value, sort: sortColumn.value, direction: sortDirection.value, per_page: perPage.value }, { preserveState: true });
  }
  function applyFilters() {}
  function resetFilters() { filters.value = { search: '', unseen: false }; }
  function changePerPage() { sortBy(sortColumn.value); }
  function visitPage(url) { if (!url) return; router.get(new URL(url).href, {}, { preserveState: true }); }
  
  // Feedback CRUD
  function openCreateModal() { 
    form.reset(); 
    selectedSubject.value = ''; 
    showCreateModal.value = true; 
  }
  function submitForm() {
    // Make sure subject is set from selectedSubject
    form.subject = selectedSubject.value;
    
    const routeName = props.tenantSlug ? 'support.feedback.store' : 'support.feedback.store.admin';
    const params = props.tenantSlug ? { tenantSlug: props.tenantSlug } : {};
    form.post(route(routeName, params), { onSuccess() { successMessage.value = 'Thank you for your feedback.'; showCreateModal.value = false; form.reset(); } });
  }
  
  // Single view/delete
  function viewFeedback(id) {
    const routeName = props.tenantSlug ? 'support.feedback.show' : 'support.feedback.show.admin';
    const params = props.tenantSlug ? { tenantSlug: props.tenantSlug, feedback: id } : id;
    router.get(route(routeName, params));
  }
  function confirmDeleteFeedback(id) { selectedFeedback.value = [id]; showDeleteSelectedModal.value = true; }
  
  // Bulk delete
  function deleteSelectedFeedback() {
    if (!selectedFeedback.value.length) return;
    const routeName = props.SuperAdmin ? 'support.feedback.destroyBulk.admin' : 'support.feedback.destroyBulk';
    const params = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };
    const bulk = useForm({ ids: selectedFeedback.value });
    bulk.delete(route(routeName, params), { onSuccess() { successMessage.value = `${selectedFeedback.value.length} deleted.`; selectedFeedback.value = []; showDeleteSelectedModal.value = false; } });
  }
  
  // Category management
  function openSubjectModal() { subjectForm.reset(); showSubjectModal.value = true; }
  function submitSubjectForm() { subjectForm.post(route('feedback.categories.store.admin'), { onSuccess() { subjectForm.reset(); successMessage.value = 'Category created.'; } }); }
  function handleSubjectChange() { form.subject = selectedSubject.value; }
  function deleteSubject(id) { if (confirm('Delete this category?')) router.delete(route('feedback.categories.destroy.admin', id), { onSuccess() { successMessage.value = 'Deleted'; } }); }
  function restoreSubject(id) { router.post(route('feedback.categories.restore.admin', id), {}, { onSuccess() { successMessage.value = 'Restored'; } }); }
  function forceDeleteSubject(id) { if (confirm('Permanently delete?')) router.delete(route('feedback.categories.forceDelete.admin', id), { onSuccess() { successMessage.value = 'Removed'; } }); }
  
  // Formatting
  function formatDateTime(dt) { if (!dt) return 'N/A'; const d = new Date(dt); return `${d.getMonth()+1}-${d.getDate()}-${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`; }
  
  watch(successMessage, v => { if (v) setTimeout(() => successMessage.value = '', 8000); });
  </script>
  
  <style scoped>
  /* no custom styles */
  </style>