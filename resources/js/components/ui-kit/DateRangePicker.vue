<script setup>
const from = defineModel('from', { type: String, default: '' });
const to = defineModel('to', { type: String, default: '' });

defineProps({
    label: { type: String, default: '' },
});
</script>

<template>
    <label class="block space-y-1">
        <span v-if="label" class="text-xs font-medium">{{ label }}</span>
        <span class="flex h-7 items-center gap-1 rounded-md border border-border px-2">
            <input v-model="from" type="date" class="bg-transparent text-[13px] outline-none" />
            <span class="text-muted">-</span>
            <input v-model="to" type="date" class="bg-transparent text-[13px] outline-none" />
        </span>
    </label>
</template>
