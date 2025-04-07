<script setup lang="ts">
import { type Column } from '@tanstack/vue-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator,
} from '@/components/ui/command';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { computed } from 'vue';

interface FilterOption {
  label: string;
  value: string;
  icon?: any;
}

const props = defineProps<{
  column?: Column<any, unknown>;
  title?: string;
  options: FilterOption[];
}>();

const facets = computed(() => {
  if (!props.column) return [];
  return props.column.getFacetedUniqueValues();
});

const selectedValues = computed(() => {
  if (!props.column) return new Set();
  return new Set(props.column.getFilterValue() as string[]);
});

function handleSelect(value: string) {
  if (!props.column) return;
  
  const filterValues = props.column.getFilterValue() as string[] || [];
  const selected = new Set(filterValues);
  
  if (selected.has(value)) {
    selected.delete(value);
  } else {
    selected.add(value);
  }
  
  props.column.setFilterValue(selected.size ? Array.from(selected) : undefined);
}
</script>

<template>
  <Popover>
    <PopoverTrigger :as-child="true">
      <Button variant="outline" size="sm" :class="cn('h-8 border-dashed', selectedValues.size > 0 && 'border-primary')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
        {{ title }}
        <Separator orientation="vertical" class="mx-2 h-4" />
        <Badge
          v-for="option in options.filter(option => selectedValues.has(option.value))"
          :key="option.value"
          variant="secondary"
          class="rounded-sm px-1 font-normal"
        >
          {{ option.label }}
        </Badge>
        <Badge
          v-if="selectedValues.size > 2"
          variant="secondary"
          class="rounded-sm px-1 font-normal"
        >
          +{{ selectedValues.size - 2 }} more
        </Badge>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[200px] p-0" align="start">
      <Command>
        <CommandInput placeholder="Search..." />
        <CommandList>
          <CommandEmpty>No results found.</CommandEmpty>
          <CommandGroup>
            <CommandItem
              v-for="option in options"
              :key="option.value"
              :value="option.value"
              @select="handleSelect(option.value)"
            >
              <div
                :class="cn(
                  'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                  selectedValues.has(option.value)
                    ? 'bg-primary text-primary-foreground'
                    : 'opacity-50 [&_svg]:invisible'
                )"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M20 6 9 17l-5-5"/></svg>
              </div>
              <span>{{ option.label }}</span>
              <span v-if="facets.get(option.value)" class="ml-auto flex h-4 w-4 items-center justify-center font-mono text-xs">
                {{ facets.get(option.value) }}
              </span>
            </CommandItem>
          </CommandGroup>
          <CommandSeparator v-if="selectedValues.size > 0" />
          <CommandGroup v-if="selectedValues.size > 0">
            <CommandItem
              @select="column?.setFilterValue(undefined)"
              class="justify-center text-center"
            >
              Clear filters
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>