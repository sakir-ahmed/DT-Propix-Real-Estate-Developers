<script setup>
const model = defineModel({ type: [String, Number], default: '' });

defineProps({
    label: { type: String, default: '' },
});
</script>

<template>
    <label class="block space-y-1">
        <span v-if="label" class="text-xs font-medium">{{ label }}</span>
        <span class="flex h-7 overflow-hidden rounded-md border border-border">
            <span class="flex items-center bg-border/40 px-2 text-[13px] text-muted">৳</span>
            <input v-model="model" inputmode="decimal" class="w-full px-2 text-right text-[13px] outline-none" />
        </span>
    </label>
</template>
