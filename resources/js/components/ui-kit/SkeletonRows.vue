<script setup>
defineProps({
    rows: { type: Number, default: 4 },
});
</script>

<template>
    <div class="space-y-2" role="status">
        <div v-for="row in rows" :key="row" class="h-8 animate-pulse rounded-md bg-border" />
    </div>
</template>
