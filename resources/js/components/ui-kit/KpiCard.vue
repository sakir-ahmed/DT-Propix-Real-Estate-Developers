<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    trend: { type: String, default: '' },
    direction: { type: String, default: 'up' },
});
</script>

<template>
    <article class="rounded-lg border border-border bg-surface p-3">
        <p class="text-xs font-medium text-muted">{{ label }}</p>
        <p class="mt-1 text-2xl font-bold tracking-tight tabular-nums">{{ value }}</p>
        <p v-if="trend" class="mt-1 inline-flex items-center gap-1 text-xs" :class="direction === 'down' ? 'text-danger' : 'text-success'">
            <AppIcon :name="direction === 'down' ? 'arrow-down' : 'arrow-up'" />
            {{ trend }}
        </p>
    </article>
</template>
