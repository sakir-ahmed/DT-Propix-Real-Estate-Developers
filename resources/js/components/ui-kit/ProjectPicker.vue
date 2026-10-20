<script setup>
const model = defineModel({ type: String, default: '' });

defineProps({
    projects: { type: Array, default: () => [] },
    label: { type: String, default: '' },
});
</script>

<template>
    <label class="block space-y-1">
        <span v-if="label" class="text-xs font-medium">{{ label }}</span>
        <select v-model="model" class="h-7 w-full rounded-md border border-border bg-surface px-2 text-[13px]">
            <option v-for="project in projects" :key="project.value" :value="project.value">{{ project.label }}</option>
        </select>
    </label>
</template>
