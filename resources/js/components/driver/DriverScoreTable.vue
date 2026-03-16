<script setup lang="ts">
import { computed } from "vue"

import Icon from "@/components/Icon.vue"
import { Badge } from "@/components/ui/badge"

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from "@/components/ui/table"

type Driver = {
    driver_name: string
    acceptance_score: number
    on_time_score: number
    raw_safety_score: number
    driver_distraction: number
    speeding_violations: number
    sign_violations: number
    traffic_light_violation: number
    following_distance: number
    roadside_parking: number
}

const props = defineProps<{
    drivers: Driver[]
}>()

const hasDrivers = computed(() => props.drivers?.length > 0)

/* ---------------------------------- */
/* Ranking (sorted by safety score) */
/* ---------------------------------- */

const rankedDrivers = computed(() => {
    return [...props.drivers].sort(
        (a, b) => b.raw_safety_score - a.raw_safety_score
    )
})

/* ---------------------------------- */
/* Fleet averages */
/* ---------------------------------- */

const fleetAverage = computed(() => {

    if (!props.drivers.length) return null

    const sum = (key: keyof Driver) =>
        props.drivers.reduce((t, d) => t + (d[key] as number || 0), 0)

    const count = props.drivers.length

    return {
        acceptance: sum("acceptance_score") / count,
        onTime: sum("on_time_score") / count,
        safety: sum("raw_safety_score") / count,
        distraction: sum("driver_distraction") / count,
        speeding: sum("speeding_violations") / count,
        signs: sum("sign_violations") / count,
        lights: sum("traffic_light_violation") / count,
        following: sum("following_distance") / count,
        parking: sum("roadside_parking") / count
    }

})

/* ---------------------------------- */
/* Score coloring */
/* ---------------------------------- */

const scoreVariant = (score: number) => {
    if (score >= 90) return "success"
    if (score >= 75) return "midRange"
    if (score >= 60) return "warning"
    return "destructive"
}

const safetyVariant = (score: number) => {
    if (score >= 900) return "success"
    if (score >= 750) return "midRange"
    if (score >= 600) return "warning"
    return "destructive"
}

/* ---------------------------------- */
/* Violation heat coloring */
/* ---------------------------------- */

const violationClass = (value: number) => {

    if (value >= 3) return "text-red-600 font-semibold"
    if (value >= 1) return "text-yellow-600 font-medium"

    return "text-muted-foreground"

}

/* ---------------------------------- */
/* Formatting */
/* ---------------------------------- */

const percent = (v: number) => `${Math.round(v ?? 0)}%`
const number = (v: number) => Math.round(v ?? 0)

</script>

<template>

    <!-- EMPTY STATE -->

    <div v-if="!hasDrivers" class="flex flex-col items-center justify-center py-20 text-muted-foreground">

        <Icon name="users-x" class="h-12 w-12 mb-3 opacity-50" />

        <p class="text-lg font-medium">
            No driver performance data available
        </p>

        <p class="text-sm">
            Try selecting a different date range
        </p>

    </div>

    <!-- TABLE -->

    <div v-else class="rounded-xl border bg-background shadow-sm">

        <div class="overflow-x-auto">

            <Table class="min-w-[1100px]">

                <!-- HEADER -->

                <TableHeader>

                    <TableRow class="bg-muted/60 hover:bg-muted/60">

                        <TableHead rowspan="2" class="sticky left-0 z-30 bg-muted/60 min-w-[220px]">
                            Driver
                        </TableHead>

                        <TableHead rowspan="2" class="text-center">
                            Rank
                        </TableHead>

                        <TableHead colspan="3" class="text-center">
                            Performance
                        </TableHead>

                        <TableHead colspan="6" class="text-center">
                            Safety Violations
                        </TableHead>

                    </TableRow>

                    <TableRow class="bg-muted/40">

                        <TableHead class="text-center">Acceptance</TableHead>
                        <TableHead class="text-center">On Time</TableHead>
                        <TableHead class="text-center">Safety</TableHead>

                        <TableHead class="text-center">Distraction</TableHead>
                        <TableHead class="text-center">Speeding</TableHead>
                        <TableHead class="text-center">Signs</TableHead>
                        <TableHead class="text-center">Lights</TableHead>
                        <TableHead class="text-center">Following</TableHead>
                        <TableHead class="text-center">Parking</TableHead>

                    </TableRow>

                </TableHeader>

                <!-- BODY -->

                <TableBody>

                    <TableRow v-for="(driver, index) in rankedDrivers" :key="driver.driver_name"
                        class="odd:bg-muted/20 hover:bg-muted/40 transition-colors">

                        <TableCell class="sticky left-0 z-20 bg-background font-semibold">
                            {{ driver.driver_name }}
                        </TableCell>

                        <TableCell class="text-center font-semibold">
                            #{{ index + 1 }}
                        </TableCell>

                        <TableCell class="text-center">
                            <Badge :variant="scoreVariant(driver.acceptance_score)">
                                {{ percent(driver.acceptance_score) }}
                            </Badge>
                        </TableCell>

                        <TableCell class="text-center">
                            <Badge :variant="scoreVariant(driver.on_time_score)">
                                {{ percent(driver.on_time_score) }}
                            </Badge>
                        </TableCell>

                        <TableCell class="text-center">
                            <Badge :variant="safetyVariant(driver.raw_safety_score)">
                                {{ number(driver.raw_safety_score) }}
                            </Badge>
                        </TableCell>

                        <TableCell class="text-center" :class="violationClass(driver.driver_distraction)">
                            {{ driver.driver_distraction }}
                        </TableCell>

                        <TableCell class="text-center" :class="violationClass(driver.speeding_violations)">
                            {{ driver.speeding_violations }}
                        </TableCell>

                        <TableCell class="text-center" :class="violationClass(driver.sign_violations)">
                            {{ driver.sign_violations }}
                        </TableCell>

                        <TableCell class="text-center" :class="violationClass(driver.traffic_light_violation)">
                            {{ driver.traffic_light_violation }}
                        </TableCell>

                        <TableCell class="text-center" :class="violationClass(driver.following_distance)">
                            {{ driver.following_distance }}
                        </TableCell>

                        <TableCell class="text-center" :class="violationClass(driver.roadside_parking)">
                            {{ driver.roadside_parking }}
                        </TableCell>

                    </TableRow>

                    <!-- Fleet Average -->

                    <TableRow v-if="fleetAverage" class="bg-muted/60 font-semibold border-t">

                        <TableCell class="sticky left-0 bg-muted/60">
                            Fleet Average
                        </TableCell>

                        <TableCell></TableCell>

                        <TableCell class="text-center">
                            {{ percent(fleetAverage.acceptance) }}
                        </TableCell>

                        <TableCell class="text-center">
                            {{ percent(fleetAverage.onTime) }}
                        </TableCell>

                        <TableCell class="text-center">
                            {{ number(fleetAverage.safety) }}
                        </TableCell>

                        <TableCell class="text-center">
                            {{ number(fleetAverage.distraction) }}
                        </TableCell>

                        <TableCell class="text-center">
                            {{ number(fleetAverage.speeding) }}
                        </TableCell>

                        <TableCell class="text-center">
                            {{ number(fleetAverage.signs) }}
                        </TableCell>

                        <TableCell class="text-center">
                            {{ number(fleetAverage.lights) }}
                        </TableCell>

                        <TableCell class="text-center">
                            {{ number(fleetAverage.following) }}
                        </TableCell>

                        <TableCell class="text-center">
                            {{ number(fleetAverage.parking) }}
                        </TableCell>

                    </TableRow>

                </TableBody>

            </Table>

        </div>

    </div>

</template>