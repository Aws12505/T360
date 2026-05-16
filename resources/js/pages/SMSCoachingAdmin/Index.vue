<script setup>
import { computed, watch, ref, nextTick } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { TrendingUp, TrendingDown, ChevronDown } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Button,
    Input,
    Label,
    Badge,
    Separator,
    Textarea,
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
    AlertDialog,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogAction,
    AlertDialogCancel,
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
    SelectValue,
    Checkbox,
} from '@/components/ui';
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover';
import { useToast } from '@/components/ui/toast/use-toast';

const props = defineProps({
    permissions: Array,
    tenants: Array,
    metrics: Array,
    globalMessages: Object,
    globalThresholds: Object,
    messageOverrides: Array,
    thresholdOverrides: Array,
    placeholders: Array,
});

const breadcrumbs = computed(() => [
    { title: 'Admin Dashboard', href: route('admin.dashboard') },
    { title: 'SMS Coaching', href: route('admin.sms-coaching.index') },
]);

const statusOptions = [
    { value: 'good', label: 'Good' },
    { value: 'minor_improvement', label: 'Needs Improvement' },
    { value: 'bad', label: 'Bad' },
];

const thresholdMetrics = computed(() =>
    (props.metrics || []).filter((metric) => metric.thresholds)
);

const globalMessagesForm = useForm({
    messages: JSON.parse(JSON.stringify(props.globalMessages || {})),
});

const globalThresholdsForm = useForm({
    thresholds: JSON.parse(JSON.stringify(props.globalThresholds || {})),
});

const messageOverrideForm = useForm({
    tenant_ids: [],
    metric_key: 'general',
    status: '',
    message: '',
});

const thresholdOverrideForm = useForm({
    tenant_ids: [],
    metric_key: '',
    good: '',
    minor_improvement: '',
    bad: '',
});

const { toast } = useToast();

const placeholderQuery = ref('');
const activeField = ref(null);
const activeTarget = ref(null);
const pendingDelete = ref(null);
const messageTenantsOpen = ref(false);
const thresholdTenantsOpen = ref(false);

watch(
    () => messageOverrideForm.metric_key,
    (metric) => {
        if (metric === 'general') {
            messageOverrideForm.status = '';
        } else if (!messageOverrideForm.status) {
            messageOverrideForm.status = 'good';
        }
    }
);

const submitGlobalMessages = () => {
    globalMessagesForm.post(route('admin.sms-coaching.messages.global'), {
        preserveScroll: true,
        onSuccess: () => toast({ title: 'Saved', description: 'Global messages updated.' }),
    });
};

const submitGlobalThresholds = () => {
    globalThresholdsForm.post(route('admin.sms-coaching.thresholds.global'), {
        preserveScroll: true,
        onSuccess: () => toast({ title: 'Saved', description: 'Global thresholds updated.' }),
    });
};

const submitMessageOverride = () => {
    messageOverrideForm.post(route('admin.sms-coaching.messages.overrides'), {
        preserveScroll: true,
        onSuccess: () => {
            messageOverrideForm.reset('message');
            toast({ title: 'Saved', description: 'Message override saved.' });
        },
    });
};

const submitThresholdOverride = () => {
    thresholdOverrideForm.post(route('admin.sms-coaching.thresholds.overrides'), {
        preserveScroll: true,
        onSuccess: () => {
            thresholdOverrideForm.reset('good', 'minor_improvement', 'bad');
            toast({ title: 'Saved', description: 'Threshold override saved.' });
        },
    });
};

const confirmDelete = (type, override) => {
    pendingDelete.value = { type, override };
};

const executePendingDelete = () => {
    if (!pendingDelete.value) return;
    const { type, override } = pendingDelete.value;
    const routeName = type === 'message'
        ? 'admin.sms-coaching.messages.overrides.delete'
        : 'admin.sms-coaching.thresholds.overrides.delete';
    const param = type === 'message' ? { message: override.id } : { threshold: override.id };
    router.delete(route(routeName, param), {
        preserveScroll: true,
        onSuccess: () => toast({
            title: 'Removed',
            description: `${type === 'message' ? 'Message' : 'Threshold'} override removed.`,
        }),
    });
    pendingDelete.value = null;
};

// Tenant multi-select helpers — use div+click pattern to avoid double-toggle from label+button
const toggleTenant = (form, id) => {
    const idx = form.tenant_ids.indexOf(id);
    idx === -1 ? form.tenant_ids.push(id) : form.tenant_ids.splice(idx, 1);
};

const tenantsLabel = (ids) => {
    if (!ids?.length) return 'Select tenants...';
    if (ids.length === 1) {
        return (props.tenants || []).find((t) => t.id === ids[0])?.name ?? '1 tenant';
    }
    return `${ids.length} tenants selected`;
};

// Character count helpers
const charCount = (val) => (val || '').length;
const charCountClass = (count) =>
    count <= 160
        ? 'text-green-600 dark:text-green-400'
        : count <= 320
        ? 'text-amber-600 dark:text-amber-400'
        : 'text-red-600 dark:text-red-400';
const charSegments = (count) => (count <= 160 ? 1 : count <= 320 ? 2 : Math.ceil(count / 153));

// Status badge variant
const statusVariant = (status) =>
    status === 'good' ? 'success'
    : status === 'minor_improvement' ? 'warning'
    : status === 'bad' ? 'destructive'
    : 'secondary';

const formatMetric = (key) => {
    const metric = (props.metrics || []).find((item) => item.key === key);
    return metric ? metric.label : key;
};

const formatStatus = (value) => {
    if (!value) return 'General';
    const found = statusOptions.find((option) => option.value === value);
    return found ? found.label : value;
};

// Placeholder groups with search
const placeholderGroups = computed(() => {
    const query = placeholderQuery.value.trim().toLowerCase();
    const items = (props.placeholders || []).filter((ph) => {
        const value = (ph.value || '').toLowerCase();
        const label = (ph.label || '').toLowerCase();
        return !query || value.includes(query) || label.includes(query);
    });

    const isDriver = (ph) => (ph.value || '').startsWith('{driver_');
    const isCompany = (ph) => (ph.value || '').startsWith('{company_');
    const isThreshold = (ph) => (ph.value || '').startsWith('{threshold_');
    const isDate = (ph) => (ph.value || '').startsWith('{date_');

    const groups = [
        { key: 'driver', label: 'Driver Metrics', items: items.filter(isDriver) },
        { key: 'company', label: 'Company Averages', items: items.filter(isCompany) },
        { key: 'thresholds', label: 'Thresholds', items: items.filter(isThreshold) },
        { key: 'date', label: 'Date Range', items: items.filter(isDate) },
    ];

    const used = new Set(groups.flatMap((g) => g.items.map((i) => i.value)));
    const otherItems = items.filter((i) => !used.has(i.value));
    if (otherItems.length) groups.push({ key: 'other', label: 'Other', items: otherItems });

    return groups.filter((g) => g.items.length > 0);
});

const setActiveField = (field, event) => {
    activeField.value = field;
    activeTarget.value = event?.target ?? null;
};

const activeFieldLabel = computed(() => {
    if (!activeField.value) return 'Click a message field to activate.';
    if (activeField.value.type === 'override') return 'Tenant Override Message';
    if (activeField.value.metricKey === 'general') return 'General Message';
    return `${formatMetric(activeField.value.metricKey)} · ${formatStatus(activeField.value.statusKey)}`;
});

// Get the current active message text for preview
const activeMessageText = computed(() => {
    if (!activeField.value) return '';
    if (activeField.value.type === 'global') {
        return globalMessagesForm.messages?.[activeField.value.metricKey]?.[activeField.value.statusKey] || '';
    }
    if (activeField.value.type === 'override') {
        return messageOverrideForm.message || '';
    }
    return '';
});

// Render the message with {variable} patterns highlighted as styled spans
const activePreviewHtml = computed(() => {
    const text = activeMessageText.value;
    if (!text) return '';
    const escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\n/g, '<br>');
    return escaped.replace(
        /\{[^}]+\}/g,
        (match) =>
            `<mark class="inline-flex items-center rounded bg-primary/15 px-1 py-0.5 font-mono text-xs font-medium text-primary">${match}</mark>`
    );
});

const setMessageValue = (field, nextValue) => {
    if (!field) return;
    if (field.type === 'global') {
        if (!globalMessagesForm.messages[field.metricKey]) {
            globalMessagesForm.messages[field.metricKey] = {};
        }
        globalMessagesForm.messages[field.metricKey][field.statusKey] = nextValue;
        return;
    }
    if (field.type === 'override') {
        messageOverrideForm.message = nextValue;
    }
};

const insertPlaceholder = (placeholderValue) => {
    if (!activeTarget.value || !activeField.value) return;

    const target = activeTarget.value;
    const value = target.value ?? '';
    const start = target.selectionStart ?? value.length;
    const end = target.selectionEnd ?? value.length;
    const nextValue = value.slice(0, start) + placeholderValue + value.slice(end);

    setMessageValue(activeField.value, nextValue);

    nextTick(() => {
        target.focus();
        const cursor = start + placeholderValue.length;
        target.selectionStart = cursor;
        target.selectionEnd = cursor;
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :permissions="permissions">

        <Head title="SMS Coaching" />

        <div class="w-full max-w-6xl space-y-6">
            <div class="space-y-2">
                <h1 class="text-2xl font-bold text-foreground">SMS Coaching</h1>
                <p class="text-sm text-muted-foreground">
                    Manage global messages and thresholds, then add tenant-specific overrides when needed.
                </p>
            </div>

            <Tabs default-value="messages" class="space-y-6">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="messages">Messages</TabsTrigger>
                    <TabsTrigger value="thresholds">Thresholds</TabsTrigger>
                    <TabsTrigger value="overrides">Overrides</TabsTrigger>
                </TabsList>

                <!-- ─── MESSAGES TAB ─── -->
                <TabsContent value="messages">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_288px]">
                        <div class="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Global Messages</CardTitle>
                                    <p class="text-sm text-muted-foreground">
                                        SMS segments: 1 segment up to 160 chars, 2 for 161–320, 3+ beyond 320.
                                    </p>
                                </CardHeader>
                                <CardContent class="space-y-6">
                                    <!-- General message -->
                                    <div class="space-y-2">
                                        <Label>General Message</Label>
                                        <Textarea
                                            v-model="globalMessagesForm.messages.general.general"
                                            rows="3"
                                            placeholder="General message sent before thresholds"
                                            @focus="setActiveField({ type: 'global', metricKey: 'general', statusKey: 'general' }, $event)"
                                        />
                                        <p class="text-right text-xs"
                                            :class="charCountClass(charCount(globalMessagesForm.messages.general.general))">
                                            {{ charCount(globalMessagesForm.messages.general.general) }} chars ·
                                            {{ charSegments(charCount(globalMessagesForm.messages.general.general)) }} segment(s)
                                        </p>
                                    </div>

                                    <Separator />

                                    <!-- Threshold-based metric messages -->
                                    <div v-for="(metric, index) in thresholdMetrics" :key="metric.key"
                                        class="space-y-4">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <h3 class="text-base font-semibold text-foreground">
                                                {{ metric.label }} Messages
                                            </h3>
                                            <div class="flex items-center gap-2">
                                                <Badge v-if="metric.direction === 'high'" variant="outline"
                                                    class="gap-1">
                                                    <TrendingUp class="h-3 w-3" />
                                                    Higher is better
                                                </Badge>
                                                <Badge v-else-if="metric.direction === 'low'" variant="outline"
                                                    class="gap-1">
                                                    <TrendingDown class="h-3 w-3" />
                                                    Lower is better
                                                </Badge>
                                                <Badge variant="outline">Threshold-based</Badge>
                                            </div>
                                        </div>
                                        <div class="grid gap-4 md:grid-cols-3">
                                            <div v-for="status in statusOptions" :key="status.value"
                                                class="space-y-2">
                                                <Badge :variant="statusVariant(status.value)">
                                                    {{ status.label }}
                                                </Badge>
                                                <Textarea
                                                    v-model="globalMessagesForm.messages[metric.key][status.value]"
                                                    rows="3"
                                                    :placeholder="`${status.label} message`"
                                                    @focus="setActiveField({ type: 'global', metricKey: metric.key, statusKey: status.value }, $event)"
                                                />
                                                <p class="text-right text-xs"
                                                    :class="charCountClass(charCount(globalMessagesForm.messages[metric.key][status.value]))">
                                                    {{ charCount(globalMessagesForm.messages[metric.key][status.value]) }} chars ·
                                                    {{ charSegments(charCount(globalMessagesForm.messages[metric.key][status.value])) }} segment(s)
                                                </p>
                                            </div>
                                        </div>
                                        <Separator v-if="index < thresholdMetrics.length - 1" class="mt-2" />
                                    </div>

                                    <div class="flex items-center gap-3 pt-2">
                                        <Button type="button" @click="submitGlobalMessages"
                                            :disabled="globalMessagesForm.processing">
                                            Save Global Messages
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Sticky placeholder sidebar -->
                        <div class="sticky top-6 self-start">
                            <Card class="overflow-hidden">
                                <CardHeader class="space-y-1 pb-3">
                                    <CardTitle class="text-sm">Insert Placeholder</CardTitle>
                                    <p class="text-xs text-muted-foreground">
                                        {{ activeFieldLabel }}
                                    </p>
                                </CardHeader>
                                <CardContent class="space-y-4 pt-0">
                                    <Input v-model="placeholderQuery" placeholder="Search placeholders..."
                                        class="h-8 text-sm" />

                                    <!-- Scrollable placeholder list -->
                                    <div class="max-h-[52vh] overflow-y-auto space-y-4 pr-1">
                                        <div v-for="group in placeholderGroups" :key="group.key"
                                            class="space-y-1.5">
                                            <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">
                                                {{ group.label }}
                                            </p>
                                            <div class="space-y-1">
                                                <button
                                                    v-for="item in group.items"
                                                    :key="item.value"
                                                    type="button"
                                                    class="group flex w-full items-start gap-2 rounded-md border border-border/50 bg-muted/20 px-2.5 py-2 text-left transition-colors hover:border-primary/40 hover:bg-primary/5"
                                                    @click="insertPlaceholder(item.value)"
                                                >
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate text-xs font-medium text-foreground">
                                                            {{ item.label }}
                                                        </p>
                                                        <p class="mt-0.5 truncate font-mono text-[10px] text-muted-foreground">
                                                            {{ item.value }}
                                                        </p>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Live preview with variable highlighting -->
                                    <template v-if="activeMessageText">
                                        <Separator />
                                        <div class="space-y-1.5">
                                            <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">
                                                Preview
                                            </p>
                                            <div class="rounded-md border bg-muted/20 px-3 py-2 text-xs leading-relaxed text-foreground"
                                                v-html="activePreviewHtml" />
                                        </div>
                                    </template>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <!-- ─── THRESHOLDS TAB ─── -->
                <TabsContent value="thresholds">
                    <Card>
                        <CardHeader>
                            <CardTitle>Global Thresholds</CardTitle>
                            <p class="text-sm text-muted-foreground">
                                Set the numeric cutoffs that determine which coaching message each driver receives.
                            </p>
                        </CardHeader>
                        <CardContent class="space-y-8">
                            <div v-for="(metric, index) in thresholdMetrics" :key="metric.key" class="space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="text-base font-semibold text-foreground">{{ metric.label }}</h3>
                                    <div class="flex items-center gap-2">
                                        <Badge v-if="metric.direction === 'high'" variant="outline" class="gap-1">
                                            <TrendingUp class="h-3 w-3" />
                                            Higher is better
                                        </Badge>
                                        <Badge v-else-if="metric.direction === 'low'" variant="outline" class="gap-1">
                                            <TrendingDown class="h-3 w-3" />
                                            Lower is better
                                        </Badge>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label class="flex items-center gap-1.5">
                                            <Badge variant="success">Good</Badge>
                                        </Label>
                                        <Input v-model="globalThresholdsForm.thresholds[metric.key].good"
                                            type="number" step="0.01" placeholder="e.g. 90" />
                                        <p class="text-xs text-muted-foreground">
                                            Drivers at or above this value get the Good message.
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="flex items-center gap-1.5">
                                            <Badge variant="warning">Needs Improvement</Badge>
                                        </Label>
                                        <Input
                                            v-model="globalThresholdsForm.thresholds[metric.key].minor_improvement"
                                            type="number" step="0.01" placeholder="e.g. 70" />
                                        <p class="text-xs text-muted-foreground">
                                            Below Good but above Bad — Needs Improvement message.
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="flex items-center gap-1.5">
                                            <Badge variant="destructive">Bad</Badge>
                                        </Label>
                                        <Input v-model="globalThresholdsForm.thresholds[metric.key].bad"
                                            type="number" step="0.01" placeholder="e.g. 50" />
                                        <p class="text-xs text-muted-foreground">
                                            Drivers below this value get the Bad message.
                                        </p>
                                    </div>
                                </div>

                                <Separator v-if="index < thresholdMetrics.length - 1" />
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <Button type="button" @click="submitGlobalThresholds"
                                    :disabled="globalThresholdsForm.processing">
                                    Save Global Thresholds
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- ─── OVERRIDES TAB ─── -->
                <TabsContent value="overrides">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_288px]">
                        <div class="space-y-6">

                            <!-- Message Overrides -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Tenant Message Overrides</CardTitle>
                                    <p class="text-sm text-muted-foreground">
                                        Overrides follow the same SMS segment rules (160 / 320 characters).
                                    </p>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <!-- Row 1: Tenants + Metric -->
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label>Tenants</Label>
                                            <Popover v-model:open="messageTenantsOpen">
                                                <PopoverTrigger as-child>
                                                    <Button type="button" variant="outline"
                                                        class="w-full justify-between font-normal">
                                                        <span class="truncate">
                                                            {{ tenantsLabel(messageOverrideForm.tenant_ids) }}
                                                        </span>
                                                        <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-72 p-2">
                                                    <div class="max-h-56 overflow-y-auto space-y-0.5">
                                                        <!--
                                                            Use div+click (not label+button) to avoid the browser
                                                            double-firing click on both label and the button inside it.
                                                            The Checkbox is display-only (pointer-events-none).
                                                        -->
                                                        <div
                                                            v-for="tenant in tenants"
                                                            :key="tenant.id"
                                                            class="flex cursor-pointer select-none items-center gap-2.5 rounded px-2 py-2 text-sm hover:bg-accent"
                                                            @click="toggleTenant(messageOverrideForm, tenant.id)"
                                                        >
                                                            <div class="pointer-events-none flex items-center">
                                                                <Checkbox
                                                                    :checked="messageOverrideForm.tenant_ids.includes(tenant.id)"
                                                                />
                                                            </div>
                                                            {{ tenant.name }}
                                                        </div>
                                                    </div>
                                                </PopoverContent>
                                            </Popover>
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Metric</Label>
                                            <Select v-model="messageOverrideForm.metric_key">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select metric..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="metric in metrics" :key="metric.key"
                                                        :value="metric.key">
                                                        {{ metric.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <!-- Row 2: Status + Message -->
                                    <div class="grid gap-4 md:grid-cols-[180px_1fr]">
                                        <div class="space-y-2">
                                            <Label>Status</Label>
                                            <Select v-model="messageOverrideForm.status"
                                                :disabled="messageOverrideForm.metric_key === 'general'">
                                                <SelectTrigger
                                                    :disabled="messageOverrideForm.metric_key === 'general'">
                                                    <SelectValue placeholder="Select status..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="">General</SelectItem>
                                                    <SelectItem v-for="status in statusOptions"
                                                        :key="status.value" :value="status.value">
                                                        {{ status.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Message</Label>
                                            <Textarea v-model="messageOverrideForm.message" rows="3"
                                                placeholder="Override message"
                                                @focus="setActiveField({ type: 'override' }, $event)" />
                                            <p class="text-right text-xs"
                                                :class="charCountClass(charCount(messageOverrideForm.message))">
                                                {{ charCount(messageOverrideForm.message) }} chars ·
                                                {{ charSegments(charCount(messageOverrideForm.message)) }} segment(s)
                                            </p>
                                        </div>
                                    </div>

                                    <Button type="button" @click="submitMessageOverride"
                                        :disabled="messageOverrideForm.processing">
                                        Save Message Override
                                    </Button>

                                    <Separator />

                                    <!-- Existing message overrides list -->
                                    <div v-if="messageOverrides?.length" class="space-y-3">
                                        <div v-for="override in messageOverrides" :key="override.id"
                                            class="flex flex-col gap-2 rounded-lg border-l-4 border border-border p-3 md:flex-row md:items-start md:justify-between"
                                            :class="{
                                                'border-l-green-500': override.status === 'good',
                                                'border-l-amber-500': override.status === 'minor_improvement',
                                                'border-l-red-500': override.status === 'bad',
                                                'border-l-blue-400': !override.status,
                                            }">
                                            <div class="min-w-0 space-y-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-semibold text-foreground">
                                                        {{ override.tenant_name }}
                                                    </span>
                                                    <Badge variant="outline" class="text-xs">
                                                        {{ formatMetric(override.metric_key) }}
                                                    </Badge>
                                                    <Badge :variant="statusVariant(override.status)" class="text-xs">
                                                        {{ formatStatus(override.status) }}
                                                    </Badge>
                                                </div>
                                                <p class="text-xs text-muted-foreground line-clamp-2">
                                                    {{ override.message }}
                                                </p>
                                            </div>
                                            <Button variant="destructive" size="sm" type="button" class="shrink-0"
                                                @click="confirmDelete('message', override)">
                                                Remove
                                            </Button>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm text-muted-foreground">
                                        No message overrides yet.
                                    </p>
                                </CardContent>
                            </Card>

                            <!-- Threshold Overrides -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Tenant Threshold Overrides</CardTitle>
                                    <p class="text-sm text-muted-foreground">
                                        Override the global thresholds for specific tenants.
                                    </p>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <!-- Row 1: Tenants + Metric -->
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label>Tenants</Label>
                                            <Popover v-model:open="thresholdTenantsOpen">
                                                <PopoverTrigger as-child>
                                                    <Button type="button" variant="outline"
                                                        class="w-full justify-between font-normal">
                                                        <span class="truncate">
                                                            {{ tenantsLabel(thresholdOverrideForm.tenant_ids) }}
                                                        </span>
                                                        <ChevronDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-72 p-2">
                                                    <div class="max-h-56 overflow-y-auto space-y-0.5">
                                                        <div
                                                            v-for="tenant in tenants"
                                                            :key="tenant.id"
                                                            class="flex cursor-pointer select-none items-center gap-2.5 rounded px-2 py-2 text-sm hover:bg-accent"
                                                            @click="toggleTenant(thresholdOverrideForm, tenant.id)"
                                                        >
                                                            <div class="pointer-events-none flex items-center">
                                                                <Checkbox
                                                                    :checked="thresholdOverrideForm.tenant_ids.includes(tenant.id)"
                                                                />
                                                            </div>
                                                            {{ tenant.name }}
                                                        </div>
                                                    </div>
                                                </PopoverContent>
                                            </Popover>
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Metric</Label>
                                            <Select v-model="thresholdOverrideForm.metric_key">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select metric..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="metric in thresholdMetrics"
                                                        :key="metric.key" :value="metric.key">
                                                        {{ metric.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <!-- Row 2: Good / Needs Improvement / Bad -->
                                    <div class="grid gap-4 md:grid-cols-3">
                                        <div class="space-y-2">
                                            <Label class="flex items-center gap-1.5">
                                                <Badge variant="success">Good</Badge>
                                            </Label>
                                            <Input v-model="thresholdOverrideForm.good" type="number"
                                                step="0.01" placeholder="e.g. 90" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="flex items-center gap-1.5">
                                                <Badge variant="warning">Needs Improvement</Badge>
                                            </Label>
                                            <Input v-model="thresholdOverrideForm.minor_improvement" type="number"
                                                step="0.01" placeholder="e.g. 70" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="flex items-center gap-1.5">
                                                <Badge variant="destructive">Bad</Badge>
                                            </Label>
                                            <Input v-model="thresholdOverrideForm.bad" type="number"
                                                step="0.01" placeholder="e.g. 50" />
                                        </div>
                                    </div>

                                    <Button type="button" @click="submitThresholdOverride"
                                        :disabled="thresholdOverrideForm.processing">
                                        Save Threshold Override
                                    </Button>

                                    <Separator />

                                    <!-- Existing threshold overrides list -->
                                    <div v-if="thresholdOverrides?.length" class="space-y-3">
                                        <div v-for="override in thresholdOverrides" :key="override.id"
                                            class="flex flex-col gap-2 rounded-lg border-l-4 border-l-primary border border-border p-3 md:flex-row md:items-center md:justify-between">
                                            <div class="space-y-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-semibold text-foreground">
                                                        {{ override.tenant_name }}
                                                    </span>
                                                    <Badge variant="outline" class="text-xs">
                                                        {{ formatMetric(override.metric_key) }}
                                                    </Badge>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
                                                    <Badge variant="success" class="text-xs">Good</Badge>
                                                    <span>{{ override.good }}</span>
                                                    <span class="opacity-40">·</span>
                                                    <Badge variant="warning" class="text-xs">Needs Improvement</Badge>
                                                    <span>{{ override.minor_improvement }}</span>
                                                    <span class="opacity-40">·</span>
                                                    <Badge variant="destructive" class="text-xs">Bad</Badge>
                                                    <span>{{ override.bad }}</span>
                                                </div>
                                            </div>
                                            <Button variant="destructive" size="sm" type="button" class="shrink-0"
                                                @click="confirmDelete('threshold', override)">
                                                Remove
                                            </Button>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm text-muted-foreground">
                                        No threshold overrides yet.
                                    </p>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Sticky placeholder sidebar (Overrides tab) -->
                        <div class="sticky top-6 self-start">
                            <Card class="overflow-hidden">
                                <CardHeader class="space-y-1 pb-3">
                                    <CardTitle class="text-sm">Insert Placeholder</CardTitle>
                                    <p class="text-xs text-muted-foreground">
                                        {{ activeFieldLabel }}
                                    </p>
                                </CardHeader>
                                <CardContent class="space-y-4 pt-0">
                                    <Input v-model="placeholderQuery" placeholder="Search placeholders..."
                                        class="h-8 text-sm" />

                                    <div class="max-h-[52vh] overflow-y-auto space-y-4 pr-1">
                                        <div v-for="group in placeholderGroups" :key="group.key"
                                            class="space-y-1.5">
                                            <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">
                                                {{ group.label }}
                                            </p>
                                            <div class="space-y-1">
                                                <button
                                                    v-for="item in group.items"
                                                    :key="item.value"
                                                    type="button"
                                                    class="group flex w-full items-start gap-2 rounded-md border border-border/50 bg-muted/20 px-2.5 py-2 text-left transition-colors hover:border-primary/40 hover:bg-primary/5"
                                                    @click="insertPlaceholder(item.value)"
                                                >
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate text-xs font-medium text-foreground">
                                                            {{ item.label }}
                                                        </p>
                                                        <p class="mt-0.5 truncate font-mono text-[10px] text-muted-foreground">
                                                            {{ item.value }}
                                                        </p>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <template v-if="activeMessageText">
                                        <Separator />
                                        <div class="space-y-1.5">
                                            <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">
                                                Preview
                                            </p>
                                            <div class="rounded-md border bg-muted/20 px-3 py-2 text-xs leading-relaxed text-foreground"
                                                v-html="activePreviewHtml" />
                                        </div>
                                    </template>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>
        </div>

        <!-- Shared delete confirmation dialog -->
        <AlertDialog :open="!!pendingDelete" @update:open="(v) => { if (!v) pendingDelete = null }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Remove Override</AlertDialogTitle>
                    <AlertDialogDescription>
                        Remove the
                        <strong>{{ formatMetric(pendingDelete?.override?.metric_key) }}</strong>
                        <template v-if="pendingDelete?.override?.status">
                            · <strong>{{ formatStatus(pendingDelete?.override?.status) }}</strong>
                        </template>
                        override for
                        <strong>{{ pendingDelete?.override?.tenant_name }}</strong>?
                        This cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="pendingDelete = null">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                        @click="executePendingDelete">
                        Remove
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AppLayout>
</template>
