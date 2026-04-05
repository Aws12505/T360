<template>
    <div class="h-full flex flex-col">

        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-base sm:text-lg font-semibold">Safety Metrics</h3>
                <div class="text-xs text-muted-foreground">Green Zone Score</div>
            </div>

        </div>

        <!-- Score -->
        <div class="text-3xl sm:text-4xl font-bold mb-4 leading-none"
            :class="getSafetyScoreColorClass(safetyData.average_driver_score)">
            {{ formatDecimal(safetyData.average_driver_score || 'N/A') }}
        </div>

        <!-- Table Header -->
        <div class="flex justify-between text-xs sm:text-sm font-semibold mb-2 text-muted-foreground">
            <span>Alert</span>
            <span class="hidden sm:block">Total / Rate</span>
        </div>

        <!-- Rows -->
        <div v-for="row in rows" :key="row.label" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 p-2 rounded-md
           border border-transparent
           hover:bg-accent hover:border-border hover:shadow-sm
           transition-all duration-200">
            <span class="text-sm font-medium">{{ row.label }}</span>

            <div class="flex justify-between sm:justify-end gap-4 text-sm">
                <span class="tabular-nums">{{ formatDecimal(row.total) }}</span>
                <span class="text-muted-foreground tabular-nums">
                    {{ formatRate(row.rate) }}
                </span>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-xs sm:text-sm text-muted-foreground mt-4 pt-3 border-t">
            Total Hours: {{ formatDecimal(safetyData.total_hours || 0) }}
        </div>

    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    safetyData: {
        type: Object,
        default: () => ({})
    }
});

// Normalize rows (key improvement)
const rows = computed(() => [
    {
        label: 'Distracted Driving',
        total: props.safetyData.driver_distraction,
        rate: props.safetyData.rates?.driver_distraction
    },
    {
        label: 'Speeding',
        total: props.safetyData.speeding_violations,
        rate: props.safetyData.rates?.speeding_violations
    },
    {
        label: 'Sign Violation',
        total: props.safetyData.sign_violations,
        rate: props.safetyData.rates?.sign_violations
    },
    {
        label: 'Traffic Light Violation',
        total: props.safetyData.traffic_light_violation,
        rate: props.safetyData.rates?.traffic_light_violation
    },
    {
        label: 'Following Distance',
        total: props.safetyData.following_distance,
        rate: props.safetyData.rates?.following_distance
    },
    {
        label: 'Roadside Parking',
        total: props.safetyData.roadside_parking,
        rate: props.safetyData.rates?.roadside_parking
    }
]);

const formatDecimal = (value: any) => {
    const num = Number(value);
    if (!Number.isFinite(num)) return '0';
    return Math.round(num).toString();
};

const formatRate = (value: any) => {
    const num = Number(value);
    if (!Number.isFinite(num)) return '0.00';
    return num.toFixed(2);
};

const getSafetyScoreColorClass = (rating: any) => {
    const num = Number(rating);
    if (!Number.isFinite(num)) return 'text-muted-foreground';
    if (num >= 900) return 'text-green-600';
    if (num >= 750) return 'text-emerald-600';
    if (num >= 600) return 'text-blue-600';
    return 'text-red-600';
};

const getSafetyBadgeVariant = (rating: string | null) => {
    if (!rating) return 'outline';
    if (rating === 'Gold Tier') return 'gold';
    if (rating === 'Silver Tier') return 'silver';
    if (rating === 'Not Eligible') return 'not-eligible';
    return 'outline';
};

const overallSafetyRating = computed(() => {
    if (!props.safetyData?.ratings) return null;

    const ratings = props.safetyData.ratings;
    const values: Record<string, number> = {
        gold: 3,
        silver: 2,
        not_eligible: 1,
    };

    const keys = [
        'traffic_light_violation',
        'speeding_violations',
        'following_distance',
        'driver_distraction',
        'sign_violations',
        'roadside_parking',
    ];

    let best = 'gold';

    for (const key of keys) {
        if (ratings[key] && values[ratings[key]] < values[best]) {
            best = ratings[key];
        }
    }

    if (best === 'gold') return 'Gold Tier';
    if (best === 'silver') return 'Silver Tier';
    if (best === 'not_eligible') return 'Not Eligible';
    return null;
});
</script>