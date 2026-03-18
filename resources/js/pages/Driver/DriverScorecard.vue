<script setup lang="ts">
import { ref, computed } from "vue"
import { Head, router } from "@inertiajs/vue3"
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from "@/layouts/AppLayout.vue"
import TimePeriodTabs from "@/components/summary/TimePeriodTabs.vue"
import DriverScoreTable from "@/components/driver/DriverScoreTable.vue"
import { Button } from "@/components/ui/button"
import Icon from "@/components/Icon.vue"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { toPng } from "html-to-image"

const props = defineProps({
    driversOverallPerformance: {
        type: Object,
        default: () => ({ drivers: [] })
    },
    tenantSlug: String,
    dateFilter: String,
    dateRange: Object,
    permissions: Array
})

const scorecardRef = ref(null)

const drivers = computed(() => {
    return props.driversOverallPerformance?.drivers ?? []
})

const hasDrivers = computed(() => drivers.value.length > 0)

const handleTimePeriodChange = (filter: string) => {
    if (filter === 'custom') {
        showCustomDialog.value = true;
        return;
    }
    router.visit(route("driver.scorecard", {
        tenantSlug: props.tenantSlug,
        dateFilter: filter
    }), {
        preserveScroll: true,
        only: ["driversOverallPerformance", "dateFilter", "dateRange"]
    })
}

const exportScorecard = async () => {

    if (!scorecardRef.value || !hasDrivers.value) return

    const el = scorecardRef.value

    const originalOverflow = el.style.overflow
    const originalWidth = el.style.width

    try {

        el.style.overflow = "visible"
        el.style.width = "max-content"

        await new Promise(r => setTimeout(r, 50))

        const dataUrl = await toPng(el, {
            pixelRatio: 2,
            cacheBust: true
        })

        const link = document.createElement("a")
        link.download = "driver-scorecard.png"
        link.href = dataUrl
        link.click()

    }
    finally {

        el.style.overflow = originalOverflow
        el.style.width = originalWidth

    }

}
const customStartDate = ref<string | null>(null);
const customEndDate = ref<string | null>(null);
const showCustomDialog = ref(false);
const applyCustomRange = () => {
    if (!customStartDate.value || !customEndDate.value) return;

    showCustomDialog.value = false;

    router.visit(route("driver.scorecard", {
        tenantSlug: props.tenantSlug,
        dateFilter: 'custom'
    }), {
        preserveScroll: true,
        only: ["driversOverallPerformance", "dateFilter", "dateRange"]
    })
};
</script>

<template>
    <AppLayout :tenantSlug="tenantSlug" :permissions="permissions">

        <Head title="Driver Scorecard" />

        <div class="container mx-auto space-y-6 p-6">

            <!-- Header -->

            <div class="flex items-center justify-between">

                <h1 class="text-2xl font-bold flex items-center gap-2">
                    <Icon name="users" />
                    Driver Performance Scorecard
                </h1>

                <Button variant="outline" @click="exportScorecard" :disabled="!hasDrivers">
                    <Icon name="camera" class="mr-2 h-4 w-4" />
                    Capture Snapshot
                </Button>

            </div>

            <!-- Date Filter -->

            <TimePeriodTabs :activeTabId="dateFilter" :dateRangeText="dateRange?.label"
                :weekNumber="dateRange?.weekNumber" :startWeekNumber="dateRange?.startWeekNumber"
                :endWeekNumber="dateRange?.endWeekNumber" :year="dateRange?.year"
                @tab-change="handleTimePeriodChange" />

            <!-- Scorecards -->

            <div ref="scorecardRef">
                <DriverScoreTable :drivers="drivers" />
            </div>

        </div>
        <Dialog v-model:open="showCustomDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Select Custom Date Range</DialogTitle>
                    <DialogDescription>
                        Choose a start and end date for your report.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div>
                        <Label>Start Date</Label>
                        <Input type="date" v-model="customStartDate" />
                    </div>

                    <div>
                        <Label>End Date</Label>
                        <Input type="date" v-model="customEndDate" />
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
