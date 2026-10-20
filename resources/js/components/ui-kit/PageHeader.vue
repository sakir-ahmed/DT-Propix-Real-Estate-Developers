<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <header class="mb-3 flex items-start justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold">{{ title }}</h1>
            <p v-if="description" class="mt-1 text-[13px] text-muted">{{ description }}</p>
        </div>
        <div class="flex items-center gap-2">
            <slot />
        </div>
    </header>
</template>
