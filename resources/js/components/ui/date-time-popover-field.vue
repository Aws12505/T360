<script setup lang="ts">
import { computed } from "vue";
import type { DateValue } from "@internationalized/date";
import { DateFormatter, getLocalTimeZone, parseDate } from "@internationalized/date";
import { CalendarIcon } from "lucide-vue-next";

import { Button } from "@/components/ui/button";
import { Calendar } from "@/components/ui/calendar";
import SleekTimePicker from "@/components/ui/sleek-time-picker.vue";
import { Label } from "@/components/ui/label";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";

const props = withDefaults(
    defineProps<{
        modelValue?: string | null; // YYYY-MM-DDTHH:mm
        dateLabel?: string;
        timeLabel?: string;
        placeholder?: string;
        disabled?: boolean;
        required?: boolean;
        className?: string;
    }>(),
    {
        modelValue: null,
        dateLabel: "Date",
        timeLabel: "Time",
        placeholder: "Pick a date",
        disabled: false,
        required: false,
        className: "",
    }
);

const emit = defineEmits<{
    (e: "update:modelValue", value: string | null): void;
}>();

const df = new DateFormatter("en-US", { dateStyle: "medium" });

const datePart = computed(() => {
    if (!props.modelValue) return "";
    return String(props.modelValue).split("T")[0] || "";
});

const timePart = computed(() => {
    if (!props.modelValue) return "";
    const t = String(props.modelValue).split("T")[1] || "";
    return t.slice(0, 5);
});

const calendarValue = computed<DateValue | null>(() => {
    if (!datePart.value) return null;
    try {
        return parseDate(datePart.value);
    } catch {
        return null;
    }
});

function updateDate(val: DateValue | undefined) {
    const nextDate = val
        ? val.toDate(getLocalTimeZone()).toISOString().split("T")[0]
        : "";

    const currentTime = timePart.value || "00:00";

    if (!nextDate) {
        emit("update:modelValue", null);
        return;
    }

    emit("update:modelValue", `${nextDate}T${currentTime}`);
}

function updateTime(value: string | number) {
    const nextTime = String(value || "");
    const currentDate = datePart.value;

    if (!currentDate && !nextTime) {
        emit("update:modelValue", null);
        return;
    }

    if (!currentDate) {
        emit("update:modelValue", null);
        return;
    }

    if (!nextTime) {
        emit("update:modelValue", `${currentDate}T00:00`);
        return;
    }

    emit("update:modelValue", `${currentDate}T${nextTime}`);
}
</script>

<template>
    <div :class="['grid grid-cols-1 sm:grid-cols-2 gap-2', className]">
        <div class="space-y-1">
            <Label v-if="dateLabel">{{ dateLabel }}</Label>

            <Popover>
                <PopoverTrigger as-child>
                    <Button type="button" variant="outline" class="h-10 w-full justify-start text-left font-normal"
                        :disabled="disabled">
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{
                            calendarValue
                                ? df.format(calendarValue.toDate(getLocalTimeZone()))
                                : placeholder
                        }}
                    </Button>
                </PopoverTrigger>

                <PopoverContent class="w-auto p-0 " align="start" side="bottom" :avoid-collisions="true">
                    <Calendar :model-value="calendarValue" layout="month-and-year" @update:model-value="updateDate" />
                </PopoverContent>
            </Popover>
        </div>

        <div class="space-y-1">
            <Label v-if="timeLabel">{{ timeLabel }}</Label>
            <SleekTimePicker :value="timePart" @change="updateTime" :disabled="disabled" :required="required"
                format="HH:mm" :allow-format-toggle="true" placeholder="Select time" />
        </div>
    </div>
</template>