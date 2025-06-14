<template>
  <Card class="overflow-hidden rounded-xl shadow-md border border-gray-200 dark:border-gray-700 max-w-2xl">
    <form @submit.prevent="emit('submit')" class="p-6 space-y-6">
      <!-- Coaching Message Section -->
      <div class="grid gap-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <Label for="coaching_message" class="text-lg font-bold">Coaching Message</Label>
          <Badge variant="outline" class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1.5 font-medium">
            <span class="font-mono">{{ charCount }}</span> / 160 characters
          </Badge>
        </div>

        <!-- Placeholder Buttons -->
        <div class="flex flex-wrap gap-2 mb-2">
          <button
            v-for="placeholder in placeholders"
            :key="placeholder.value"
            type="button"
            @click="insertPlaceholder(placeholder.value)"
            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 text-xs transition-colors"
            :title="placeholder.description"
          >
            <span class="font-mono text-xs">{{ placeholder.label }}</span>
          </button>
        </div>

        <!-- Content Editable Input -->
        <div
          contenteditable
          ref="editableDiv"
          class="w-full border border-gray-200 dark:border-gray-700 rounded-xl p-6 bg-white dark:bg-gray-800 min-h-[200px] resize-y focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring font-sans whitespace-pre-wrap"
          @input="handleInput"
          @paste.prevent="handlePaste"
          placeholder="Type your SMS message here (max 160 characters)..."
        ></div>

        <!-- Hidden real input -->
        <textarea v-model="form.coaching_message" class="hidden" />

        <p class="text-sm text-muted-foreground mt-1">
          Placeholders will be replaced with actual values when sent
        </p>
        <p v-if="charCount > 160" class="text-sm text-destructive mt-1">
          Message exceeds 160 character limit
        </p>
        <p v-if="form.errors.coaching_message" class="text-sm text-destructive mt-1">
          {{ form.errors.coaching_message }}
        </p>
      </div>

      <!-- Performance Categories -->
      <div>
        <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">Performance Categories</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="(label, field) in fields" :key="field" class="grid gap-2">
            <Label :for="field" class="font-medium text-gray-700 dark:text-gray-300">{{ label }}</Label>
            <select
              :id="field"
              v-model="form[field]"
              class="w-full border rounded-md p-2 bg-background text-foreground border-input focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              <option value="">Select...</option>
              <option v-for="option in options" :key="option" :value="option" class="capitalize">
                {{ formatOption(option) }}
              </option>
            </select>
            <p v-if="form.errors[field]" class="text-sm text-destructive">
              {{ form.errors[field] }}
            </p>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex items-center gap-4">
        <Button
          type="submit"
          :disabled="form.processing || charCount > 160"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 transition-colors"
        >
          <svg v-if="form.processing" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
          <span>{{ form.processing ? 'Saving...' : 'Save Template' }}</span>
        </Button>
        <Transition
          enter-active-class="transition ease-in-out duration-300"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out duration-300"
          leave-to-class="opacity-0"
        >
          <p v-show="form.recentlySuccessful" class="text-sm text-muted-foreground">
            Template saved successfully.
          </p>
        </Transition>
      </div>
    </form>
  </Card>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';
import { defineProps, defineEmits } from 'vue';
import { Card } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
  form: Object,
  recentlySuccessful: Boolean,
});
const emit = defineEmits(['submit']);

const fields = {
  acceptance: 'Acceptance',
  ontime: 'On-Time',
  greenzone: 'Green Zone',
  severe_alerts: 'Severe Alerts',
};

const options = ['good', 'bad', 'minor_improvement'];

const placeholders = [
  { value: '{driver_first_name}', label: 'First Name', description: "Driver's first name" },
  { value: '{driver_last_name}', label: 'Last Name', description: "Driver's last name" },
  { value: '{driver_ontime_score}', label: 'On-Time %', description: "Driver's on-time score percentage" },
  { value: '{driver_acceptance_score}', label: 'Acceptance %', description: "Driver's acceptance score percentage" },
  { value: '{driver_greenzone_score}', label: 'Green Zone', description: "Driver's greenzone score number" },
  { value: '{driver_severe_alerts}', label: 'Severe Alerts', description: "Driver's severe alerts number" },
  { value: '{company_avg_ontime}', label: 'Avg On-Time %', description: "Company's average on-time score percentage" },
  { value: '{company_avg_acceptance}', label: 'Avg Acceptance %', description: "Company's average acceptance score percentage" },
  { value: '{company_avg_greenzone}', label: 'Avg Green Zone', description: "Company's average greenzone score number" },
];

const editableDiv = ref(null);
const charCount = ref(0);

// Format dropdown options
const formatOption = (option) =>
  option.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

// Insert placeholder
const insertPlaceholder = (value) => {
  const span = document.createElement('span');
  const label = placeholders.find(p => p.value === value)?.label || value;
  
  span.innerHTML = `<span class='placeholder-token'>&lt;${label}&gt;</span>`;
  span.contentEditable = "false";
  span.dataset.placeholder = value;
  span.className = "placeholder-token";

  const selection = window.getSelection();
  if (!selection || !editableDiv.value) return;

  const range = selection.getRangeAt(0);
  range.deleteContents();
  range.insertNode(span);
  range.collapse(false);

  selection.removeAllRanges();
  selection.addRange(range);

  syncMessageFromContent();
  updateCharCount();
};

// Initial render
onMounted(() => {
  renderInitialMessage();
});

const renderInitialMessage = () => {
  const message = props.form.coaching_message || '';
  if (!editableDiv.value) return;

  const html = message.replace(/{[^}]+}/g, (match) => {
    const label = placeholders.find(p => p.value === match)?.label || match;
    return `<span class="placeholder-token" contenteditable="false" data-placeholder="${match}">&lt;${label}&gt;</span>`;
  });

  editableDiv.value.innerHTML = html;
  updateCharCount();
};

// Sync content to form
const syncMessageFromContent = () => {
  const div = editableDiv.value;
  if (!div) return;

  let result = '';
  for (const node of div.childNodes) {
    if (node.nodeType === Node.TEXT_NODE) {
      result += node.textContent;
    } else if (node.nodeType === Node.ELEMENT_NODE && node.classList.contains('placeholder-token')) {
      result += node.dataset.placeholder;
    } else if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'BR') {
      result += '\n';
    }
  }

  props.form.coaching_message = result;
};

// Handle typing
const handleInput = () => {
  syncMessageFromContent();
  updateCharCount();
};

// Handle paste
const handlePaste = (e) => {
  const text = e.clipboardData.getData('text/plain');
  document.execCommand('insertText', false, text);
};

// Count characters + placeholders as 5 chars
const updateCharCount = () => {
  const message = props.form.coaching_message || '';
  const matches = message.match(/{[^}]+}/g) || [];
  const placeholderCount = matches.length;
  charCount.value = message.replace(/{[^}]+}/g, '').length + (placeholderCount * 5);
};

watch(() => props.form.coaching_message, updateCharCount);
</script>

<style scoped>
.placeholder-token {
  @apply inline-block bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 rounded px-1.5 py-0.5 text-xs font-mono mr-1 mb-1 align-middle whitespace-nowrap;
}
</style>