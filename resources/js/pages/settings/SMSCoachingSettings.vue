<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { MessageSquare, TrendingUp, Shield } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import { Badge, Card, CardHeader, CardTitle, CardContent } from '@/components/ui';
import { useToast } from '@/components/ui/toast/use-toast';

const props = defineProps({
    settings: Object,
    metrics: Array,
    tenantSlug: String,
    permissions: Array,
});

const breadcrumbs = computed(() => [
    {
        title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
        href: props.tenantSlug
            ? route('dashboard', { tenantSlug: props.tenantSlug })
            : route('admin.dashboard'),
    },
]);

const { toast } = useToast();

// Coerce all settings values to proper booleans so Switch works reliably
const form = useForm({
    settings: Object.fromEntries(
        Object.entries(props.settings || {}).map(([k, v]) => [k, Boolean(v)])
    ),
});

const groupedMetrics = computed(() => {
    const metrics = Array.isArray(props.metrics) ? props.metrics : [];
    const byKey = Object.fromEntries(metrics.map((metric) => [metric.key, metric]));

    const general = ['general'].map((key) => byKey[key]).filter(Boolean);
    const performanceKeys = ['acceptance', 'on_time', 'greenzone'];
    const performance = performanceKeys.map((key) => byKey[key]).filter(Boolean);
    const safety = metrics.filter((metric) => !['general', ...performanceKeys].includes(metric.key));

    return [
        {
            key: 'general',
            title: 'General',
            description: 'Sent first, before any threshold-based coaching messages.',
            metrics: general,
        },
        {
            key: 'performance',
            title: 'Performance',
            description: 'Acceptance, on-time, and green zone coaching messages.',
            metrics: performance,
        },
        {
            key: 'safety',
            title: 'Safety Alerts',
            description: 'Each alert can be enabled or disabled independently.',
            metrics: safety,
        },
    ].filter((group) => group.metrics.length > 0);
});

const groupIconMap = {
    general: MessageSquare,
    performance: TrendingUp,
    safety: Shield,
};

const enableAll = (group) => group.metrics.forEach((m) => { form.settings[m.key] = true; });
const disableAll = (group) => group.metrics.forEach((m) => { form.settings[m.key] = false; });

const toggleMetric = (key) => {
    form.settings[key] = !form.settings[key];
};

const submit = () => {
    form.post(route('sms-coaching-settings.update', props.tenantSlug), {
        preserveScroll: true,
        onSuccess: () => {
            toast({ title: 'Saved', description: 'SMS coaching settings updated.' });
        },
    });
};

const permissionNames = computed(() =>
    Array.isArray(props.permissions)
        ? props.permissions.map((p) => p?.name).filter(Boolean)
        : []
);

const canUpdate = computed(() => permissionNames.value.includes('sms-coaching-thresholds.update'));
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">

        <Head title="SMS Coaching" />

        <SettingsLayout :permissions="permissions">
            <div class="space-y-6">
                <HeadingSmall title="SMS Coaching"
                    description="Choose which coaching messages should be sent to drivers for your tenant." />

                <Separator />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <Card v-for="group in groupedMetrics" :key="group.key"
                            :class="group.key === 'general' ? 'lg:col-span-2' : ''">
                            <CardHeader>
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <CardTitle class="flex items-center gap-2 text-base">
                                            <component :is="groupIconMap[group.key]"
                                                class="h-4 w-4 text-muted-foreground" />
                                            {{ group.title }}
                                        </CardTitle>
                                        <p class="text-xs text-muted-foreground">{{ group.description }}</p>
                                    </div>
                                    <div v-if="canUpdate && group.metrics.length > 1"
                                        class="flex shrink-0 items-center gap-2">
                                        <Button type="button" variant="ghost" size="sm" class="h-7 text-xs"
                                            @click="enableAll(group)">
                                            Enable All
                                        </Button>
                                        <Button type="button" variant="ghost" size="sm" class="h-7 text-xs"
                                            @click="disableAll(group)">
                                            Disable All
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div v-for="metric in group.metrics" :key="metric.key"
                                        class="flex cursor-pointer items-center justify-between rounded-lg border px-4 py-3 transition-colors duration-200"
                                        :class="form.settings[metric.key]
                                            ? 'border-green-500/30 bg-green-50/5 dark:bg-green-900/5'
                                            : 'border-border'" @click="canUpdate && toggleMetric(metric.key)">
                                        <p class="select-none text-sm font-medium text-foreground">
                                            {{ metric.label }}
                                        </p>
                                        <div class="flex items-center gap-3">
                                            <Badge :variant="form.settings[metric.key] ? 'success' : 'secondary'"
                                                class="cursor-pointer select-none"
                                                @click.stop="canUpdate && toggleMetric(metric.key)">
                                                {{ form.settings[metric.key] ? 'On' : 'Off' }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div v-if="canUpdate" class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
