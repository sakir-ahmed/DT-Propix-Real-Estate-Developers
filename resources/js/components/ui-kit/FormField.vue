<script setup>
defineProps({
    label: { type: String, required: true },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
});
</script>

<template>
    <label class="block space-y-1">
        <span class="text-xs font-medium">{{ label }}</span>
        <slot />
        <span v-if="hint && !error" class="block text-xs text-muted">{{ hint }}</span>
        <span v-if="error" class="flex items-center gap-1 text-xs text-danger">
            <slot name="error-icon" />
            {{ error }}
        </span>
    </label>
</template>
