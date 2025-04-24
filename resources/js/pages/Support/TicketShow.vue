<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <Head :title="`Ticket #${ticket.id}`" />

    <div class="max-w-3xl mx-auto p-6 space-y-6">
      <!-- Success Message -->
      <Alert v-if="successMessage" variant="success" class="animate-in fade-in duration-300">
        <AlertTitle class="flex items-center gap-2">
          <Icon name="check-circle" class="h-5 w-5 text-green-500" />
          Success
        </AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>

      <!-- Ticket Details -->
      <Card class="shadow-sm hover:shadow transition-all duration-300">
        <CardHeader class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-3 border-b">
          <div>
            <div class="flex items-center gap-2">
              <Icon name="ticket" class="h-5 w-5 text-primary" />
              <CardTitle class="text-xl">{{ ticket.subject }}</CardTitle>
              <Badge :variant="getStatusVariant(ticket.status)" class="capitalize animate-pulse" v-if="ticket.status === 'open'">
                {{ ticket.status.replace('_',' ') }}
              </Badge>
              <Badge :variant="getStatusVariant(ticket.status)" class="capitalize" v-else>
                {{ ticket.status.replace('_',' ') }}
              </Badge>
            </div>
            <p class="text-sm text-muted-foreground mt-1 flex items-center gap-1">
              <Icon name="calendar" class="h-3.5 w-3.5" />
              Created: {{ formatDateTime(ticket.created_at) }}
              <span v-if="ticket.user" class="flex items-center gap-1 ml-2">
                <Icon name="user" class="h-3.5 w-3.5" />
                by {{ ticket.user.name }}
              </span>
            </p>
          </div>
          
          <!-- Status Update Form (Super-admin only) -->
          <div v-if="SuperAdmin" class="flex-shrink-0">
            <form @submit.prevent="updateStatus" class="flex gap-2">
              <select 
                v-model="statusForm.status" 
                required 
                class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-primary/30 transition-all"
              >
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="closed">Closed</option>
              </select>
              <Button type="submit" size="sm" class="shadow-sm hover:shadow transition-all">
                <Icon name="save" class="mr-2 h-4 w-4" />
                Update Status
              </Button>
            </form>
          </div>
        </CardHeader>
        <CardContent class="pt-4">
          <div class="bg-muted/30 p-4 rounded-md border border-border/50 hover:border-primary/20 transition-colors">
            <p class="whitespace-pre-line">{{ ticket.message }}</p>
          </div>
        </CardContent>
      </Card>

      <!-- Responses -->
      <div>
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
            <Icon name="message-circle" class="h-5 w-5 text-primary" />
            Responses
          </h2>
          <Badge v-if="ticket.responses.length" variant="outline" class="animate-in slide-in-from-right">
            {{ ticket.responses.length }} {{ ticket.responses.length === 1 ? 'response' : 'responses' }}
          </Badge>
        </div>
        
        <div v-if="!ticket.responses.length" class="bg-muted/20 p-6 rounded-md text-center text-muted-foreground border border-dashed border-border">
          <div class="flex flex-col items-center">
            <Icon name="inbox" class="h-12 w-12 mb-3 opacity-20" />
            <p>No responses yet.</p>
            <p class="text-sm mt-2" v-if="ticket.status !== 'closed'">Be the first to respond below.</p>
          </div>
        </div>
        
        <div class="space-y-4 mt-4">
          <TransitionGroup name="list" tag="div" class="space-y-4">
            <Card 
              v-for="(r, index) in ticket.responses" 
              :key="r.id"
              :class="{
                'border-primary/20 bg-primary/5': r.is_admin,
                'border-l-4 border-l-primary': r.is_admin && !r.seen_at
              }"
              class="shadow-sm hover:shadow transition-all duration-300"
            >
              <CardContent class="p-4">
                <div class="flex justify-between items-center mb-2">
                  <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold">
                      {{ r.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <span class="font-semibold">{{ r.user.name }}</span>
                      <Badge v-if="r.is_admin" variant="outline" class="text-xs ml-1">Staff</Badge>
                      <Badge v-if="!r.seen_at" variant="destructive" class="text-xs ml-1 animate-pulse">New</Badge>
                    </div>
                  </div>
                  <span class="text-xs text-muted-foreground flex items-center gap-1">
                    <Icon name="clock" class="h-3.5 w-3.5" />
                    {{ formatDateTime(r.created_at) }}
                  </span>
                </div>
                <div class="bg-background/50 p-3 rounded-md border border-border/50 mt-2 hover:border-primary/20 transition-colors">
                  <p class="whitespace-pre-line">{{ r.message }}</p>
                </div>
                <div class="text-xs text-right mt-2 text-muted-foreground" v-if="index === ticket.responses.length - 1">
                  <span v-if="r.seen_at">Seen {{ formatDateTime(r.seen_at) }}</span>
                  <span v-else>Not seen yet</span>
                </div>
              </CardContent>
            </Card>
          </TransitionGroup>
        </div>
      </div>

      <!-- New Response (only show if ticket is not closed) -->
      <Card v-if="ticket.status !== 'closed'" class="shadow-sm hover:shadow transition-all duration-300 border-primary/10">
        <CardHeader class="pb-2">
          <CardTitle class="flex items-center gap-2">
            <Icon name="reply" class="h-5 w-5 text-primary" />
            Add Response
          </CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitResponse" class="space-y-4">
            <textarea
              v-model="responseForm.message"
              rows="4"
              class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 transition-all focus:border-primary"
              placeholder="Your response…"
              required
            ></textarea>
            <DialogFooter class="flex justify-end">
              <Button 
                type="submit" 
                class="shadow-sm hover:shadow transition-all"
                :disabled="responseForm.processing"
              >
                <Icon name="send" class="mr-2 h-4 w-4" />
                {{ responseForm.processing ? 'Sending...' : 'Send Response' }}
              </Button>
            </DialogFooter>
          </form>
        </CardContent>
      </Card>
      
      <!-- Ticket Closed Message (only show if ticket is closed) -->
      <Card v-else class="border-muted bg-muted/10 shadow-sm">
        <CardContent class="p-4 text-center">
          <div class="flex flex-col items-center justify-center py-4">
            <Icon name="check-circle" class="h-12 w-12 text-muted-foreground mb-2" />
            <p class="text-muted-foreground">This ticket is closed. No further responses can be added.</p>
            <Button 
              v-if="SuperAdmin" 
              @click="reopenTicket" 
              variant="outline" 
              size="sm" 
              class="mt-4 shadow-sm hover:shadow transition-all"
            >
              <Icon name="refresh-cw" class="mr-2 h-4 w-4" />
              Reopen Ticket
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Back to Tickets Button -->
      <div class="flex justify-center mt-8">
        <Link
          :href="props.tenantSlug
            ? route('support.index', { tenantSlug: props.tenantSlug })
            : route('support.index.admin')"
          class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 shadow-sm hover:shadow transition-all"
        >
          <Icon name="arrow-left" class="mr-2 h-4 w-4" />
          Back to Tickets
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, router, Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Icon from '@/components/Icon.vue'
import {
  Button, Card, CardHeader, CardTitle, CardContent, CardFooter,
  DialogFooter, Badge, Alert, AlertTitle, AlertDescription
} from '@/components/ui'

const props = defineProps({
  ticket:     Object,
  tenantSlug: { type: String, default: null },
  SuperAdmin: Boolean
})

const successMessage = ref('')
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
  },
  { title: `Ticket #${props.ticket.id}`, href: '' }
]

const statusForm = useForm({ status: props.ticket.status })
const responseForm = useForm({ ticket_id: props.ticket.id, message: '' })

// Get badge variant based on status
function getStatusVariant(status) {
  switch(status) {
    case 'open': return 'default';
    case 'in_progress': return 'warning';
    case 'closed': return 'secondary';
    default: return 'outline';
  }
}

function updateStatus() {
  const name = props.tenantSlug
    ? route('support.update.status', { tenantSlug: props.tenantSlug, ticket: props.ticket.id })
    : route('support.update.status.admin', props.ticket.id)
  
  statusForm.put(name, { 
    onSuccess: () => {
      successMessage.value = `Ticket status updated to ${statusForm.status.replace('_', ' ')}.`
      setTimeout(() => router.reload(), 1500)
    } 
  })
}

function reopenTicket() {
  statusForm.status = 'open'
  updateStatus()
}

function submitResponse() {
  const name = props.tenantSlug
    ? route('support.responses.store', { tenantSlug: props.tenantSlug })
    : route('support.responses.store.admin')
  
  responseForm.post(name, { 
    onSuccess: () => {
      successMessage.value = 'Response added successfully.'
      setTimeout(() => router.reload(), 1500)
    } 
  })
}

function formatDateTime(dt) {
  if (!dt) return 'N/A';
  
  const d = new Date(dt)
  const hh = String(d.getHours()).padStart(2,'0')
  const mm = String(d.getMinutes()).padStart(2,'0')
  return `${d.getMonth()+1}-${d.getDate()}-${d.getFullYear()} ${hh}:${mm}`
}

// Scroll to bottom of page when there are responses
onMounted(() => {
  if (props.ticket.responses && props.ticket.responses.length > 0) {
    // Add a small delay to ensure the DOM is fully rendered
    setTimeout(() => {
      window.scrollTo({
        top: document.body.scrollHeight,
        behavior: 'smooth'
      });
    }, 500);
  }
})
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
  