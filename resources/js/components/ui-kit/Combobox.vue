<script setup>
import { computed, ref } from 'vue';
import { ComboboxContent, ComboboxEmpty, ComboboxInput, ComboboxItem, ComboboxPortal, ComboboxRoot, ComboboxTrigger } from 'reka-ui';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    options: { type: Array, default: () => [] },
    label: { type: String, default: '' },
});

const model = defineModel({ type: String, default: '' });
const query = ref('');

const filtered = computed(() => props.options.filter((option) => option.label.toLowerCase().includes(query.value.toLowerCase())));
</script>

<template>
    <label class="block space-y-1">
        <span v-if="label" class="text-xs font-medium">{{ label }}</span>
        <ComboboxRoot v-model="model" class="relative">
            <ComboboxTrigger class="flex h-7 w-full items-center rounded-md border border-border bg-surface px-2 text-left text-[13px]">
                <ComboboxInput v-model="query" class="w-full bg-transparent outline-none" @click.stop />
                <AppIcon name="magnifying-glass" />
            </ComboboxTrigger>
            <ComboboxPortal>
                <ComboboxContent class="z-50 mt-1 max-h-48 w-[var(--reka-combobox-trigger-width)] overflow-auto rounded-md border border-border bg-surface p-1">
                    <ComboboxEmpty class="px-2 py-1 text-[13px] text-muted">-</ComboboxEmpty>
                    <ComboboxItem v-for="option in filtered" :key="option.value" :value="option.value" class="cursor-pointer rounded px-2 py-1 text-[13px] data-[highlighted]:bg-accent/10">
                        {{ option.label }}
                    </ComboboxItem>
                </ComboboxContent>
            </ComboboxPortal>
        </ComboboxRoot>
    </label>
</template>
