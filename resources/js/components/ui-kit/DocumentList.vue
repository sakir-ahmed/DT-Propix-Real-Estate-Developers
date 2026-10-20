<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    documents: { type: Array, default: () => [] },
});
</script>

<template>
    <ul class="divide-y divide-border rounded-lg border border-border bg-surface">
        <li v-for="document in documents" :key="document.name" class="flex items-center gap-2 px-3 py-2 text-[13px]">
            <AppIcon name="download" />
            <span class="flex-1 truncate">{{ document.name }}</span>
            <span class="text-muted">{{ document.visibility }}</span>
        </li>
    </ul>
</template>
