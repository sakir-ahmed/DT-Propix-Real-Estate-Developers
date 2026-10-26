<script setup>
import AppIcon from '@/components/ui-kit/AppIcon.vue';

defineProps({
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="divide-y divide-border rounded-xl border border-border bg-surface">
        <details v-for="(item, index) in items" :key="item.id ?? index" class="group" :open="index === 0">
            <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-left text-base font-semibold marker:content-none">
                <span>{{ item.question }}</span>
                <AppIcon name="chevron-down" class="shrink-0 transition-transform group-open:rotate-180" />
            </summary>
            <div class="px-5 pb-5 text-[15px] leading-7 text-muted">
                <p>{{ item.answer }}</p>
            </div>
        </details>
    </div>
</template>
