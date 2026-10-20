<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    label: { type: String, required: true },
    tone: { type: String, default: 'muted' },
    icon: { type: String, default: 'circle-info' },
});

const tones = {
    success: 'bg-success/10 text-success',
    warning: 'bg-warning/15 text-foreground',
    danger: 'bg-danger/10 text-danger',
    info: 'bg-accent/10 text-accent',
    muted: 'bg-border text-muted',
};
</script>

<template>
    <span class="inline-flex h-5 items-center gap-1 rounded-md px-2 text-xs font-medium" :class="tones[tone] ?? tones.muted">
        <AppIcon :name="icon" />
        {{ label }}
    </span>
</template>
