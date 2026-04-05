<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { Clock, RotateCcw } from "lucide-vue-next";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ScrollArea } from "@/components/ui/scroll-area";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";

type TimeDisplayFormat = "HH:mm" | "hh:mm aa";

const props = withDefaults(
    defineProps<{
        id?: string;
        value?: string;
        label?: string;
        placeholder?: string;
        disabled?: boolean;
        readOnly?: boolean;
        required?: boolean;
        format?: TimeDisplayFormat;
        allowFormatToggle?: boolean;
    }>(),
    {
        id: "time",
        value: "",
        label: "",
        placeholder: "Select time",
        disabled: false,
        readOnly: false,
        required: false,
        format: "HH:mm",
        allowFormatToggle: true,
    }
);

const emit = defineEmits<{
    (e: "change", value: string): void;
}>();

const open = ref(false);

const formatMode = ref<TimeDisplayFormat>(props.format);

watch(
    () => props.format,
    (v) => {
        formatMode.value = v;
    },
    { immediate: true }
);

const use12h = computed(() => formatMode.value.includes("aa"));

function pad2(n: number) {
    return String(n).padStart(2, "0");
}

function parseTime(value?: string) {
    if (!value) return null;
    const m = String(value).match(/^(\d{1,2}):(\d{2})$/);
    if (!m) return null;
    const h = Number(m[1]);
    const min = Number(m[2]);
    if (Number.isNaN(h) || Number.isNaN(min)) return null;
    return { h, m: min };
}
function setFormatMode(mode: TimeDisplayFormat) {
    formatMode.value = mode;
}
const parts = ref<{ h: number; m: number } | null>(parseTime(props.value));

watch(
    () => props.value,
    (v) => {
        parts.value = parseTime(v);
    },
    { immediate: true }
);

const displayValue = computed(() => {
    if (!parts.value) return "";
    const { h, m } = parts.value;

    if (!use12h.value) return `${pad2(h)}:${pad2(m)}`;

    const am = h < 12;
    const hh = h % 12 === 0 ? 12 : h % 12;
    return `${pad2(hh)}:${pad2(m)} ${am ? "AM" : "PM"}`;
});

function commit(next: { h: number; m: number } | null) {
    parts.value = next;
    emit("change", next ? `${pad2(next.h)}:${pad2(next.m)}` : "");
}

function setHour(displayHour: number) {
    const current = parts.value ?? { h: 0, m: 0 };

    if (!use12h.value) {
        commit({ h: displayHour, m: current.m });
        return;
    }

    const isAM = current.h < 12;
    let h24 = displayHour % 12;
    if (!isAM) h24 += 12;
    commit({ h: h24, m: current.m });
}

function setMinute(minute: number) {
    const current = parts.value ?? { h: 0, m: 0 };
    commit({ h: current.h, m: minute });
}

function setAMPM(next: "AM" | "PM") {
    const current = parts.value ?? { h: 0, m: 0 };
    let h = current.h;

    if (next === "AM" && h >= 12) h -= 12;
    if (next === "PM" && h < 12) h += 12;

    commit({ h, m: current.m });
}

function clearTime() {
    commit(null);
}

function setNow() {
    const now = new Date();
    commit({ h: now.getHours(), m: now.getMinutes() });
}

const hourOptions = computed(() =>
    use12h.value
        ? Array.from({ length: 12 }, (_, i) => i + 1)
        : Array.from({ length: 24 }, (_, i) => i)
);

const minuteOptions = Array.from({ length: 60 }, (_, i) => i);

const displayHour = computed(() => {
    if (!parts.value) return use12h.value ? 12 : 0;
    if (!use12h.value) return parts.value.h;
    return parts.value.h % 12 === 0 ? 12 : parts.value.h % 12;
});

const ampm = computed<"AM" | "PM">(() => {
    if (!parts.value) return "AM";
    return parts.value.h < 12 ? "AM" : "PM";
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <Label v-if="label" :for="id" class="px-1 text-sm font-medium">
            {{ label }}
        </Label>

        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button :id="id" type="button" variant="outline" class="h-10 w-full justify-start text-left font-normal"
                    :disabled="disabled || readOnly">
                    <Clock class="mr-2 h-4 w-4" />
                    <span :class="displayValue ? '' : 'text-muted-foreground'">
                        {{ displayValue || placeholder }}
                    </span>
                </Button>
            </PopoverTrigger>

            <PopoverContent class="w-[280px] p-3 rounded-xl" align="end" :side-offset="4">

                <div class="space-y-3">
                    <div v-if="allowFormatToggle" class="flex gap-2 rounded-md border bg-muted/30 p-1">
                        <Button type="button" size="sm" variant="outline" :class="!use12h
                            ? 'flex-1 bg-accent text-accent-foreground hover:bg-accent hover:text-accent-foreground'
                            : 'flex-1'
                            " @click="setFormatMode('HH:mm')">
                            24-hour
                        </Button>

                        <Button type="button" size="sm" variant="outline" :class="use12h
                            ? 'flex-1 bg-accent text-accent-foreground hover:bg-accent hover:text-accent-foreground'
                            : 'flex-1'
                            " @click="setFormatMode('hh:mm aa')">
                            12-hour
                        </Button>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-2">
                            <div class="text-[10px] tracking-wide font-medium text-muted-foreground px-1">
                                HOUR
                            </div>
                            <ScrollArea class="h-48 rounded-md border bg-card">
                                <div class="py-1">
                                    <Button v-for="h in hourOptions" :key="h" size="sm" type="button" variant="ghost"
                                        :class="displayHour === h
                                            ? 'h-8 w-full justify-center rounded-none font-mono text-sm bg-accent text-accent-foreground hover:bg-accent hover:text-accent-foreground'
                                            : 'h-8 w-full justify-center rounded-none font-mono text-sm hover:bg-accent hover:text-accent-foreground'
                                            " @click="setHour(h)">
                                        {{ pad2(h) }}
                                    </Button>
                                </div>
                            </ScrollArea>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="text-[10px] tracking-wide font-medium text-muted-foreground px-1">
                                MIN
                            </div>
                            <ScrollArea class="h-48 rounded-md border bg-card">
                                <div class="py-1">
                                    <Button v-for="m in minuteOptions" :key="m" size="sm" type="button" variant="ghost"
                                        :class="parts?.m === m
                                            ? 'h-8 w-full justify-center rounded-none font-mono text-sm bg-accent text-accent-foreground hover:bg-accent hover:text-accent-foreground'
                                            : 'h-8 w-full justify-center rounded-none font-mono text-sm hover:bg-accent hover:text-accent-foreground'
                                            " @click="setMinute(m)">
                                        {{ pad2(m) }}
                                    </Button>
                                </div>
                            </ScrollArea>
                        </div>
                    </div>

                    <div v-if="use12h" class="flex gap-2 p-2 bg-muted/40 rounded-md border justify-center">
                        <Button size="sm" type="button" variant="outline" :class="ampm === 'AM'
                            ? 'flex-1 bg-accent text-accent-foreground hover:bg-accent hover:text-accent-foreground'
                            : 'flex-1'
                            " @click="setAMPM('AM')">
                            AM
                        </Button>
                        <Button size="sm" type="button" variant="outline" :class="ampm === 'PM'
                            ? 'flex-1 bg-accent text-accent-foreground hover:bg-accent hover:text-accent-foreground'
                            : 'flex-1'
                            " @click="setAMPM('PM')">
                            PM
                        </Button>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t">
                        <Button variant="ghost" size="sm" type="button"
                            class="text-muted-foreground hover:text-destructive" @click="clearTime">
                            <RotateCcw class="size-3 mr-1" />
                            Clear
                        </Button>

                        <div class="flex items-center gap-2">
                            <Button variant="ghost" size="sm" type="button" @click="setNow">
                                Now
                            </Button>
                            <Button size="sm" type="button" @click="open = false">
                                Apply
                            </Button>
                        </div>
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>