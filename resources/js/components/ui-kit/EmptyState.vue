<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    title: { type: String, required: true },
    message: { type: String, default: '' },
});
</script>

<template>
    <div class="rounded-lg border border-border bg-surface px-4 py-8 text-center">
        <AppIcon name="circle-info" class="mx-auto mb-2 size-5" />
        <p class="text-sm font-semibold">{{ title }}</p>
        <p v-if="message" class="mt-1 text-[13px] text-muted">{{ message }}</p>
        <div class="mt-3">
            <slot />
        </div>
    </div>
</template>
