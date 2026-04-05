<template>
  <form @submit.prevent="submit" class="space-y-6">
    <!-- Tenant dropdown for SuperAdmin -->
    <div v-if="isSuperAdmin" class="col-span-full">
      <Label>Company Name</Label>
      <select v-model="form.tenant_id" class="select-base">
        <option :value="null" disabled>Select Company</option>
        <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
          {{ tenant.name }}
        </option>
      </select>
      <InputError :message="form.errors.tenant_id" />
    </div>

    <!-- Date/Time -->
    <div>
      <DateTimePopoverField v-model="form.date" dateLabel="Date" timeLabel="Time" />
      <InputError :message="form.errors.date" />
    </div>

    <!-- Shared fields -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <!-- Delay Type -->
      <div>
        <Label for="delay_type">Delay Type</Label>
        <select id="delay_type" v-model="form.delay_type" class="select-base">
          <option value="origin">Origin</option>
          <option value="destination">Destination</option>
        </select>
        <InputError :message="form.errors.delay_type" />
      </div>

      <!-- Load ID -->
      <div>
        <Label for="load_id">Load ID</Label>
        <Input id="load_id" v-model="form.load_id" placeholder="Optional load ID..." class="w-full" />
        <InputError :message="form.errors.load_id" />
      </div>

      <!-- Driver Name -->
      <div>
        <Label for="driver_name">Driver Name</Label>
        <Input id="driver_name" v-model="form.driver_name" placeholder="Optional..." class="w-full" />
        <InputError :message="form.errors.driver_name" />
      </div>

      <!-- Delay Reason -->
      <div>
        <Label for="delay_reason">Delay Reason</Label>
        <Input id="delay_reason" v-model="form.delay_reason" placeholder="Optional reason..." class="w-full" />
        <InputError :message="form.errors.delay_reason" />
      </div>

      <!-- Delay Duration -->
      <div class="col-span-full">
        <Label>Delay Duration</Label>
        <div class="flex items-center gap-3">
          <div class="flex-1">
            <Input type="number" min="0" v-model.number="form.delay_duration_hours" class="w-full"
              placeholder="Hours" />
            <span class="text-xs text-muted-foreground mt-1 block">Hours</span>
          </div>
          <span class="text-muted-foreground mt-[-14px]">:</span>
          <div class="flex-1">
            <Input type="number" min="0" max="59" v-model.number="form.delay_duration_minutes" class="w-full"
              placeholder="Minutes" />
            <span class="text-xs text-muted-foreground mt-1 block">Minutes</span>
          </div>
          <!-- Auto-resolved preview -->
          <div v-if="resolvedCategory" class="flex-1 rounded-md border bg-muted/30 px-3 py-2 text-sm text-center">
            <span class="block text-xs text-muted-foreground mb-0.5">Category</span>
            <span class="font-medium">{{ resolvedCategory }}</span>
          </div>
          <div v-if="resolvedPenalty !== null"
            class="flex-1 rounded-md border bg-muted/30 px-3 py-2 text-sm text-center">
            <span class="block text-xs text-muted-foreground mb-0.5">Penalty</span>
            <span class="font-medium">{{ resolvedPenalty }}</span>
          </div>
        </div>
        <InputError :message="form.errors.delay_duration_hours" />
        <InputError :message="form.errors.delay_duration_minutes" />
      </div>

      <!-- Disputed -->
      <div>
        <Label for="disputed">Disputed</Label>
        <select id="disputed" v-model="form.disputed" class="select-base">
          <option value="none">None</option>
          <option value="pending">Pending</option>
          <option value="won">Won</option>
          <option value="lost">Lost</option>
        </select>
        <InputError :message="form.errors.disputed" />
      </div>

      <!-- Driver Controllable -->
      <div>
        <Label for="driver_controllable">Driver Controllable</Label>
        <select id="driver_controllable" v-model="form.driver_controllable" class="select-base">
          <option :value="null">N/A</option>
          <option :value="true">Yes</option>
          <option :value="false">No</option>
        </select>
        <InputError :message="form.errors.driver_controllable" />
      </div>

      <!-- Carrier Controllable -->
      <div>
        <Label for="carrier_controllable">Carrier Controllable</Label>
        <select id="carrier_controllable" v-model="form.carrier_controllable" class="select-base">
          <option :value="null">N/A</option>
          <option :value="true">Yes</option>
          <option :value="false">No</option>
        </select>
        <InputError :message="form.errors.carrier_controllable" />
      </div>
    </div>

    <!-- Form Actions -->
    <div class="flex flex-col space-y-2 sm:space-y-0 sm:flex-row sm:justify-end sm:space-x-2 pt-4 border-t">
      <Button type="button" @click="emit('close')" variant="outline" class="w-full sm:w-auto">
        Cancel
      </Button>
      <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
        {{ form.id ? "Update" : "Create" }}
      </Button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Input from "@/components/ui/input/Input.vue";
import Button from "@/components/ui/button/Button.vue";
import Label from "@/components/ui/label/Label.vue";
import InputError from "@/components/ui/inputError/InputError.vue";
import DateTimePopoverField from "@/components/ui/date-time-popover-field.vue";
const props = defineProps({
  delay: { type: Object, default: null },
  tenants: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: { type: String, default: null },
});

const emit = defineEmits(["close", "success"]);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function toDatetimeLocal(val: string | null): string {
  if (!val) return "";
  if (val.includes("T")) return val.slice(0, 16);
  return val.replace(" ", "T").slice(0, 16);
}

function toBool(val: any): boolean | null {
  if (val === null || val === undefined || val === "") return null;
  if (val === true || val === 1 || val === "1") return true;
  return false;
}

// Convert stored total minutes back to hours + minutes for the form inputs
function minutesToHoursAndMinutes(
  total: number | null
): { hours: number; minutes: number } {
  if (!total) return { hours: 0, minutes: 0 };
  return {
    hours: Math.floor(total / 60),
    minutes: total % 60,
  };
}

// ─── Category / Penalty resolvers (mirrors backend logic) ─────────────────────

const CATEGORY_LABELS: Record<string, string> = {
  "1_60": "1–60 minutes late",
  "61_240": "61–240 minutes late",
  "241_600": "241–600 minutes late",
  "601_plus": "601+ minutes late",
};

function categoryFromMinutes(total: number): string {
  if (total <= 60) return "1_60";
  if (total <= 240) return "61_240";
  if (total <= 600) return "241_600";
  return "601_plus";
}

function penaltyFromCategory(cat: string): number {
  const map: Record<string, number> = {
    "1_60": 1,
    "61_240": 2,
    "241_600": 4,
    "601_plus": 8,
  };
  return map[cat] ?? 0;
}

// ─── Build form state ─────────────────────────────────────────────────────────

function buildFormData(d: any) {
  const { hours, minutes } = minutesToHoursAndMinutes(d?.delay_duration ?? null);
  return {
    id: d?.id ?? null,
    tenant_id: d?.tenant_id ?? null,
    date: toDatetimeLocal(d?.date) ?? "",
    delay_type: d?.delay_type ?? "origin",
    load_id: d?.load_id ?? "",
    driver_name: d?.driver_name ?? "",
    delay_reason: d?.delay_reason ?? "",
    delay_duration_hours: hours,
    delay_duration_minutes: minutes,
    disputed: d?.disputed ?? "none",
    driver_controllable: toBool(d?.driver_controllable),
    carrier_controllable: toBool(d?.carrier_controllable),
  };
}

const form = useForm(buildFormData(props.delay));

watch(
  () => props.delay,
  (d) => {
    const data = buildFormData(d);
    Object.keys(data).forEach((key) => {
      (form as any)[key] = (data as any)[key];
    });
    form.clearErrors();
  },
  { immediate: true }
);

// ─── Live-computed category + penalty preview ─────────────────────────────────

const totalMinutes = computed(() => {
  const h = form.delay_duration_hours ?? 0;
  const m = form.delay_duration_minutes ?? 0;
  return h * 60 + m;
});

const resolvedCategory = computed(() => {
  if (totalMinutes.value <= 0) return null;
  return CATEGORY_LABELS[categoryFromMinutes(totalMinutes.value)];
});

const resolvedPenalty = computed(() => {
  if (totalMinutes.value <= 0) return null;
  return penaltyFromCategory(categoryFromMinutes(totalMinutes.value));
});

// ─── Submit ───────────────────────────────────────────────────────────────────

function submit() {
  const isEdit = !!form.id;

  const routeName = props.isSuperAdmin
    ? isEdit
      ? "ontime.update.admin"
      : "ontime.store.admin"
    : isEdit
      ? "ontime.update"
      : "ontime.store";

  const routeParams = props.isSuperAdmin
    ? isEdit
      ? { delay: form.id }
      : {}
    : isEdit
      ? { tenantSlug: props.tenantSlug, delay: form.id }
      : { tenantSlug: props.tenantSlug };

  const method = isEdit ? "put" : "post";

  form[method](route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      emit("success");
      emit("close");
    },
  });
}
</script>

<style scoped>
.select-base {
  @apply flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background appearance-none focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50;
}
</style>
