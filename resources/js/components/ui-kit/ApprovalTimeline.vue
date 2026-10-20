<script setup>
import StatusBadge from './StatusBadge.vue';

defineProps({
    steps: { type: Array, default: () => [] },
});
</script>

<template>
    <ol class="space-y-2">
        <li v-for="step in steps" :key="step.label" class="flex items-center justify-between rounded-md border border-border px-3 py-1.5 text-[13px]">
            <span>{{ step.label }}</span>
            <StatusBadge :label="step.status" :tone="step.status === 'Approved' ? 'success' : 'warning'" icon="lock" />
        </li>
    </ol>
</template>
