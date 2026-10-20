<script setup>
import UnitStatusBadge from './UnitStatusBadge.vue';

defineProps({
    units: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <button
            v-for="unit in units"
            :key="unit.code"
            type="button"
            class="rounded-lg border border-border bg-surface p-2 text-left"
        >
            <span class="block text-[13px] font-semibold">{{ unit.code }}</span>
            <UnitStatusBadge class="mt-1" :status="unit.status" />
        </button>
    </div>
</template>
